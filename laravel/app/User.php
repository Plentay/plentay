<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\MyResetPassword;
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'email',
      'password',
      'contact_name',
      'address',
      'url',
      'image',
      'about',
      'phone_number',
      'credit',
      'validation_code',
      'active',
      'price_update_block',
      'price_update_element',
      'description_update_element',
      'currency_id',
      'role_id',
      'company_id',
    ];
    public function products(){
        return $this->hasMany('App\Product');
      }
    public function invoice(){
        return $this->hasMany('App\Invoice');
      }
      public function report(){
          return $this->hasMany('App\Report');
        }
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id','id');
      }




    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function sendPasswordResetNotification($token)
{
    $this->notify(new MyResetPassword($token));
}
}
