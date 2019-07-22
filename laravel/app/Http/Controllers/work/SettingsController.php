<?php

namespace App\Http\Controllers\work;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use App\Product;
use App\Gateway;
use App;
use URL;
use Session;
class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
     {
         $this->middleware('auth:admin');
     }
    public function index()
    {
      $gateways  =  Gateway::first();

        $settings =Setting::first();
        return view('work.settings.index')
        ->with('settings',$settings)
        ->with('gateways',$gateways);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sitemap()
    {
        // dd('here');
        // create new sitemap object
$sitemap = App::make('sitemap');

// get all products from db (or wherever you store them)
$products = \DB::table('products')->orderBy('created_at', 'desc')->get();

// counters
$counter = 0;
$sitemapCounter = 0;
$productCounter = 0;
// add every product to multiple sitemaps with one sitemap index
foreach ($products as $product) {
  if ($counter == 50000) {
    // generate new sitemap file
    $sitemap->store('xml', 'sitemap-' . $sitemapCounter);
    // add the file to the sitemaps array
    $sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
    // reset items array (clear memory)
    $sitemap->model->resetItems();
    // reset the counter
    $counter = 0;
    // count generated sitemap
    $sitemapCounter++;
  }

  // add product to items array
  $product_url = url('/').'/'.$product->slug.'-'.$product->id;
  $sitemap->add($product_url, $product->created_at, '1.0', 'daily');
  // count number of elements
  $counter++;
  $productCounter++;
}

// you need to check for unused items
if (!empty($sitemap->model->getItems())) {
  // generate sitemap with last items
  $sitemap->store('xml', 'sitemap-' . $sitemapCounter);
  // add sitemap to sitemaps array
  $sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
  // reset items array
  $sitemap->model->resetItems();
}
$Totalsitemaps = $sitemapCounter+1;
// generate new sitemapindex that will contain all generated sitemaps above
$sitemap->store('sitemapindex', 'sitemap');
Session::flash('info', "Added $productCounter Products");
Session::flash('success', "Successfully Created $Totalsitemaps Sitemap(s)");
return redirect()->route('work.settings');
    }

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
    public function update(Request $request)
    {
        // dd($request->all());
        $settings =Setting::first();
        if($settings->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
        $gateways =Gateway::first();
        $this->validate($request,[
          'home_rand_pro'=>'required',
          'home_posts'=>'required',
          'home_users'=>'required',
          'compare_percentage'=>'required',
          'compared_products'=>'required',
          'buy_button'=>'required',
          'search_element'=>'required',
          'search_order'=>'required',
          'site_name'=>'required',
          'currency_symbol'=>'required',
          'currency_code'=>'required',
          'site_email'=>'required',

      ]);

        if ($request->hasFile('image')){
            if (file_exists($settings->logo)){
              unlink($settings->logo);
            }
            $image = $request->image;
            $image_new_name = time().$image->getClientOriginalName();
            $image->move('uploads/logo/',$image_new_name);
            $settings->logo = 'uploads/logo/'.$image_new_name;
            // $user->save();
        }

$paypal_active       = !empty($request->paypal_active) ? 1 : 0;
$stripe_active       = !empty($request->stripe_active) ? 1 : 0;
$voguepay_active     = !empty($request->voguepay_active) ? 1 : 0;
$bankwire_active     = !empty($request->bankwire_active) ? 1 : 0;

$gateways-> paypal_client_id         = $request-> paypal_client_id;
$gateways-> paypal_client_secret     = $request-> paypal_client_secret;
$gateways-> stripe_publishable_key   = $request-> stripe_publishable_key;
$gateways-> stripe_secret_key        = $request-> stripe_secret_key;
$gateways-> voguepay_merchant_id     = $request-> voguepay_merchant_id;
$gateways-> bankwire_active          = $bankwire_active;
$gateways-> voguepay_active          = $voguepay_active;
$gateways-> stripe_active            = $stripe_active;
$gateways-> paypal_active            = $paypal_active;
$gateways->save();


  $settings-> social_facebook      = $request->social_facebook;
  $settings-> social_twitter       = $request->social_twitter;
  $settings-> social_instagram     = $request->social_instagram;
  $settings-> home_rand_pro        = $request->home_rand_pro;
  $settings-> home_posts           = $request->home_posts;
  $settings-> home_users           = $request->home_users;
  $settings-> csv_import_limit     = $request->csv_import_limit;
  $settings-> compare_percentage   = $request->compare_percentage;
  $settings-> compared_products    = $request->compared_products;
  $settings-> buy_button           = $request->buy_button;
  $settings-> search_element       = $request->search_element;
  $settings-> search_order         = $request->search_order;
  $settings-> site_name            = $request->site_name;
  $settings-> meta_name            = $request->meta_name;
  $settings-> site_about           = $request->site_about;
  $settings-> keywords             = $request->keywords;
  $settings-> currency_symbol      = $request->currency_symbol;
  $settings-> currency_name        = $request->currency_name;
  $settings-> currency_code        = $request->currency_code;
  $settings-> site_email           = $request->site_email;
  $settings-> site_number          = $request->site_number;
  $settings-> disqus                   = $request-> disqus;



        $settings->save();
        Session::flash('success','Successfully Updated Settings');
        return redirect()->route('work.settings');
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
