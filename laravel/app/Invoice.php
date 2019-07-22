<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  protected $fillable = [
    'package_name',
    'description',
    'user_id',
    'invoice_number',
    'payment_method',
    'rate_per_click',
    'amount',
    'status',
    'vat',
  ];
  public function user(){
      // return $this->hasMany('App\User');
      return $this->belongsTo(User::class,'user_id','id');
    }

}
