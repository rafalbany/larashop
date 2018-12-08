<?php

namespace App;

class ProgrammingStatisticsQueriesModel extends BaseModel {
    protected $primaryKey = 'id';
    protected $table = 'programming_statistics_queries';
    protected $fillable = ['lang','query'];
    
    public static function getCountOfJobsFromQuery($url) {
        $zm = file_get_contents($url);
        $expl_text = explode('id="searchCount">',$zm);
        $expl_text = substr($expl_text[1],0,30);
        $gtxt = explode(' ',$expl_text);
        $count = count($gtxt);
        return str_replace(',','',$gtxt[$count-2]);
    }
}
