<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
  protected $fillable = [
    'ip_address',
    'source',
    'destination',
    'user_id',
  ];
  public function user(){
      return $this->belongsTo(User::class,'user_id','id');
    }
}
