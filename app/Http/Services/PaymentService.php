<?php

namespace App\Http\Services;

class PaymentService
{
    private $_config = [];
	
	function __construct(array $attributes = [])
    {
    }

    public function setConfig($anchor_or_array, $value = null) {
        if(is_string($anchor_or_array))
            $this->_config[$anchor_or_array] = $value;
        else {
            if(is_array($anchor_or_array)&&count($anchor_or_array)>0) {
                foreach($anchor_or_array as $a => $v)
                    $this->_config[$a] = $v;
            }
        }
    }

    public function getConfig($anchor) {
        if(isseT($this->_config[$anchor]))
            return $this->_config[$anchor];
    }

    public function returnPaymentButtonHtml($prefix_link, $order, $client, $payment_data, $client_url, $post_url) {
        return '';
    }
}
