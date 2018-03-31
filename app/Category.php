<?php

namespace App;

class Category extends BaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'categories';
    protected $fillable = array('name', 'created_at_ip', 'updated_at_ip'); //defines field names 
    //that can be mass assigned. This is a security measure that ensures only authorized 
    //fieldnames are affected.

}
