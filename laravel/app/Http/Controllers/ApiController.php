<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Currency;
use App\Category;
use App\Setting;
use App\Product;
use App\Post;
use App\Page;
use App\Slider;
use App;
use Mail;
use URL;
use DataTables;
use DB; 
  
class ApiController extends Controller
{

  function __construct() {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, X-Access-Token,X-Key, Content-Type, Accept, Access-Control-Request-Method,version,lang,user_id,token");
  }
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

  // Slider
  public function sliders()
  {
    $result = DB::table('sliders')->orderBy('url', 'asc')->get();
    if(count($result) > 0){
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    }else{
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Not found sliders'
                      );
    }
    return  $responce;
  }

  // Brands
  public function brands()
  {
    $result = DB::table('brands')->orderBy('id', 'asc')->get();
    if(count($result) > 0){
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    }else{
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Not found sliders'
                      );
    }
    return  $responce;
  }

  // contact us
  public function contact_send(Request $request)
  {
      $name = $request->input('name');
      $mobile_number = $request->input('mobile_number');
      $subject = $request->input('subject');
      $message = $request->input('message');

      $result = array('name' => $name, 
        'mobile_number' => $mobile_number, 
        'subject' => $subject, 
        'message' => $message,
        'create_dt' => time()
      );
      DB::table('contact_us')->insert($result);   
      $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Thank you for Contacting us,We will get back to you shortly'
                      );
    return  $responce;
  }


  //Parent Child Categoty
public function allCategory()
{
  $result = Category::where('parent_id','1')->get();
  if(count($result) > 0){
        $sts = array();
    foreach ($result as $key => $value) {
          $category_id = $value['id'];
          $st = $this->chield_category($category_id);
          $sts[] = array('parent' => $value,
                        'chield_category' => $st);
      }
  }
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $sts
                    );
    return  $responce;
}

public function chield_category($category_id)
{
   $sts = array();
   $result = Category::where('parent_id',$category_id)->get();

   if(!empty($result) && count($result) > 0){
   foreach ($result as $key => $value) {
          $category_id = $value['id'];
          $st = $this->sub_chield_category($category_id);
          $sts[] = array('id' => $value['id'],
                       'parent_id' => $value['parent_id'],
                       'name' => $value['name'],
                       'slug' => $value['slug'],
                       'description' => $value['description'],
                       'sub_chield_category' => $st
                     );
      }
 }
  return $sts;
}


public function sub_chield_category($category_id)
{
   $sts = array();
   $result = Category::where('parent_id',$category_id)->get();

   if(!empty($result) && count($result) > 0){
   foreach ($result as $key => $value) {
         $sts[] = array('id' => $value['id'],
                       'parent_id' => $value['parent_id'],
                       'name' => $value['name'],
                       'slug' => $value['slug'],
                       'description' => $value['description'],
                     );
      }
 }
  return $sts;
}
  //Parent Categoty
  public function parentCategory()
  {
    $result = Category::where('parent_id','0')->get();
    $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $result
                      );
    return  $responce;
  }
  
  //Parent Child Categoty
  public function childCategory(Request $request)
  {
      $category_id = $request->input('category_id');

      if(empty($category_id)){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Please send category id.'
                      );
        return  $responce; 
      }

      $result = Category::where('parent_id',$category_id)->get();
      if(count($result) > 0){
        foreach ($result as $key => $value) {

            $child_id = $value['id']; 
            $child_result = Category::where('parent_id',$child_id)->get();
            $st[] = array('category' => $value,
                          'sub_category' => $child_result);
        }
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $st
                      );
      }else{
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Not found category'
                      );
      }
      return  $responce;
  }

  //Product Detail 
  public function productDetail(Request $request)
  {
      $product_id = $request->input('product_id');
      if(empty($product_id)){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Please send product id.'
                      );
        return  $responce; 
      }
      $result = DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->join('brands as b', 'b.id', '=', 'p.brand_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->where('p.active', '=', 1)
            ->where('p.id', '=', $product_id)
            ->select('p.*', 'p.amount as price', 'p.image as pictures', 'p.description as shortDetails', 'p.description as description',   'b.title as brand_name', 'b.image as brand_image', 'c.name as category', 'c.name as category_description','u.name as marchant_name', 'u.email', 'u.contact_name','u.address','u.url','u.image','u.about','u.phone_number')
            ->addSelect(DB::raw("'' as salePrice"))
            ->addSelect(DB::raw("'' as discount"))
            ->addSelect(DB::raw("'250' as stock"))
            ->addSelect(DB::raw("'true' as new"))
            ->addSelect(DB::raw("'true' as sale"))
            ->addSelect(DB::raw("'' as colors"))
            ->addSelect(DB::raw("'' as size"))
            ->addSelect(DB::raw("'' as tags"))
            ->addSelect(DB::raw("'' as variants"))
            ->get();

      if(count($result) > 0){
         foreach ($result as $key => $value) {
          $value = (array)$value; 
         // echo '<pre/>'; print_r($value); die; 
          $colors = array();
          $size = array();
          $tags = array($value['category_name']);
          $variants = array();
          $pictures = array($value['pictures']);

          $data[] = array("id" => $value['id'],
                          "name" => $value['name'],
                          "price" => $value['price'],
                           "salePrice" => $value['price'],
                           "discount" => $value['discount'],
                           "pictures" => $pictures,
                           "shortDetails" => $value['description'],
                           "description" => $value['description'],
                           "stock" => $value['stock'],
                           "new" => $value['new'],
                           "sale" => $value['sale'],
                           "category" => $value['category_name'],
                           "colors" => $colors,
                           "size" => $size,
                           "tags" => $tags,
                           "variants" => $variants
                        );
        }
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $data
                      );
      }else{
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Not found product'
                      );
      }
      return  $responce;
  }

  //Products grid 
  public function products(Request $request)
  {
      $types = $request->input('types');
      $result = DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->join('brands as b', 'b.id', '=', 'p.brand_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->where('p.active', '=', 1)
           // ->where('p.id', '=', $product_id)
            ->select('p.*',  'p.amount as price', 'p.image as pictures', 'p.description as shortDetails', 'p.description as description','b.title as brand_name', 'b.image as brand_image','c.name as category_name', 'c.name as category_description','u.name as marchant_name', 'u.email', 'u.contact_name','u.address','u.url','u.image','u.about','u.phone_number')
             ->addSelect(DB::raw("'' as salePrice"))
            ->addSelect(DB::raw("'' as discount"))
            ->addSelect(DB::raw("'' as stock"))
            ->addSelect(DB::raw("'true' as new"))
            ->addSelect(DB::raw("'true' as sale"))
            ->addSelect(DB::raw("'' as colors"))
            ->addSelect(DB::raw("'' as size"))
            ->addSelect(DB::raw("'' as tags"))
            ->addSelect(DB::raw("'' as variants"))
            ->limit(25)
            ->get();

      if(count($result) > 0){
         foreach ($result as $key => $value) {
          $value = (array)$value; 
         // echo '<pre/>'; print_r($value); die; 
          $colors = array();
          $size = array();
          $tags = array($value['category_name']);
          $variants = array();
          $pictures = array($value['pictures']);

          $data[] = array("id" => $value['id'],
                          "name" => $value['name'],
                          "price" => $value['price'],
                           "salePrice" => $value['price'],
                           "discount" => $value['discount'],
                           "pictures" => $pictures,
                           "shortDetails" => $value['description'],
                           "description" => $value['description'],
                           "stock" => $value['stock'],
                           "new" => $value['new'],
                            "sale" => $value['sale'],
                            "category" => $value['category_name'],
                            "colors" => $colors,
                            "size" => $size,
                            "tags" => $tags,
                            "variants" => $variants
                        );
        }
          $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'result' => $data
                      );
      }else{
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Not found product'
                      );
      }
      return  $responce;
  }

  //Company Registration 
  public function companyRegistration(Request $request)
  {
      $name = $request->input('name');
      $email = $request->input('email');
      $password  = $request->input('password');
      $contact_name = $request->input('contact_name');
      $address = $request->input('address');
      $image = $request->input('image');
      $url = $request->input('url');
      $about = $request->input('about');
      $phone_number = $request->input('phone_number');
      $confirmation_code = str_random(60);
      
      $result = User::where('email',$email)->get();
      if(count($result) > 0){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'This email is taken by another account'
                      );
        return  $responce; 
      }

      $result = User::where('name',$name)->get();
      if(count($result) > 0){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'This name is taken by another account'
                      );
        return  $responce; 
      }

      $result = array('name' => str_slug($name), 
        'email' => $email, 
        'password' => Hash::make($password), 
        'contact_name' => $contact_name,
        'address' => $address, 
        'url' => $url, 
        'about' => $about,
        'phone_number' => $phone_number,
        'validation_code'=> $confirmation_code,
        'role_id' => 1,
        'active' => 0,
        'company_id' => 0,
        'image' => '',
        'credit' => '0.00',
        'currency_id' => '147',
        'price_update_block' => '',
        'price_update_element' => '',
        'description_update_element' => '',
        'remember_token' => ''
      );
      DB::table('users')->insert($result);  

      $settings =Setting::first();
        $email_data = array(
          'name' => $name,
          'email' => $email,
          'confirmation_code' => $confirmation_code,
          'settings' => $settings,
       );
        /*Mail::send('emails.account_verify', $email_data, function($message)use($email_data,$settings)  {
            $message->from($settings->site_email,$settings->site_name);
            $message->to($email_data['email'], $email_data['name']);
            $message->subject('Verify your email address');
        });*/

      $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Thanks for signing up! Please check your email.'
                      );
    return  $responce;
  }

  //login
  public function login(Request $request)
  {
      $email = $request->input('email');
      $password  = $request->input('password');

      if(empty($email)){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Please send email id'
                      );
        return  $responce; 
      }else if(empty($password)){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Please send password'
                      );
        return  $responce; 
      }

      
      $result = User::where('email',$email)->get();
      if(count($result) < 1){
        $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'This email is not available'
                      );
        return  $responce; 
      }else{
        if($result[0]['active'] == '0'){
          $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Your account is deactive please varify your account.'
                      );
          return  $responce; 
        }
      }
      if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1]))
      {
            $responce = array('status' => 1,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Successfully login',
                      'result' => $result[0]
                      );
            return  $responce;
      }else{
         $responce = array('status' => 0,
                      'error_code' => 0,
                      'error_line' => __line__,
                      'message' => 'Password not match'
                      );
        return  $responce; 
      }

     

      
      
    
  }


  
}
