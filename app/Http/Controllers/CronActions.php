<?php

namespace App\Http\Controllers;

use App\ProgrammingStatisticsPlacesModel;
use App\ProgrammingStatisticsQueriesModel;
use App\ProgrammingStatisticsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;

class CronActions extends Controller {

    public function __construct() {
        
    }
    
    public function downloadProgrammingStatistics() {
       
        self::saveProgrammingStats();
        die('sukces');
    }
    
    public static function saveProgrammingStats() {
        
        $dev_stat_places = ProgrammingStatisticsPlacesModel::get();
        $dev_stat_queries = ProgrammingStatisticsQueriesModel::get();
        
        foreach($dev_stat_places as $place) {
            foreach($dev_stat_queries as $query) {
                $site = $place->link;
                $qry_str = "q={$query->query}";   
                $url = $site.$qry_str;
                $count_of_jobs = ProgrammingStatisticsQueriesModel::getCountOfJobsFromQuery($url);
                if($count_of_jobs) {
                    $stat_dev = new ProgrammingStatisticsModel();
                    $stat_dev->lang = $query->lang;
                    $stat_dev->count = $count_of_jobs;
                    $stat_dev->place = $place->place;
                    $stat_dev->stat_date = date('Y-m-d G:i:s');
                    $stat_dev->save();
                }
            }
        }
    }
}