<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
  protected $fillable = [
    'bankwire_active',
    'paypal_active',
    'paypal_email',
    'paypal_currency_id',
    'paypal_client_id',
    'paypal_client_secret',
    'stripe_active',
    'stripe_publishable_key',
    'stripe_secret_key',
    'stripe_currency_id',
    'voguepay_active',
    'voguepay_merchant_id',
    'voguepay_currency_id',
  ];
}
