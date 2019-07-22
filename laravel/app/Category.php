<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nestable\NestableTrait;
class Category extends Model
{
  use NestableTrait;
  protected $fillable = [
    'parent_id',
    'name',
    'slug',
    'description',
  ];
  protected $parent = ['parent_id'];
  public function products(){
      return $this->hasMany('App\Product');
    }

  public function parent()
    {
        return $this->belongsTo('App\Category', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Category', 'parent_id');
    }

    // public function parent()
    // {
    //     return $this->belongsTo(Category::class, 'parent_id', 'id');
    // }
    //
    // public function children()
    // {
    //     return $this->hasMany(Category::class, 'parent_id', 'id');
    // }
}
