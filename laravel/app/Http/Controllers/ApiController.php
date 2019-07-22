<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Currency;
use App\Category;
use App\Setting;
/*use Session;*/
use App\Product;
use App\Post;
use App\Page;
use App\Slider;
use App;
use Mail;
use URL;
use DataTables;
class ApiController extends Controller
{
  // about us
  public function aboutUs()
  {
    $result = Page::where('slug','about')->first();
    $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    return  $responce;
  }

  // privacy policy
  public function privacyPolicy()
  {
    $result = Page::where('slug','policy-privacy')->first();
    $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    return  $responce;
  }

  // terms & conditions
  public function termsConditions()
  {
    $result = Page::where('slug','tos')->first();
    $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    return  $responce;
  }

  // contact us
  public function contact_send(Request $request)
  {
    /*  print_r($request); die;*/
     
     $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Thank you for Contacting us,We will get back to you shortly'
                      );
    return  $responce;
  }


  

  
}
