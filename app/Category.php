<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    public $timestamps = false;
    public function words()
    {
        return $this->hasMany('App\Word');
    }
}
