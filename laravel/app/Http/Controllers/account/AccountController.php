<?php

namespace App\Http\Controllers\account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\Product;
use App\User;
use App\Invoice;
use App\Setting;
use App\Currency;
use Auth;
use Session;
use DataTables;
class AccountController extends Controller
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
      //get session user
      $user = Auth::user();
      $settings =Setting::first();
      $products = $user->products;
      $products_count = $products->count();
      $products_impressions = collect($products)->sum('views_count');
      $products_clicks = collect($products)->sum('click_count');

      return view('account.index')
      ->with('settings', $settings)
      ->with('products_count', $products_count)
      ->with('products_impressions', $products_impressions)
      ->with('products_clicks', $products_clicks)
      ->with('user',$user)//pass user to view
      ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function edit()
    {
        $user = Auth::user();
      return view('account.user.edit')
      ->with('user',$user)
      ->with('currencies',Currency::all());
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
      $user = Auth::user();
      // dd($request->all());
      $this->validate($request,[
      'name'=>'required',
      'contact_name'=>'required',
      'currency_id'=>'required',
      'email'=>'required|email',
      'url'=>'required',
    ]);


if (!empty($request->password)){
  $setting =Setting::first();
  if($setting->live_production==0){
    Session::flash('info', 'demo');
    return redirect()->back();
  }
  $password = $request->password;
  $user-> password = bcrypt($password);
}

if ($request->hasFile('image')){
    if (file_exists($user->image)){
      unlink($user->image);
    }
    $image = $request->image;
    $image_new_name = time().$image->getClientOriginalName();
    $image->move('uploads/merchants/',$image_new_name);
    $user->image = 'uploads/merchants/'.$image_new_name;
    // $user->save();
}
$user-> contact_name = $request->contact_name;
$user-> url = $request->url;
$user-> phone_number = $request->phone_number;
// $user-> credit = $request->credit;
$user-> price_update_block = $request->price_update_block;
$user-> price_update_element = $request->price_update_element;
$user-> description_update_element = $request->description_update_element;
$user-> address = $request->address;
$user-> about = $request->about;
$user-> currency_id = $request->currency_id;
$user-> active = $request->active;
$user->save();
Session::flash('success','Success: Updated');
return redirect()->route('account.user.profile');
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
    public function profile()
    {
      $user = Auth::user();
      // $curr = User::find($user->id)->currency;
      // dd($curr->symbol);

      $user_pro_count = count(User::find($user->id)->products);
    return view('account.user.profile')
    ->with('user',$user)
    ->with('user_pro_count',$user_pro_count);
    }
}
