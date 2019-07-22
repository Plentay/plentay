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
use App\CrawlerKonga;
class KongaController extends Controller
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
       $site_settings = Setting::first();
       $crawler_konga = CrawlerKonga::first();
       $categories = Category::attr(['name' => 'category_id', 'class' => 'form-control show-tick'])
             ->selected(1)
             ->renderAsDropdown();
        return view('work.crawl.konga')
        ->with('users',User::all())
        ->with ('categories',$categories)
        ->with ('crawler_konga',$crawler_konga);
    }
    public function edit_konga()
    {
       // $site_settings = Setting::first();
       $crawler_konga = CrawlerKonga::first();
        return view('work.crawl.konga_affiliate')
         ->with ('crawler_konga',$crawler_konga);
    }
    public function save_konga(Request $request)
    {
      $this->validate($request,[
      'affiliate_id'=>'required',
    ]);
    $crawler_konga = CrawlerKonga::first();
    $crawler_konga-> affiliate_id = $request->affiliate_id;
    $crawler_konga->save();
    Session::flash('success','Success');
    return redirect()->route('work.crawl.konga');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function konga_run(Request $request)
    {

error_reporting(0);
ini_set('max_execution_time', -1);//unlimited execution

$this->validate($request,[
'user_id'=>'required',
'category_id'=>'required',
'keywords'=>'required',
]);

$depth          	  = $request->depth;//products per page
$page	            	= $request->page;//start page
$max_page	          = $request->max_page;
$minimum_price	    = $request->minimum_price;
$sort 			        = '?sort=Price%3A+High+to+Low&dir=desc';
///////////////////////////////////////////////////////////
// Declearations        user_id
$product_merchant       = $request->user_id;
$keywords           	  = $request->keywords;
$keywords 		          = str_replace(' ', '+', $keywords);
$konga_db               = CrawlerKonga::first();
$next_constant 		      = 9; //maximuM products that can be pulled from a page bason on provided json
$depth          	      = $request->depth;//products per page
$page	            	    = $request->page;//start page
$page 			            = $page*$next_constant;
$max_page	              = $request->max_page;
$max_page 	            = $max_page*$next_constant;
$minimum_price	        = $request->minimum_price;

// $sort			              ="&sort=price:desc";
$sort			              ="&sort=price_desc";

$affiliate_id 		      ="?k_id=$konga_db->affiliate_id";
$products 	  	        = [];

//////////////////////////////////////////////////////////////////
////// Get product blocks and extract info (also insert to db) /////
//////////////////////////////////////////////////////////////////
//for visual pruposes only
$dis_page     = $page/$next_constant;
$dis_max_page = $max_page/$next_constant;
$dis_total_pages = $dis_max_page-$dis_page;
// echo "<p>$keywords  <== Keyword(s) </p>";
// echo "<p>$dis_page  <== Start Page</p>";
// echo "<p>$dis_max_page  <== End Page </p>";
// echo "<p><strong>$dis_total_pages</strong>  <==Total Pages </p><br>";
//for visual pruposes only

while ($page <= $max_page){ //pages
$json_string = "https://www.konga.com/v1/json/search?q=$keywords&from=$page$sort";
$jsondata = file_get_contents($json_string);
$obj = json_decode($jsondata, true);
dd($obj );
$i 		  = 1;
$arr_keys 	  = -1;
$imported     = 0;
$count_total_run  = 0;
$count_skipped_exists = 0;
$count_skipped_incomplete = 0;

//for ($x = 1; $x <= 9; $x++) {
for ($x = 1; $x <= 9; $x++) {
	$count_total_run ++;
	$arr_keys++;
  // dump($arr_keys);

        if ($i > $depth) {
                break;
        }

		$data = $obj['data'];
		$products_element = $data['products'];
    $key = $products_element[$arr_keys];//singles out a product from the json list
    // dump($key);
    // dd($key);

    $fields = $key['fields'];
		//unset ($fields);
		// print_r($fields);
    // dd($fields);
		// get title attribute
		$product_title		 = $fields['name'];

		// get product_url_slug attribute
		$product_url_slug = str_slug($product_title);

		// get description attribute
		$product_description = $product_title;
		// get price attribute
		$product_price		 = $fields['price'];
		$product_price 		 = str_replace(' ', '', $product_price);
		$product_price 		 = str_replace(',', '', $product_price);
		// get image attribute
		$product_image		 = "https://images.konga.com/v2/media/catalog/product/".$fields['image_thumbnail_path'];
		// get href attribute
		$product_original_url = $fields['url_key'];
		$product_original_url = "https://www.konga.com/".$product_original_url;

    if (Product::where('original_url', '=',$product_original_url)->exists()&& !empty($product_price)) {
        $product = Product::where('original_url',$product_original_url)->first();
        $product->amount = $product_price;
        $product->save();
        $count_skipped_exists++;
        continue;
        //skip if the product exists //verifying via original url
    }

		// get Affiliate href attribute
        $product_affiliate_url =  $product_original_url.$affiliate_id;

		//check for empty fields and rows
          if (empty($product_title) || empty($product_original_url) || empty($product_image) || empty($product_price)) {
          //echo "ONE OR MORE FIELDS IS EMPTY Skipped";
         // echo "Title $ProductTitle <br>";
          //echo "Url $ProductUrl <br>";
          //echo "Img $ProductImage <br>";
          //echo "Price $ProductPrice </hr>";
          $count_skipped_incomplete++;
          continue;
          }

// push to a list of products
if ($product_price >= $minimum_price){
//display purposes
  $products[] = [
            'name'=>$product_title,
            'description'=>$product_description,
            'category_id'=>$request->category_id,
            'amount'=>$product_price,
            'user_id'=>$product_merchant,
            'original_url'=>$product_original_url,
            'affiliate_url'=>$product_affiliate_url,
            'slug'=>$product_url_slug,
            'image'=>$product_image,
          ];

  $product = Product::create([
    'name'=>$product_title,
    'description'=>$product_description,
    'category_id'=>$request->category_id,
    'amount'=>$product_price,
    'user_id'=>$product_merchant,
    'original_url'=>$product_original_url,
    'affiliate_url'=>$product_affiliate_url,
    'slug'=>$product_url_slug,
    'image'=>$product_image,
  ]);

//Repetitive Insert command on each row
$imported++;
}

$i++;

}
$page += 9;
}

// echo "<div class='alert alert-info'>";
// echo "<pre>";
// print_r($products);
// echo "</div>";

//echo "<pre>";

//echo "<pre>";

//var_dump($products);
//print_r($products);


$total_imported = count($products);
return redirect()->back()->with('message',"<div class='text-center'
                         style=' width: auto;
                          padding: 10px;
                          border: 5px solid gray;
                          margin: 0;''>
                          <div style='Color:black'> Keywords: $keywords </div><br />
                          <div style='Color:green'> Imported $total_imported </div><br />
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
