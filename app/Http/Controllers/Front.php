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

    public function products() {
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