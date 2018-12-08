<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Category;
use App\Product;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;

class CronActions extends Controller {

    public function __construct() {
        
    }
    
    public function downloadProgrammingStatistics() {
       
        $stats = self::getProgrammingStats();
        
    }
    
    public static function getProgrammingStats() {
        die('1sdf');
    }
}