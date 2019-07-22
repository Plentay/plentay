<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable = [
    'disqus',
    'currency_symbol',
    'currency_name',
    'currency_code',
    'logo',
    'site_name',
    'site_email',
    'site_address',
    'site_number',
    'site_about',
    'live_production',
    'csv_import_limit',
    'home_rand_pro',
    'home_posts',
    'home_users',
    'buy_button',
    'compare_percentage',
    'compared_products',
    'enable_admin',
    'social_facebook',
    'social_twitter',
    'social_instagram',
    'keywords',
    'meta_name',
    'search_element',
    'search_order',


  ];
}
