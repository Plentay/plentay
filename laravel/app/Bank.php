<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    //

    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'other_details',
    ];

}
