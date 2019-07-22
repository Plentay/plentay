<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

  protected $fillable = [
    'name',
    'code',
    'symbol',
    'html_entity',
  ];
  public function users(){
      return $this->hasMany('App\User');
    }

}
