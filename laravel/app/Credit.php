<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
  
  protected $fillable = [
    'package_name',
    'description',
    'rate_per_click',
    'min',
    'max',
    'vat',
  ];
}
