<?php

namespace App\Http\Controllers;

use Request;
use App\Category;
use App\Setting;
use Session;
use App\Product;
use App\Page;
use App\Credit;
use App\Report;
use App\User;
use Redirect;
class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function products()
    {
      $settings =Setting::first();
      // $posts = Post::orderBy('id', 'desc')->take(6)->get();
      $products = Product::orderBy('id', 'desc')->paginate(9);
      return view('products')
      ->with('products',$products)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);
    }
    public function search()
    {
      $query = request()->get('query');
      if (empty($query)) {
            Session::flash('info','Query Is Empty');
          return redirect('/products');
      }
      $min_length = 3;
      if(strlen($query) < $min_length){
      Session::flash('info',"Minimum Length is $min_length");
      return redirect('/products');
      }
      $settings =Setting::first();
      $products = Product::where('name','like',  '%' . $query . '%')
      ->orwhere('description','like',  '%' . $query . '%')->orderBy($settings->search_element, $settings->search_order)->paginate(9);

      return view('search')
      ->with('query',$query)
      ->with('products',$products)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);
    }
    public function product_page($slug, $id)
    {
   
      $url_path = $slug.'-'.$id;
      //get correct slog
      $slug = substr( $url_path, 0, strrpos( $url_path, '-' ) );
      //get correct id
      $id = trim($url_path,'-');
      $id = explode('-',$id);
      $id = end($id);

      $settings =Setting::first();
      $product = Product::where('slug',$slug)->where('id',$id)->first();
      if (empty($product)) {
            Session::flash('warning','Product was not found');
          return redirect('/products');
      }
      // $pro_id = Product::where('id',$id)->first();
      ////////// Product Views Updater //////////
      $product->views_count=$product->views_count+1;
      $product->save();
      ////////// Product Views Updater //////////
      //Compare
      $query = $product->name;
      $amount = $product->amount;
      $product_id = $product->id;
      $compared_products = Product::where('amount', '>=', $amount)->where('id', '!=',  $product_id)->limit(6)->orderBy('amount', 'asc')->get();

      if(count($compared_products) == '0'){
          $compared_products = Product::where('amount', '<', $amount)->where('id', '!=',  $product_id)->limit(6)->orderBy('amount', 'desc')->get();
      }

    
      /*$compared_products_search = Product::where('name','like',  '%' . $query . '%')->orderBy('amount', 'asc')->get();
     $compared_products = array();
      $count=0;
      foreach($compared_products_search as $ini_compared_product) {

       if($count == $settings->compared_products) break;
       $count++;
       similar_text($product->name, $ini_compared_product->name, $perc); //12
       $Compare = $settings->compare_percentage;
             if  ($perc <$Compare){
               continue;
             }
             // removes active product from the table
             if ($product->id == $ini_compared_product->id ){
                continue;
             }
       $compared_products[] = $ini_compared_product;
      }*/

      // $arr  = $compared_products;
      // $sort = array();
      // foreach($arr as $k=>$v) {
      //     $sort['amount'][$k] = $v['amount'];
      // }
      //
      // array_multisort($sort['amount'], SORT_DESC, $arr);
      //
      // echo "<pre>";
      // print_r($arr);
      // dd();

      // dump(count($compared_products));
      // dd($compared_products);


        // similar_text($product->name, $compared_product->name, $perc); //12
        // $Compare = $settings->compare_percentage;
        //       if  ($perc <$Compare){
        //         continue;
        //       }
        //       if ($compared_product->id = $product->id){
        //           continue;
        //       }

      //get random Products
      $rand_products = Product::inRandomOrder()->limit(5)->get();
      return view('product_page')
      ->with('product',$product)
      ->with('compared_products',$compared_products)
      ->with('rand_products',$rand_products)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);
    }
    public function link($id)
    {
      // dd(url('/'));
      $settings = Setting::first();
      $credit = Credit::first();
      $product = Product::where('id',$id)->first();
      //checks if the product was found
      if (empty($product)) {
            Session::flash('warning','Link Error Check URL');
          return redirect('/products');
      }
      //checks the balance of merchant
      if ($product->user->credit < $credit->rate_per_click){
        Session::flash('warning','Insufficient Credit, Contact Admin');
        return redirect('/products');
      }
      //substracts click form merchant credits
      $user = User::where('id',$product->user_id)->first();
      $user->credit = $user->credit - $credit->rate_per_click;
      $user->save();
      //adds click count to product
      $product->click_count=$product->click_count+1;
      $product->save();

      ///////////////////////////////////////////////////////get ip
//       function get_ip_address() {
//     // check for shared internet/ISP IP
//     if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
//         return $_SERVER['HTTP_CLIENT_IP'];
//     }

//     // check for IPs passing through proxies
//     if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//         // check if multiple ips exist in var
//         if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
//             $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//             foreach ($iplist as $ip) {
//                 if (validate_ip($ip))
//                     return $ip;
//             }
//         } else {
//             if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
//                 return $_SERVER['HTTP_X_FORWARDED_FOR'];
//         }
//     }
//     if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
//         return $_SERVER['HTTP_X_FORWARDED'];
//     if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
//         return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
//     if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
//         return $_SERVER['HTTP_FORWARDED_FOR'];
//     if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
//         return $_SERVER['HTTP_FORWARDED'];

//     // return unreliable ip since all else failed
//     return $_SERVER['REMOTE_ADDR'];
// }
//////////////////////////////////////////////////////////////////////get ip
      ///Insert Report Click Details *New Row
    //   $click_ipaddress        = get_ip_address();
      $click_ipaddress        = Request::ip();
      $report = Report::create([
          'ip_address'=>$click_ipaddress,
          'source'=>url('/').'/'.$product->slug.'-'.$product->id,
          'destination'=>$product->affiliate_url,
          'user_id'=>$product->user->id,
        ]);
  return Redirect::away($product->affiliate_url);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
