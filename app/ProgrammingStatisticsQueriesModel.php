<?php

namespace App;

use PHPHtmlParser\Dom;

class ProgrammingStatisticsQueriesModel extends BaseModel {
    protected $primaryKey = 'id';
    protected $table = 'programming_statistics_queries';
    protected $fillable = ['lang','query'];
    
    public static function getCountOfJobsFromQuery($url) {

        $dom = new Dom();
        $dom->loadFromUrl($url);
        $element = $dom->find('#searchCount');

        $expl_text = $element->text;
        try {
            $gtxt = explode(' ',$expl_text);
            $count = count($gtxt);
            $numb_first = str_replace(["&nbsp;",",","."," "], "",$gtxt[$count-2]);
            $numb_sec = str_replace(["&nbsp;",",","."," "], "",$gtxt[$count-1]);

            $string = htmlentities($numb_first, null, 'utf-8');
            $content = str_replace(["&nbsp;","."], "", $string);
            $content = html_entity_decode($content);
            if(is_numeric($content)) {
                return str_replace(' ','',(str_replace(',','',$content)));
            }
            $string = htmlentities($numb_sec, null, 'utf-8');
            $content = str_replace(["&nbsp;","."], "", $string);
            $content = html_entity_decode($content);
            if(is_numeric($content)) {
                return str_replace(' ','',(str_replace(',','',$content)));
            }
        } catch(\Exception $ex) {
            return die(var_dump($ex->getMessage()));
        }
    }
}
