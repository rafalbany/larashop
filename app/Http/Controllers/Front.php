<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Category;
use App\Product;
use App\User;
use App\ProgrammingStatisticsModel;
use App\ProgrammingStatisticsQueriesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;

class Front extends Controller {

    var $brands;
    var $categories;
    var $products;
    var $title;
    var $description;

    public function __construct() {
        $this->brands = Brand::all(array('name'));
        $this->categories = Category::all(array('name'));
        $this->products = Product::all(array('id','name','price'));
    }
    
    public function register() {
        if (Request::isMethod('post')) {
            User::create([
                        'name' => Request::get('name'),
                        'email' => Request::get('email'),
                        'password' => bcrypt(Request::get('password')),
            ]);
        } 
    
        return Redirect::away('login');
    }
    
    public function authenticate() {
        if (Auth::attempt(['email' => Request::get('email'), 'password' => Request::get('password')])) {
            return redirect()->intended('checkout');
        } else {
            return view('login', array('title' => 'Welcome', 'description' => '', 'page' => 'home'));
        }
    }
    
    public function logout() {
        Auth::logout();

        return Redirect::away('login');
    }

    public function index() {
        return view('home', array('title' => 'Welcome','description' => '','page' => 'home', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    /*
* This function searchs in LDAP tree ($ad -LDAP link identifier)
* entry specified by samaccountname and returns its DN or epmty
* string on failure.
*/
    static function getDN($ad, $samaccountname, $basedn) {
        $attributes = array('dn');
        $result = ldap_search($ad, $basedn,
            "(sAMAccountName={$samaccountname})", $attributes);
        if ($result === FALSE) { return ''; }
        $entries = ldap_get_entries($ad, $result);
        if ($entries['count']>0) { return $entries[0]['dn']; }
        else { return ''; };
    }

    /*
    * This function retrieves and returns CN from given DN
    */
    static function getCN($dn) {
        preg_match('/[^,]*/', $dn, $matchs, PREG_OFFSET_CAPTURE, 3);
        return $matchs[0][0];
    }

    /*
    * This function checks group membership of the user, searching only
    * in specified group (not recursively).
    */
    static function checkGroup($ad, $userdn, $groupdn) {
        $attributes = array('members');
        $result = ldap_read($ad, $userdn, "(memberof={$groupdn})", $attributes);
        if ($result === FALSE) { return FALSE; };
        $entries = ldap_get_entries($ad, $result);
        return ($entries['count'] > 0);
    }

    /*
    * This function checks group membership of the user, searching
    * in specified group and groups which is its members (recursively).
    */
    static function checkGroupEx($ad, $userdn, $groupdn) {
        $attributes = array('memberof');
        $result = ldap_read($ad, $userdn, '(objectclass=*)', $attributes);
        if ($result === FALSE) { return FALSE; };
        $entries = ldap_get_entries($ad, $result);
        if ($entries['count'] <= 0) { return FALSE; };
        if (empty($entries[0]['memberof'])) { return FALSE; } else {
            for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                if ($entries[0]['memberof'][$i] == $groupdn) { return TRUE; }
                elseif (self::checkGroupEx($ad, $entries[0]['memberof'][$i], $groupdn)) { return TRUE; };
            };
        };
        return FALSE;
    }

    public static function auth($user, $password) {
        if(empty($user) || empty($password)) return false;

        $adServer = "ldap://52.156.251.106";
        $username = 'mary.smith';
        $password = 'Pass@word1!';

        $ldaprdn = 'mycompany' . "\\" . $username;                 //działa
        $ldaprdn = 'CN=Mary Smith,CN=Users,DC=mycompany,DC=local'; //działa
        $domain = 'mycompany.local';
        $ldaprdn = "{$username}@{$domain}";                        //działa


        ////////////////TESTY//////////

        $adServer = "www.zflexldap.com";
        $username = 'guest2';
        $password = 'guest2password';

        $ldaprdn = 'mycompany' . "\\" . $username;                 //działa
        $ldaprdn = 'CN=Mary Smith,CN=Users,DC=mycompany,DC=local'; //działa
        $domain = 'zflexsoftware.com';
        $ldaprdn = "{$username}@{$domain}";                        //działa
        $ldaprdn = "cn=ro_admin,ou=sysadmins,dc=zflexsoftware,dc=com";

        $ldaprdn = "uid=guest2,ou=users,ou=guests,dc=zflexsoftware,dc=com";

//        $base_dn = 'dc=zflexsoftware,dc=com';
//
//        $ldaprdn = 'zflexldap' . "\\" . $username;

        $username = 'einstein';
        $password = 'password';
        $account_suffix = '@forumsys.com';
        $adServer = 'ldap://52.87.186.93';

        $ldaprdn = 'uid=einstein,dc=example,dc=com';

        $basedn = 'dc=example,dc=com';
        $user = 'einstein';



        $adServer = "ldap://52.156.251.106";
        $username = 'mary.smith';
        $password = 'Pass@word1!';

        $ldaprdn = 'mycompany' . "\\" . $username;                 //działa
        $ldaprdn = 'CN=Mary Smith,CN=Users,DC=mycompany,DC=local'; //działa
        $domain = 'mycompany.local';
        $ldaprdn = "{$username}@{$domain}";

        /////////////////////////

        $ldap = ldap_connect($adServer);


        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        $bind = ldap_bind($ldap, $ldaprdn, $password);

       if ($bind) {

           $base_dn = 'DC=mycompany,DC=local';
           $username = 'mary.smith';
           $groupname = 'IT';

           //tej metody mozna uzyc dopiero po wbindowaniu sie na serwer

           $userdn = self::getDN($ldap, $username, $base_dn);
           $groupdn = self::getDN($ldap, $groupname, $base_dn);

           $groupex = self::checkGroupEx($ldap, $userdn, $groupdn);

           $filter ="(cn=*)";
           $justthese = array('cn');

           $result=ldap_list($ldap, $base_dn, $filter, $justthese) or die("No search data found.");

           $info = ldap_get_entries($ldap, $result);

           /////////// TEN KAWAŁEK KODU ZCZYTUJE GRUPY DO KTORYCH NALEZY USER ///////////

           $filter = "(&(objectClass=group)(member:1.2.840.113556.1.4.1941:=$userdn))";
           $search = ldap_search($ldap, $base_dn, $filter, array("cn"));

           $allGroups = ldap_get_entries($ldap, $search);

           $all_groups_arr = [];

           for ($i=0; $i < $allGroups["count"]; $i++) {
               $all_groups_arr[] = $allGroups[$i]["cn"][0];
           }

           die(var_dump($all_groups_arr));

           //https://stackoverflow.com/questions/38364071/display-all-active-directory-groups-that-a-user-is-a-member-of-recursively

           ////////// KONIEC//////////////

//           $groupdn = self::getDN($ldap, 'Accounting', $base_dn);
//           $result = ldap_search($ldap, $base_dn, "(memberOf:1.2.840.113556.1.4.1941:={$groupdn})");
           // ten filter dziala
           //dla zagniezdzenia, np tutaj jest czlonkiem grupy Users bo nalezy do podgrupy tej grupy - tutaj np IT

//           die(var_dump($groupdn));

//           $result = ldap_search($ldap, $base_dn, "(&(objectClass=user)(memberOf:1.2.840.113556.1.4.1941:={$groupdn}))");
//
//           $info = ldap_get_entries($ldap, $result);

//           die(var_dump($info));

           $groups_arr = [];

           for ($i=0; $i < $info["count"]; $i++) {
               $groups_arr[] = $info[$i]["cn"][0];
           }

           $groupdn = self::getDN($ldap, 'Users', $base_dn);
           $result=ldap_list($ldap, $groupdn, $filter, $justthese) or die("No search data found.");

           $info = ldap_get_entries($ldap, $result);

           die(var_dump($info));

           /////


           //////////

           die(var_dump($groups_arr));

           $exists_in_groups_arr = [];
           foreach($groups_arr as $groupname) {
               $groupdn = self::getDN($ldap, $groupname, $base_dn);

               //$result = ldap_search($ldap, $base_dn, "(memberOf:1.2.840.113556.1.4.1941:={$groupdn})"); // ten filter dziala
               //dla zagniezdzenia, np tutaj jest czlonkiem grupy Users bo nalezy do podgrupy tej grupy - tutaj np IT

//               $info = ldap_get_entries($ldap, $result);

               if(self::checkGroupEx($ldap, $userdn, $groupdn)) {
                   $exists_in_groups_arr[] = $groupname;
               }

               if($info['count']) {
                   $exists_in_groups_arr[] = $groupname;
               }
           }

           die(var_dump($exists_in_groups_arr));


           die(var_dump($groupdn));

            $filter="(sAMAccountName=$username)";
//            $filter="cn=*";
            $result = ldap_search($ldap,$base_dn,$filter);
            $info = ldap_get_entries($ldap, $result);

            die(var_dump($info));

           // Loop over
//           for ($i=0; $i < $info['count']; $i++) {
//               if(!empty($info[$i]['member'])) {
//                   print_r($info[$i]['member']);
//                   echo "\n\n";
//               }
//           }
//           die('dg');

            for ($i=0; $i<$info["count"]; $i++)
            {
                if($info['count'] > 1)
                    break;
                echo "<p>You are accessing <strong> ". $info[$i]["sn"][0] .", " . $info[$i]["givenname"][0] ."</strong><br /> (" . $info[$i]["samaccountname"][0] .")</p>\n";
                echo '<pre>';
                var_dump($info);
                echo '</pre>';
                $userDn = $info[$i]["distinguishedname"][0];

                die(var_dump($userDn));
            }
            @ldap_close($ldap);
        } else {
            $msg = "Invalid email address / password";
            echo $msg;
        }




        die('1212');





        ////////////////////////

        $username = 'einstein';
        $password = 'password';
        $account_suffix = '@forumsys.com';
        $hostname = 'ldap://52.87.186.93';

        $rdn = 'uid=einstein,dc=example,dc=com';
//        $rdn = $username . $account_suffix;
        $rdn = 'uid=einstein,dc=example,dc=com';


        $con =  ldap_connect($hostname);
        if (!is_resource($con)) trigger_error("Unable to connect to $hostname",E_USER_WARNING);
        ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        if (ldap_bind($con,$rdn,$password))
        {
            $filter = "(cn=Albert Einstein)";
            $result = ldap_search($con,"dc=example,dc=com",$filter) or exit("Unable to search");
            $entries = ldap_get_entries($con, $result);

            die(var_dump($entries));
        }
        ldap_close($con);

        die('2');





        //DOMYSLNY PORT TO 389, podaje siee jako parametr w ldap_connect


        // Active Directory server
        $ldap_host = "ldap.forumsys.com";

        // connect to active directory
        $ldapconn = ldap_connect($ldap_host) or die("Could not connect to LDAP Server");

        // Active Directory DN
        $lda_admin_prdn = "cn=read-only-admin,dc=example,dc=com";

        $ldaprdn1 = 'ou=mathematicians,dc=example,dc=com';
        $ldaprdn = 'uid=einstein,dc=example,dc=com';

        // Password
        $ldappass = "password";

        // set connection is using protocol version 3, if not will occur warning error.
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn)
        {
            $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

            // verify binding
            if ($ldapbind)
            {
                $user = 'einstein';

                echo "LDAP bind successful…";

                $filter = "(sAMAccountName=" . $user . ")";
                $attr = array("scientists","sn","mail", "distinguishedname");
                $result = ldap_search($ldapconn, $ldaprdn, $filter, $attr) or exit("Unable to search LDAP server");
                $entries = ldap_get_entries($ldapconn, $result);

                die(var_dump($entries));

                $results = ldap_search($ldapconn,$ldaprdn,"(samaccountname=$user)",array("memberof","scientists"));
                $entries = ldap_get_entries($ldapconn, $results);

                die(var_dump($entries));



                $filter = "(sAMAccountName=".$user.")";
                $attr = array("memberof");
                $result = ldap_search($ldapconn, $ldaprdn, $filter, $attr) or exit("Unable to search LDAP server");

                $entries = ldap_get_entries($ldapconn, $result);

                die(var_dump($entries));

                ldap_unbind($entries);

                die(var_dump($entries));


                ////TO JAKOŚ DZIAŁA///
                ///
                ///
                // LDAP query for search
                $filter = "ou=mathematicians,dc=example,dc=com";
                $result = ldap_search($ldapconn, $ldaprdn, $filter) or exit("Unable to search LDAP server");
                $entries = ldap_get_entries($ldapconn, $result);
                var_dump($entries);

            } else {
                echo "LDAP bind failed…";
            }
        } else {
            die('1dg');
        }
        die('koniec');









        $user = 'riemann';

        // active directory server
        $ldap_host = "ldap.forumsys.com";

        // domain, for purposes of constructing $user
        $ldap_usr_dom = '@ldap.forumsys.com';

        // connect to active directory
        $ldap = ldap_connect($ldap_host);

        // configure ldap params
        ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);

        $bind = @ldap_bind($ldap, $user.$ldap_usr_dom, 'password');

        die(var_dump($bind));

        // active directory DN (base location of ldap search)
        $ldap_dn = "OU=Departments,DC=college,DC=school,DC=edu";

        // active directory user group name
        $ldap_user_group = "WebUsers";

        // active directory manager group name
        $ldap_manager_group = "WebManagers";

        // verify user and password
        if($bind = @ldap_bind($ldap, $user.$ldap_usr_dom, $password)) {
            // valid
            // check presence in groups
            $filter = "(sAMAccountName=".$user.")";
            $attr = array("memberof");
            $result = ldap_search($ldap, $ldap_dn, $filter, $attr) or exit("Unable to search LDAP server");
            $entries = ldap_get_entries($ldap, $result);
            ldap_unbind($ldap);

            // check groups
            $access = 0;
            foreach($entries[0]['memberof'] as $grps) {
                // is manager, break loop
                if(strpos($grps, $ldap_manager_group)) { $access = 2; break; }

                // is user
                if(strpos($grps, $ldap_user_group)) $access = 1;
            }

            if($access != 0) {
                // establish session variables
                $_SESSION['user'] = $user;
                $_SESSION['access'] = $access;
                return true;
            } else {
                // user has no rights
                return false;
            }

        } else {
            // invalid name or password
            return false;
        }
    }

    public function products() {


        self::auth('einstein','password');










        die('sukces');





        return view('products', array('title' => 'Products Listing','description' => '','page' => 'products', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function product_details($id) {
        $product = Product::find($id);
        return view('product_details', array('product' => $product, 'title' => $product->name,'description' => '','page' => 'products', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function product_categories($name) {
        return view('products', array('title' => 'Welcome','description' => '','page' => 'products', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function product_brands($name, $category = null) {
        return view('products', array('title' => 'Welcome','description' => '','page' => 'products', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function blog() {
        return view('blog', array('title' => 'Welcome','description' => '','page' => 'blog', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function blog_post($id) {
        return view('blog_post', array('title' => 'Welcome','description' => '','page' => 'blog', 'brands' => $this->brands, 'categories' => $this->categories, 'products' => $this->products));
    }

    public function contact_us() {
        return view('contact_us', array('title' => 'Welcome','description' => '','page' => 'contact_us'));
    }
    
    public function dev_stats($place=null) {
        
        $labels = ProgrammingStatisticsModel::select([\DB::raw('date(stat_date) AS st_date')])->groupBy('st_date')->pluck('st_date')->toArray();
        $days = ProgrammingStatisticsModel::select([\DB::raw('concat(date(stat_date),"-",lang) AS st_lang'),\DB::raw('sum(count) AS count')])->where('place','like','%'.$place.'%')->groupBy('st_lang')->get();
        $days = $days->keyBy('st_lang')->toArray();
        $languages = ProgrammingStatisticsQueriesModel::select(['lang'])->get()->keyBy('lang')->toArray();
        
        $arr = [];
        $i = 0;
        foreach($languages as $key=>$val) {
            $arr[$i] = ['label'=>$val['lang'],'data'=>[]];
            $j=0;
            foreach($labels as $label) {
                if(isset($days[$label.'-'.$key])) {
                    $arr[$i]['data'][$j] = $days[$label.'-'.$key]["count"];
                } else {
                    $arr[$i]['data'][$j] = null;
                }
                $j++;
            }
            $i++;
        }
        
        return view('plot', array('labels'=>$labels,'data'=>$arr,'page' => 'dev-stats'));
    }

    public function login() {
        return view('login', array('title' => 'Welcome','description' => '','page' => 'home'));
    }

    /*public function logout() {
        return view('login', array('title' => 'Welcome','description' => '','page' => 'home'));
    }*/

    public function cart() {
        return view('cart', array('title' => 'Welcome','description' => '','page' => 'home'));
    }

    public function checkout() {
        return view('checkout', array('title' => 'Welcome','description' => '','page' => 'home'));
    }

    public function search($query) {
        return view('products', array('title' => 'Welcome','description' => '','page' => 'products'));
    }
}