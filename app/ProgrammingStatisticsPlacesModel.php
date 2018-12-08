<?php

namespace App;

class ProgrammingStatisticsPlacesModel extends BaseModel {
    protected $primaryKey = 'id';
    protected $table = 'programming_statistics_places';
    protected $fillable = ['link','country'];
}
