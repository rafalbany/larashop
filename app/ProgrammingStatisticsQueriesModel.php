<?php

namespace App;

class ProgrammingStatisticsQueriesModel extends BaseModel {
    protected $primaryKey = 'id';
    protected $table = 'programming_statistics_queries';
    protected $fillable = ['lang','query'];
    
    public static function getCountOfJobsFromQuery($url) {
        $zm = file_get_contents($url);
        $expl_text = explode('id="searchCount">',$zm);
        try {
            $expl_text = substr($expl_text[1],0,30);
            $gtxt = explode(' ',$expl_text);
            $count = count($gtxt);
            $numb_first = str_replace(["&nbsp;",",","."], "",$gtxt[$count-2]);
            $numb_sec = str_replace(["&nbsp;",",","."], "",$gtxt[$count-1]);
            $number = is_numeric($numb_first) ? $numb_first : $numb_sec;
            $string = htmlentities($number, null, 'utf-8');
            $content = str_replace(["&nbsp;","."], "", $string);
            $content = html_entity_decode($content);

            return str_replace(' ','',(str_replace(',','',$content)));
        } catch(\Exception $ex) {
            return die(var_dump($ex->getMessage()));
        }
    }
}
