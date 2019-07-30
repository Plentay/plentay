<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/account/gateway/voguepay_success',
    	'/account/gateway/voguepay_fail',
    	'/api/contact-us',
    	'/api/childCategory',
    	'/api/productDetail',
    	'/api/products',
    	'/api/company-registration',
    	'/api/login'
    ];
}
