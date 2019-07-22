<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  protected $fillable = [
    'name',
    'amount',
    'description',
    'affiliate_url',
    'original_url',
    'slug',
    'image',
    'user_id',
    'views_count',
    'click_count',
    'category_id',
    'rating_id',
    'active',
  ];
  public function category(){
    return $this->belongsTo('App\Category');
  }
  public function user(){
    return $this->belongsTo('App\User');
  }

}
