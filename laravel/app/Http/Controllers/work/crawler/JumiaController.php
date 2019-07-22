<?php

namespace App\Http\Controllers\work\crawler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Goutte\Client;
use Sunra\PhpSimple\HtmlDomParser;
use App\Setting;
use Session;
use App\Product;
use App\User;
use App\Category;
use App\CrawlerJumia;
class JumiaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct()
     {
         $this->middleware('auth:admin');
         // $site_settings = Setting::first();
     }
    public function index()
    {
      $crawler_jumia        = CrawlerJumia::first();
      $categories = Category::attr(['name' => 'category_id', 'class' => 'form-control show-tick'])
            ->selected(1)
            ->renderAsDropdown();
       return view('work.crawl.jumia')
       ->with('users',User::all())
       ->with ('categories',$categories)
       ->with ('crawler_jumia',$crawler_jumia);
    }
    public function edit_jumia()
    {
       // $site_settings = Setting::first();
       $crawler_jumia = CrawlerJumia::first();
        return view('work.crawl.jumia_affiliate')
         ->with ('crawler_jumia',$crawler_jumia);
    }
    public function save_jumia(Request $request)
    {
      $this->validate($request,[
      'affiliate_id_start'=>'required',
      'affiliate_id_end'=>'required',
      'product_block_ini'=>'required',
      'product_name_element'=>'required',
      'product_url_element'=>'required',
      'product_image_element'=>'required',
      'product_price_element'=>'required',
    ]);
    $crawler_jumia = CrawlerJumia::first();
    $crawler_jumia-> affiliate_id_start    = $request->affiliate_id_start;
    $crawler_jumia-> affiliate_id_end      = $request->affiliate_id_end;
    $crawler_jumia-> product_block_ini     = $request->product_block_ini;
    $crawler_jumia-> product_name_element  = $request->product_name_element;
    $crawler_jumia-> product_url_element   = $request->product_url_element;
    $crawler_jumia-> product_image_element = $request->product_image_element;
    $crawler_jumia-> product_price_element = $request->product_price_element;
    $crawler_jumia->save();
    Session::flash('success','Success');
    return redirect()->route('work.crawl.jumia');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
public function jumia_run(Request $request)
{
  $this->validate($request,[
  'user_id'=>'required',
  'category_id'=>'required',
  'keywords'=>'required',
  ]);
  error_reporting(0);
  ini_set('max_execution_time', -1);
  ///------------------------------Session-----------------------------///
  Session::put('i',  1);
  Session::put('total_imported',  0);
  Session::put('count_skipped_exists',  0);
  Session::put('count_skipped_incomplete',  0);
  Session::put('count_skipped_updated_prices',  0);
  Session::put('count_total_run', 0);
  ///------------------------------Session-----------------------------///
  // Declearations
  $product_merchant      = $request->user_id;
  $product_category      = $request->category_id;
  $CrawlerJumia          = CrawlerJumia::first();
  $affiliate_id_start    = $CrawlerJumia->affiliate_id_start;
  $affiliate_id_end      = $CrawlerJumia->affiliate_id_end;
  $product_block_ini     = $CrawlerJumia->product_block_ini;//'div.sku'
  $product_name_element  = $CrawlerJumia->product_name_element; //span[class="name"]
  $product_url_element   = $CrawlerJumia->product_url_element;
  $product_image_element = $CrawlerJumia->product_image_element;
  $product_price_element = $CrawlerJumia->product_price_element;
  //keywords
  $keywords           	 = $request->keywords;
  $keywords 		         = str_replace(' ', '+', $keywords);
  $depth          	     = $request->depth;//products per page
  $page	            	   = $request->page;//start page
  // dd($page.' Page )-|-( Depth Products per Page '.$depth);
  Session::put('page',  $page);
  $max_page	             = $request->max_page;
  $minimum_price	       = $request->minimum_price;
  // $sort 			           = '?sort=Price%3A+High+to+Low&dir=desc';
  ////////////////demo//////////////
  $settings =Setting::first();
  if($settings->live_production==0 && $request->depth > 1){
    Session::flash('info', 'On demo imports are limited to 1 Page');
    return redirect()->back();
  }
  ////////////////demo//////////////

////////////////////////////////////////////////////////////////////
////// Get product blocks and extract info (also insert to db) /////
////////////////////////////////////////////////////////////////////
// $page = Session::get('page');
while (Session::get('page') <= $max_page){
  $page = Session::get('page');
// sleep(rand(1,2)) ;// periodic cool down. helps to avoid ban.

$client = new Client();
$client->setHeader("user-agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0");
$crawler = $client->request('GET', "https://www.jumia.com.ng/catalog/?q=$keywords&page=$page");//non sort

$crawler->filter($product_block_ini)->each(function($node)
use($product_name_element,
    $product_url_element,
    $product_image_element,
    $product_price_element,
    $affiliate_id_start,
    $affiliate_id_end,
    $product_category,
    $product_merchant,
    $keywords,
    $depth,
    $max_page,
    $minimum_price,
    $settings){ //div.s-item__wrapper //globals

static $i = 1;
// ///------------Session-----------///
// $i = Session::get('i');
// ///------------Session-----------///
  if ($i > $depth) {
  $count_total_run ++;
          // break;
          // alert()->success('Err', "I $i  >  Depth $depth")->showCloseButton()->autoClose(15000);
          return false;//skip

  }
$product_name='';
$product_price='';
$product_image='';
$product_original_url ='';


//sub elements
if ($node->filter($product_name_element)->count() > 0 ) {//a.vip
    $product_name = $node->filter($product_name_element)->text();
}

if ($node->filter($product_price_element)->count() > 0 ) {//span.bold
  $product_price = $node->filter($product_price_element)->text();
  // $price=substr($price, 0, strrpos($price, ' '));
  // $price = strtok($price, 'to');
  // // $price = preg_replace('/[^\p{L}\p{N}\s]/u', '', $price); //removes special charaters
  // $price = preg_replace("/[^0-9.]/", "", $price);
  // $price = str_replace(',', '', $price);
  // $product_price = str_replace(' ', '', $price);
  $product_price              = str_replace(',', '', $product_price);
  $product_price              = str_replace(' ', '', $product_price);
  $product_price              = preg_replace("/[^0-9.,]/", "", $product_price);
  $product_price              = preg_replace('/D/', '', $product_price);//floatval($num);
}
if ($node->filter($product_image_element)->count() > 0 ) { //a.img.imgWr2 .imgWr2
  $product_image = $node->filter($product_image_element)->attr('data-src');
}
if ($node->filter($product_url_element)->count() > 0 ) {
  $product_original_url = $node->filter($product_url_element)->link()->getUri();
}
//sub elements

//

  // get description attribute
  $product_description =  $product_name;
  // get product_url_slug attribute
  $product_url_slug = str_slug($product_name);
  // get Affiliate href attribute
  $product_affiliate_url = $affiliate_id_start.$product_original_url.$affiliate_id_end;
  //direct   $product_affiliate_url = str_replace('https://www.jumia.com.ng/','https%3A%2F%2Fwww.jumia.com.ng%2F', $product_affiliate_url);
  $product_affiliate_url = empty($affiliate_id_start || $affiliate_id_end) ? $product_original_url:str_replace('https://www.jumia.com.ng/','https%3A%2F%2Fwww.jumia.com.ng%2F', $product_affiliate_url);

  // if (Product::where('original_url', '=',$product_original_url)->exists()&& !empty($product_price)) {
  //     $product = Product::where('original_url',$product_original_url)->first();
  //     $product->amount = $ProductPrice;
  //     $product->save();
  //     $count_total_run ++;
  //    $count_skipped_exists++;
  //     continue;
  //     //skip if the product exists //verifying via slug
  // }

  //check for empty fields and rows
  if (empty($product_name) || empty($product_original_url) || empty($product_image) || empty($product_price)) {
  ///------------Session-----------///
  $count_skipped_incomplete = Session::get('count_skipped_incomplete');
  $count_skipped_incomplete++;
  Session::put('count_skipped_incomplete', $count_skipped_incomplete);
  //-------------------------------//
  $count_total_run = Session::get('count_total_run');
  $count_total_run++;
  Session::put('count_total_run', $count_total_run);
  ///------------Session-----------///
   return true;
  }

 // dump($product_name);
 // dump($product_price);
 // dump($product_image);
 // dump($product_original_url);
 // dump($product_affiliate_url);
 // dump($affiliate_id_start);
 // dump($affiliate_id_end);
 // dd("here");

 // $data = array(
 //   'name' => $product_name,
 //   'supplier_price'=>$product_price,
 //   'image' => $product_image,
 //   'original_url' => $product_original_url,
 //    );
 //    dump($data);// simulate save array



  if ($product_price >= $minimum_price){
      // start import
      $product = Product::create([
        'name'=>$product_name,
        'description'=>$product_description,
        'category_id'=>$product_category,
        'amount'=>$product_price,
        'user_id'=>$product_merchant,
        'original_url'=>$product_original_url,
        'affiliate_url'=>$product_affiliate_url,
        'slug'=>$product_url_slug,
        'image'=>$product_image,
      ]);

      ///------------Session-----------///
      $total_imported = Session::get('total_imported');
      $total_imported++;
      Session::put('total_imported', $total_imported);
      //-------------------------------//
      $count_total_run = Session::get('count_total_run');
      $count_total_run++;
      Session::put('count_total_run', $count_total_run);
      ///------------Session-----------///

  }//end import

///------------Session-----------///
// $i = Session::get('i');
$i++;
// Session::put('i', $i);
//-------------------------------//
$count_total_run = Session::get('count_total_run');
$count_total_run++;
Session::put('count_total_run', $count_total_run);
///------------Session-----------///
});


$page = Session::get('page');
$page += 1;
Session::put('page', $page);




}
///------------Session-----------///
$i = Session::get('i');
$total_imported = Session::get('total_imported');
$count_skipped_exists = Session::get('count_skipped_exists');
$count_skipped_incomplete = Session::get('count_skipped_incomplete');
$count_skipped_updated_prices = Session::get('count_skipped_updated_prices');
$count_total_run = Session::get('count_total_run');
//-----------UNSET--------------------//
Session::forget('i');
Session::forget('total_imported');
Session::forget('count_skipped_exists');
Session::forget('count_skipped_incomplete');
Session::forget('count_skipped_updated_prices');
Session::forget('count_total_run');
Session::forget('page');

///------------Session-----------///
// dump($i ." i");
// dump($total_imported ." total_imported");
// dump($count_skipped_exists ." count_skipped_exists");
// dump($count_skipped_incomplete ." count_skipped_incomplete");
// dump($count_skipped_updated_prices ." count_skipped_updated_prices");
// dump($count_total_run ." count_total_run");
// dd("END");
// $grand_total_imported = count($products);
return redirect()->back()->with('message',"<div class='text-center'
                          style=' width: auto;
                          padding: 10px;
                          border: 5px solid gray;
                          margin: 0;''>
                          <div style='Color:black'> Keywords: $keywords</div><br />
                          <div style='Color:green'> Imported ($total_imported) Products</div><br />
                          <div style='Color:blue'> Price Updated $count_skipped_exists </div><br />
                          <div style='Color:red'> Skipped $count_skipped_incomplete </div>
                        </div>")
                        ->with('products', $products);

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
