<?php

namespace App;

class ProgrammingStatisticsModel extends BaseModel {
    protected $primaryKey = 'id';
    protected $table = 'programming_statistics';
    protected $fillable = ['lang', 'count', 'stat_date'];
}
