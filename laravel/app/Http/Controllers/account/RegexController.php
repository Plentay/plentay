<?php

namespace App\Http\Controllers\account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Goutte\Client;
use Auth;
use Session;
use Sunra\PhpSimple\HtmlDomParser;
class RegexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
     {
                    $this->middleware('auth');
     }
    public function index()
    {
      $user = Auth::user();
      return view('account.product.regex')
      ->with('user',$user);

    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function run_regex(Request $request){
       error_reporting(0);
       $product_url                    = $request->product_url;
       // $product_block                  = $request->product_block;
       $product_block_element          = $request->product_block_element;
       $data_type                      = $request->type;

       ini_set('max_execution_time', 300);

       $client = new Client();
       $client->setHeader("user-agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0");
       $crawler = $client->request('GET', $product_url);
       if (false == ( $crawler = $client->request('GET', $product_url))) {
       return redirect()->back()->with('message',"<div class='alert alert-warning text-center'> Unable to Process URL</div>");
       }
       if (empty($crawler)){
         return redirect()->back()->with('message',"<div class='alert alert-warning text-center'> Unable to Contact Link (Empty Crawler)</div>");
       }
       ///------------------------------Session-----------------------------///
       Session::put('message', "");
       ///------------------------------Session-----------------------------///
if ($crawler->filter($product_block_element)->count() > 0 ) {
       $product_element_value='';
       // global $data_type;
       // dd("YEs");

     if($data_type==1){
         $product_element_value = $crawler->filter($product_block_element)->text();
         $product_element_value              = str_replace(',', '', $product_element_value);
         $product_element_value              = str_replace(' ', '', $product_element_value);
         $product_element_value              = preg_replace("/[^0-9.,]/", "", $product_element_value);
         $product_element_value              = preg_replace('/D/', '', $product_element_value);//floatval($num);
     }elseif ($data_type==2) {
           $product_element_value = $crawler->filter($product_block_element)->text();
     }elseif ($data_type==3) {
           $product_element_value = $crawler->filter($product_block_element)->html();
     }
     // dd("Skipped");

       // dump($product_stock." Stock.<hr/>");
       // dump($price." Price.<hr/>");
       // // dd("here");
       // dd($product_description)

       //if the new price is empty
       if (empty($product_element_value)){
         ///------------Session-----------///
         $message = Session::get('message');
         $message = "<div class='alert alert-warning text-center'> Empty Result</div>";
         Session::put('message', $message);
         ///------------Session-----------///
       }
       if (!empty($product_element_value)){
             ///------------Session-----------///
             $message = Session::get('message');
             $message = "<div class='alert alert-success text-center'>
             <p>URL: $product_url</p>
             <p>Element: $product_block_element</p>
             Clean: $product_element_value
             </div>";
             Session::put('message', $message);
             ///------------Session-----------///
     }

 };
 ///------------Session-----------///
 $message = Session::get('message');
 //-----------UNSET----------------//
   Session::forget('message');
 ///------------Session-----------///

 if(!empty($message)){
 return redirect()->back()->with('message',$message);
 }else{
 return redirect()->back()->with('message',"<div class='alert alert-danger text-center'> Empty Run</div>");
 }


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
