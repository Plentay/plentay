<?php

namespace App\Http\Controllers\account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Goutte\Client;
use Auth;
use App\Category;
use App\Product;
use App\User;
use App\Currency;
use App\Setting;
use Session;
use DataTables;
use Sunra\PhpSimple\HtmlDomParser;

class ProductsController extends Controller
{
  public function __construct()
  {
                 $this->middleware('auth');
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();
      $query = request()->get('query');
      if (!empty($query)) {
        $products = Product::where('name','like',  '%' . $query . '%')
                            ->orwhere('amount','like',  '%' . $query . '%')
                            ->orwhere('views_count','like',  '%' . $query . '%')
                            ->orwhere('click_count','like',  '%' . $query . '%')
                            ->where('user_id',$user->id)
                            ->paginate(10);
      }else{
        $products = Product::orderBy('id', 'desc')->where('user_id',$user->id)->paginate(10);
      }
      return view('account.product.index')
      ->with('query',$query)
      ->with('user',$user)
      ->with('products',$products);
    }
    ////////Get DT //////////////////////////////////
    public function get_products_data(Request $request){
    $user = Auth::user();
  $products = Product::select([
                        'id',
                        'name',
                        'amount',
                        'slug',
                        'image',
                        'user_id',
                        'views_count',
                        'click_count',
                        'category_id',
                        'created_at',
                        ])->where('user_id',$user->id)->get();

    return Datatables::of($products)
    ->addColumn('created_at', function($products) {
      $date = \Carbon\Carbon::parse($products->created_at)->format('d-m-y');
        return "$date";
      })
      ->addColumn('amount', function($products) {
          $user =User::where('id',$products->user_id)->first();//get user
          $currency =Currency::where('id',$user->currency_id)->first();//get currency
          $amount = number_format("$products->amount",0);
          return "$currency->symbol$amount";
        })
    ->addColumn('name_image', function($products) {
      $product_image_type= substr( $products->image, 0, 4 ) === "http";
      $product_image     = $product_image_type==1 ? $products->image : asset($products->image);
      $product_name      = $products->name;
      $product_name      = strlen($product_name) > 20 ? substr($product_name,0,20)."..." : $product_name;
      $product_page_link = url('/').'/'.$products->slug.'-'.$products->id;
       return '<td><a href="'.$product_page_link.'" target="_blank">'.$product_name.'</a><br>
	             <img src="'.$product_image.'"  alt=""  width="100" height="50" /></td>';
        })
      // dd($products->parent_id);

    ->addColumn('category', function($products) {
        $category_id_value = $products->category_id<=1 ? 1 : $products->category_id;
        $cat_parent =Category::where('id',$category_id_value)->first();
                  return "<b>$cat_parent->name</b>";
                  })
    ->addColumn('action', function($products) {
      $delete_confirmation          = '\'Do You Want to Delete: '.$products->name.' ? \'';
      $update_confirmation          = '\'Do You Want to Update Price ? \'';
                return '
                 <a href="'.route('account.product.update_product',$products->id).'" class="btn btn-primary btn-xs" title="Update Price" onclick="return confirm('.$update_confirmation.');"><i class="material-icons">swap_vertical_circle</i></a>
                 <a href="'.route('account.product.edit',$products->id).'" class="btn btn-warning btn-xs" title="Edit"><i class="material-icons">mode_edit</i></a>
                 <a href="'.route('account.product.delete',$products->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');"><i class="material-icons">delete</i></a>';

                })
    ->rawColumns(['name_image','category','action'])
    ->make(true);
  }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $user = Auth::user();
      $categories = Category::attr(['name' => 'category_id', 'class' => 'form-control show-tick'])
            ->selected(1)
            ->renderAsDropdown();
      return view('account.product.create')
      ->with('user',$user)
      ->with ('categories',$categories)
      ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $this->validate($request,[
        'name'=>'required',
        'original_url'=>'required',
        'affiliate_url'=>'required',
        'user_id'=>'required',
        'amount'=>'required|numeric|between:0,999999999999999999999999999.99',
      ]);

      if (Product::where('slug', '=',str_slug($request->name))->exists()) {
            Session::flash('warning','Product Slug Already Exists');
          return redirect()->back();
      }


      $image = $request->image;
      $image_new_name=time().$image->getClientOriginalName();
      $image->move('uploads/products/',$image_new_name);

      $product = Product::create([
          'name'=>$request->name,
          'description'=>$request->description,
          'category_id'=>$request->category_id,
          'amount'=>$request->amount,
          'user_id'=>$request->user_id,
          'original_url'=>$request->original_url,
          'affiliate_url'=>$request->affiliate_url,
          'slug'=>str_slug ($request->name),
          'image'=>'uploads/products/'.$image_new_name,
          // 'active'=>$request->active,
        ]);
        Session::flash('success','Success');
        // dd($request->all());
          return redirect()->route('account.products');

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
      $user = Auth::user();
      $product = Product::find($id);//for security
      // dd($product->user_id);
      if($user->id != $product->user_id){
        Session::flash('warning', 'Access Restricted');
        return redirect('/account');
      }

      //get selected product category
      $product_category =  $product->category_id;
      $categories = Category::attr(['name' => 'category_id', 'class' => 'form-control show-tick'])
            ->selected($product_category)
            ->renderAsDropdown();
      //get merchant list
      //process image
      $product_image_type= substr( $product->image, 0, 4 ) === "http";
      $product_image     = $product_image_type==1 ? $product->image : asset($product->image);


        return view('account.product.edit')
        ->with('product',$product)
        ->with ('categories',$categories)
        ->with ('product_image',$product_image)//dual function
        ->with('user',$user)
        ;

    ;
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
        $user = Auth::user();
        $product = Product::find($id);//for security
        if($user->id != $product->user_id){
          Session::flash('warning', 'Access Restricted');
          return redirect('/account');
        }
        $this->validate($request,[
        'name'=>'required',
        'original_url'=>'required',
        'affiliate_url'=>'required',
        'user_id'=>'required',
        'amount'=>'required|numeric|between:0,999999999999999999999999999.99',
      ]);
      if ($request->hasFile('image')){
          if (file_exists($product->image)){
            unlink($product->image);
          }
          $image = $request->image;
          $image_new_name = time().$image->getClientOriginalName();
          $image->move('uploads/products/',$image_new_name);
          $product->image = 'uploads/products/'.$image_new_name;
          // $product->save();
      }
      $product-> name = $request->name;
      $product-> description = $request->description;
      $product-> category_id = $request->category_id;
      $product-> amount = $request->amount;
      $product-> user_id = $request->user_id;
      $product-> original_url = $request->original_url;
      $product-> affiliate_url = $request->affiliate_url;

      $product->save();
      Session::flash('success','Successfully Updated');
      return redirect()->route('account.products');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_product($id){
      error_reporting(0);
      ini_set('max_execution_time', 300);
      // get product info

      $product = Product::find($id);
      // dd($product->user_id);
      if (empty($product->user_id)){
        Session::flash('warning', "Product's User is Unknown");
        return redirect()->back();
      }

      //sec
      $user = Auth::user();
      if($user->id != $product->user_id){
        Session::flash('warning', 'Access Restricted');
        return redirect('/account');
      }
      //sec

      if (empty($product->original_url)){
        Session::flash('warning', "Product's Original url is Empty");
        return redirect()->back();
      }

// get merchant info
$product_user = User::where('id',$product->user_id)->first();

//sec

if (empty($product_user->price_update_element)){
  Session::flash('warning', "Price Update Element is Empty");
  return redirect()->back();
}

$product_url ="$product->original_url";
$product_price_element = "$product_user->price_update_element";
$product_description_element = "$product_user->description_update_element";


$client = new Client();
$client->setHeader("user-agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0");
$crawler = $client->request('GET', $product_url);
if (false == ( $crawler = $client->request('GET', $product_url))) {
Session::flash('warning', "Unable to Process URL");
return redirect()->back();
}
if (empty($crawler)){
  Session::flash('warning', "Unable to Contact Link (Empty Crawler)");
  return redirect()->back();
}


if ($crawler->filter($product_price_element)->count() > 0 ) {
  $product_description='';
  $price = $crawler->filter($product_price_element)->text();
  $product_price              = str_replace(',', '', $price);
  $product_price              = str_replace(' ', '', $product_price);
  $product_price              = preg_replace("/[^0-9.,]/", "", $product_price);
  $product_price              = preg_replace('/D/', '', $product_price);//floatval($num);

  if (empty($product_price)){
    Session::flash('warning', "Err Price is Empty : Check Merchant REGEX");
    return redirect()->back();
  }

  if ($crawler->filter($product_description_element)->count() > 0 ) {//a.vip
      $product_description = $crawler->filter($product_description_element)->html();
  }

  // if description has value
  if (!empty($product_description)){
    $product->description = $product_description;
    Session::flash('info',"Updated Description");
  }

  if (!empty($product_price)){
    //save
    $product->amount = $product_price;
    $product->save();
    Session::flash('success',"Successfully Updated Price ($product_price)");
    return redirect()->back();
  }else{
    Session::flash('info',"Invalid Price");
    return redirect()->back();
  }

return redirect()->back();
}else{
  Session::flash('warning',"Error Check Regex");
  return redirect()->back();
}
}
//////////////////////////////////CSV/////////////////////////////////////
public function import()
{
  //shows files in the imports directory


  $user = Auth::user();
  $file = "$user->name";
  $glob = glob("uploads/imports/$file.*");
  // dd($files);
  // $directory    = 'uploads/imports/';
  $path = preg_grep('~\.(csv|gz)$~', $glob);
  // dd($path);
  $files   = array_diff($path, array('.', '..'));
//   dd($files);
// $file = "$user->name";
// $files = glob("uploads/imports/$file.*");
// if (count($files) > 0)
// foreach ($files as $file)
//  {
//     $info = pathinfo($file);
//     echo "File found: extension ".$info["extension"]."<br>";
//  }
  ///
  $settings =Setting::first();
  $categories = Category::attr(['name' => 'category_id', 'class' => 'form-control show-tick'])
        ->selected(1)
        ->renderAsDropdown();
  return view('account.product.import')
  ->with ('files',$files)
  ->with('user',$user)
  ->with('settings', $settings)
  ->with ('categories',$categories);
}
public function csv_upload(Request $request)
{
  $setting =Setting::first();
  $user = Auth::user();
  ////////////////demo//////////////
if($setting->live_production==0){
    Session::flash('info', 'demo');
    return redirect()->back();
  }
  $target_dir = "uploads/imports/";
    $csv_file = $request->csv_file;
    $csv_file_type = $csv_file->getClientOriginalExtension();
    // $file_new_name = $csv_file->getClientOriginalName();

    if ($csv_file_type == "gz"){
        //import gz data
          $csv_file_new_name = $user->name.'.csv.gz';
          $csv_file->move($target_dir,$csv_file_new_name);
          //extract GZ data
          error_reporting(0);
          $file_name = 'uploads/imports/'.$user->name.'.csv.gz';
          // Raising this value may increase performance
          $buffer_size = 4096; // read 4kb at a time
          $out_file_name = str_replace('.gz', '', $file_name);

          // Open our files (in binary mode)
          $file = gzopen($file_name, 'rb');
          if (empty($file)){
            Session::flash('error', "Error ($user->name.csv.gz)File Was Not Found");
            return redirect()->back();
          }
          $out_file = fopen($out_file_name, 'wb');

          // Keep repeating until the end of the input file
          while(!gzeof($file)) {
              // Read buffer-size bytes
              // Both fwrite and gzread and binary-safe
              fwrite($out_file, gzread($file, $buffer_size));
          }

          // Files are done, close files
          fclose($out_file);
          gzclose($file);
          unlink($file_name);//deletes old csv.gz file
          //////////Check Header
          $target_file = "uploads/imports/$user->name.csv";
    			$requiredHeaders = array('name', 'amount', 'description', 'image', 'original_url', 'affiliate_url'); //headers we expect
    			$header_check = fopen($target_file, 'r');
    			$firstLine = fgets($header_check); //get first line of csv file
    			fclose($header_check);
    			$foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array

          if ($foundHeaders !== $requiredHeaders) {
            Session::flash('error', "File headers do not match");
    			   // print 'Headers do not match: '.implode(', ', $foundHeaders);
    				 unlink($target_file);
             Session::flash('info', "File Deleted, Please Check the sample");
             return redirect()->back();
    			}
          if (($getdata = fopen($target_file, "r")) !== FALSE) {
    			   fgetcsv($getdata);
    			   fclose($getdata);
           Session::flash('success', "Success: File has been Uploaded and Extracted");
           return redirect()->back();
    			}else{
            Session::flash('error', "Upload Error");
            return redirect()->back();
          }
        }

    if ($csv_file_type == "csv"){
          $csv_file_new_name = "$user->name.csv";
          $csv_file->move($target_dir,$csv_file_new_name);
          $target_file = "uploads/imports/$user->name.csv";
    			//////////Check Header
    			$requiredHeaders = array('name', 'amount', 'description', 'image', 'original_url', 'affiliate_url'); //headers we expect
    			$header_check = fopen($target_file, 'r');
    			$firstLine = fgets($header_check); //get first line of csv file
    			fclose($header_check);
    			$foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array

          if ($foundHeaders !== $requiredHeaders) {
            Session::flash('error', "File headers do not match");
    			   // print 'Headers do not match: '.implode(', ', $foundHeaders);
    				 unlink($target_file);
             Session::flash('info', "File Deleted, Please Check the sample");
             return redirect()->back();
    			}
          if (($getdata = fopen($target_file, "r")) !== FALSE) {
    			   fgetcsv($getdata);
    			   fclose($getdata);
           Session::flash('success', "Success: File has been Uploaded");
           return redirect()->back();
         }else{
           Session::flash('error', "Upload Error");
           return redirect()->back();
         }

    }else {
      Session::flash('warning', "Invalid: File Type Must be CSV or GZ");
      return redirect()->back();
    }
}

public function csv_import(Request $request)
{

  $setting =Setting::first();
  $user = Auth::user();
////////////////demo//////////////
if($setting->live_production==0){
  Session::flash('info', 'demo');
  return redirect()->back();
}
//add settings import limit
// demo 0
$this->validate($request,[
'category_id'=>'required',
'user_id'=>'required',
]);
 error_reporting(0);
$filepath = "uploads/imports/$user->name.csv";
//////////Check Header
$requiredHeaders = array('name', 'amount', 'description', 'image', 'original_url', 'affiliate_url'); //headers we expect
$header_check = fopen($filepath, 'r');
if (empty($header_check)){
  Session::flash('error', "Invalid: $user->name.csv File Was Not Found");
  return redirect()->back();
}
$firstLine = fgets($header_check); //get first line of csv file
fclose($header_check);
$foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array

if ($foundHeaders !== $requiredHeaders) {
  Session::flash('error', "File headers do not match");
   unlink($filepath);
   Session::flash('info', "File Deleted, Please Check the sample");
   return redirect()->back();
}
$limit=0;
$rows=0;
$imported=0;
$updated=0;
$incomplete=0;
if (($getdata = fopen($filepath, "r")) !== FALSE) {
			   fgetcsv($getdata);
			   while (($data = fgetcsv($getdata)) !== FALSE) {
           $rows++;
					$fieldCount = count($data);
					for ($c=0; $c < $fieldCount; $c++) {
					  $columnData[$c] = $data[$c];
					}
		      $product_name               = $columnData[0];
          $product_price              = $columnData[1];
          $product_price              = str_replace(',', '', $product_price);
          $product_price              = str_replace(' ', '', $product_price);
          $product_price              = preg_replace("/[^0-9.,]/", "", $product_price);
          $product_price              = preg_replace('/D/', '', $product_price);//floatval($num);
          $product_description        = ($columnData[2]);
		        $product_image            = ($columnData[3]);
		    $product_original_url         = ($columnData[4]);
       $product_affiliate_url         = ($columnData[5]);
              $product_url_slug 		  = str_slug ($product_name);
          $product_merchant           = $request->user_id;
          $product_category           = $request->category_id;

          //check for empty fields and rows
          if (empty($product_merchant) || empty($product_name) || empty($product_price) || empty($product_image) || empty($product_original_url)|| empty($product_affiliate_url)) {
          $incomplete++;
          continue;
          }
          // if (Product::where('original_url', '=',$product_original_url)->exists()&& !empty($product_price)) {
          //     $products = Product::where('original_url',$product_original_url)->first();
          //     $products->amount = $product_price;
          //     $products->save();
          //     $updated++;
          //     continue;
          //     //skip if the product exists //verifying via original url
          // }
          // if (Product::where('slug', '=',$product_url_slug)->exists()) {
          //     $updated++;
          //     continue;
          //     // skip if slug exists
          // }

         // SQL Query to insert data into DataBase
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
	$imported++;
  if ($setting->csv_import_limit == $imported) {
    return redirect()->back()->with('message',"<div class='text-center'
                             style=' width: auto;
                              padding: 10px;
                              border: 5px solid gray;
                              margin: 0;''>
                              <div style='Color:red'> Import Limit Reached [Limit is $setting->csv_import_limit]</div><br />
                              <div style='Color:black'> Total Processed $rows </div><br />
                              <div style='Color:green'> Imported $imported </div><br />
                              <div style='Color:blue'> Data Skipped/Updated $updated </div><br />
                              <div style='Color:red'> Incomplete Data $incomplete </div>
                            </div>");
  }
          }
          unset($getdata);
          if (file_exists($filepath)){
              unlink($filepath);
           }

return redirect()->back()->with('message',"<div class='text-center'
                         style=' width: auto;
                          padding: 10px;
                          border: 5px solid gray;
                          margin: 0;''>
                          <div style='Color:black'> Total Processed $rows </div><br />
                          <div style='Color:green'> Imported $imported </div><br />
                          <div style='Color:blue'> Data Skipped/Updated $updated </div><br />
                          <div style='Color:red'> Incomplete Data $incomplete </div>
                        </div>");

}else {
  Session::flash('warning', "Failed");
  return redirect()->back();
}


}

    public function destroy($id)
    {
      $user = Auth::user();
      $product = Product::find($id);//for security
      // dd($product->user_id);
      if($user->id != $product->user_id){
        Session::flash('warning', 'Access Restricted');
        return redirect('/account');
      }


      if (file_exists($product->image)){
        unlink($product->image);
      }
      Product::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();
    }
}
