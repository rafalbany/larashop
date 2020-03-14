<?php

namespace App\Http\Services;

use Illuminate\Http\Request;

use App\Http\Panel\Plugins\OnlinePayments\PaymentAbstract;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

class PayPalService extends PaymentService
{
    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    private $_url_table = [
        'officjal'=>'https://api.paypal.com',
        'sandbox'=>'https://api.sandbox.paypal.com'
    ];

    private $_url_actions = [
        'auth'=>'/pl/standard/user/oauth/authorize',
        'order'=>'/api/v2_1/orders',
    ];

    private $_lang_code_arr = [
        'pl','en','cs'
    ];

    private $_currency_code_arr = [
        'PLN','EUR','GBP','CZK','USD'
    ];

    private $_countries_codes_arr = [
        'Polska'=>'PL',
        'Germany'=>'DE',
        'United States of America'=>'US',
        'Canada'=>'CA',
        'Australia'=>'AU',
        'Austria'=>'AUT',
        'Belgium'=>'BEL',
        'Bulgaria'=>'BGR',
        'Czechia'=>'CZE',
        'Denmark'=>'DNK',
        'Estonia'=>'EST',
        'Finland'=>'FIN',
        'France'=>'FRA',
        'Greece'=>'GRC',
        'Hungary'=>'HUN',
        'Ireland'=>'IRL',
        'Italy'=>'IT',
        'Latvia'=>'LV',
        'Luxembourg'=>'LU',
        'Monaco'=>'MC',
        'Norway'=>'NO',
        'Portugal'=>'PT',
        'Romania'=>'RO',
        'Serbia'=>'RS',
        'Slovakia'=>'SK',
        'Slovenia'=>'SI',
        'Spain'=>'ES',
        'Sweden'=>'SE',
        'Switzerland'=>'CH',
        'Turkey'=>'TR',
        'United Kingdom of Great Britain'=>'UK',
        'Croatia'=>'HR',
    ];

    private $_token = null;
    private $_token_expire = null;

    public function setCountryCode($code) {
        if(!in_array(mb_strtoupper($code), $this->_countries_codes_arr))
            return false;
        else {
            $this->_country_code = $code;
            return true;
        }
    }

    public function setCurrencyCode($code) {
        if(!in_array($code, $this->_currency_code_arr))
            return false;
        else {
            $this->_currency_code = $code;
            return true;
        }
    }

    public function setLangCode($code) {
        $code = strtolower($code);
        if(!in_array($code, $this->_lang_code_arr))
            return false;
        else {
            $this->_lang_code = $code;
            return true;
        }
    }

    public function registerPayment(
        $unique_session_id,
        $amount,
        $shipping = 0,
        $description,
        $client_ip,
        $client_id,
        $client_email,
        $client_first_name,
        $client_last_name,
        $client_phone,
        $client_return_url,
        $post_info_return_url,
        $products
    ) {

        $amount = str_replace(',','.',$amount)*100;

        $paypal = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->getConfig('auth_client_id'),$this->getConfig('auth_client_secret')
            )
        );

        if(!$this->getConfig('sandbox')) {
            $paypal->setConfig(['mode'=>'live']);
        }

        $subTotal = 0;

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $itemList = new ItemList();
        $items_arr = [];

        foreach($products as $product) {
            $price = $product['unitPrice'];
            $item = new Item();
            $item->setName($product['name'])->setCurrency($this->_currency_code)->setQuantity($product['quantity'])->setPrice($price);

            $items_arr[] = $item;
            $subTotal += $product['quantity']*$price;
        }
        $itemList->setItems($items_arr);
        $total = $subTotal + $shipping;

        $details = new Details();
        $details->setShipping($shipping)->setSubtotal($subTotal);

        $amount = new Amount();
        $amount->setCurrency($this->_currency_code)->setTotal($total)->setDetails($details);

        $transaction = new Transaction();

        $transaction->setCustom($client_return_url)->setAmount($amount)->setItemList($itemList)->setDescription($description)->setInvoiceNumber($unique_session_id);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($post_info_return_url)->setCancelUrl($post_info_return_url);

        $payment = new Payment();
        $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction]);

        try {

            $payment->create($paypal);

        } catch(\Exception $ex){
            die(var_dump($ex->getMessage()));
        }

        $approvalUrl = $payment->getApprovalLink();

        $this->_goToPayPal($approvalUrl);

    }

    public function checkReply($request_arr) {
        if(!isset($request_arr['paymentId']) || !isset($request_arr['PayerID']))
            return false;
        $paymentId = $request_arr['paymentId'];
        $payerId = $request_arr['PayerID'];

        $paypal = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->getConfig('auth_client_id'),$this->getConfig('auth_client_secret')
            )
        );
        if(!$this->getConfig('sandbox')) {
            $paypal->setConfig(['mode'=>'live']);
        }

        $payment = Payment::get($paymentId, $paypal);

        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        $error = false;
        try{
            $result = $payment->execute($execute,$paypal);
            $transactions = $result->getTransactions();
            if(isset($transactions[0])) {
                $transaction = $transactions[0];
                $data_arr = [];
                $data_arr['order_id'] = $transaction->getInvoiceNumber();
                $data_arr['success_url'] = $transaction->getCustom();
                $amount = $transaction->getAmount();
                $data_arr['amount'] = $amount->getTotal();
                $data_arr['result_obj'] = $payment->toJSON();

                return $data_arr;
            }
            return true;
        } catch(\Exception $ex){
            \Log::error('Nieautoryzowana próba połączenia z API PayPal');
            $error = true;
        }
        if(!$error) {
            return true;
        }
        else {
            return false;
        }
    }

    private function _getAuthToken() {
        $url = $this->_url_actions['auth'];
        $return = $this->_sendInfo($url,[
            'grant_type'=>'client_credentials',
            'client_id'=>$this->getConfig('auth_client_id'),
            'client_secret'=>$this->getConfig('auth_client_secret')
        ],null,'urlencoded');
        if(isset($return['access_token'])) {
            $this->_token = $return['access_token'];
            $this->_token_expire = $return['expires_in'];
        }
    }

    private function _goToPayPal($url) {
        \Redirect($url)->send();
    }

    private function _getUsedUrl() {
        if($this->getConfig('sandbox'))
            return $this->_url_table['sandbox'];
        else
            return $this->_url_table['officjal'];
    }

    private function _sendInfo($url, $data = null, $header = null, $data_type = 'json') {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$this->_getUsedUrl().$url);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data_type_header = '';
        $auth_token_header = "";

        if($data) {
            if ($data_type == 'urlencoded') {
                $data = http_build_query($data);
                $data_type_header = 'Content-Type: application/x-www-form-urlencoded';
            } elseif ($data_type == 'json') {
                $data = stripslashes(json_encode($data));
                $data_type_header = 'Content-Type: application/json';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if($header) {
            $auth_token_header = $header;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $data_type_header,
            $auth_token_header
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);

        if($server_output!='') {
            $return = json_decode($server_output,1);
            return $return;
        }
    }

    public function returnPaymentButtonHtml($prefix_link, $order, $client, $payment_data, $client_url, $post_url) {
        return '<a href="'.$prefix_link.'/pay">' . flang2('pay_online') . '</a>';
    }
}
