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
        $element = $dom->find('#searchCountPages');

        $expl_text = $element->text;
        try {
            $gtxt = explode(' ',$expl_text);
            $count = count($gtxt);
            $number = '';

            for ($x = ($count - 1); $x > 2; $x--) {
                $element = str_replace(["&nbsp;",",","."," "], "",$gtxt[$x]);
                if(is_numeric($element)) {
                    $number = $element . $number;
                }
            }
            return $number;
        } catch(\Exception $ex) {
            return die(var_dump($ex->getMessage()));
        }
    }
}
