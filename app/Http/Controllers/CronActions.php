<?php

namespace App\Http\Controllers;

use App\ProgrammingStatisticsPlacesModel;
use App\ProgrammingStatisticsQueriesModel;

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
        
        $dev_stat_places = ProgrammingStatisticsPlacesModel::get();
        $dev_stat_queries = ProgrammingStatisticsQueriesModel::get();
        
        foreach($dev_stat_places as $place) {
            foreach($dev_stat_queries as $query) {
                $site = $place->link;
                $qry_str = "q={$query->query}";   
                $url = $site.$qry_str;
                $count_of_jobs = ProgrammingStatisticsQueriesModel::getCountOfJobsFromQuery($url);
                die(var_dump($count_of_jobs));
            }
        }
        
        
    }
}