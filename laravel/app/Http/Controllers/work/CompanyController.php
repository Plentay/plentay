<?php

namespace App\Http\Controllers\work;
use Goutte\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mail;
use Auth;
use App\User;
use App\Currency;
use App\Setting;
use Session;
use DataTables;
use Sunra\PhpSimple\HtmlDomParser;
class CompanyController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth:admin');
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //  public function datatables()
    //  {
    //      return view('work.user.datatables');
    // }
    ///////////////////get DT //////////////////////////
    // public function get_users_data(){
    //get
    //   $users = User::select(
    //                       'name',
    //                       'email',
    //                       'updated_at',
    //                     'created_at');
    //   return Datatables::of($users)->make(true);
    // }
    ////////Get DT //////////////////////////////////
    public function get_companys_data(){

  $users = User::select([
                        'id',
                        'image',
                        'name',
                        'credit',
                        'email',
                        'active',
                        ])->where('role_id', '=','1')->get();



    return Datatables::of($users)
    ->addColumn('product_count', function($users) {
      // dd($users->id);
    $user_pro_count = count(User::find($users->id)->products);
    return "<b>$user_pro_count</b>";
    })
    ->addColumn('image', function($users) {
        $delete_confirmation          = '\'Do You Want to Delete '.$users->name.' ? \'';
                 return '<img src="'.asset($users->image).'" alt="'.$users->name.'" width="90px" height="90px" />';
                })
      ->addColumn('profile', function($users) {
        $update_confirmation          = '\'Do You Want to Update Prices ? \'';
          return '
          <a href="'.route('work.user.profile',$users->id).'" class="btn btn-info btn-xs" title="View User"><i class="material-icons">remove_red_eye</i></a>';
              })

       ->addColumn('view_users', function($users) {
          return '
          <a href="'.route('work.user.profile',$users->id).'" class="btn btn-info btn-xs" title="View User"><i class="material-icons">remove_red_eye</i></a>';
              })
      ->addColumn('active', function($users) {

            if($users->active==1){
              //if activated
                  $confirmation          = '\'Do You Want to Deactivate '.$users->name.' ? \'';
                 return '<a href="'.route('work.user.deactivate',$users->id).'" class="btn btn-success btn-xs" title="User is Activated Click to Deactivate" onclick="return confirm('.$confirmation.');"><i class="material-icons">lock_open</i></a>';
            }elseif($users->active==0){
              //if deactivated
                  $confirmation          = '\'Do You Want to Activate '.$users->name.' ? \'';
                 return '<a href="'.route('work.user.activate',$users->id).'" class="btn btn-danger btn-xs" title="User is Deactivated Click to Activate" onclick="return confirm('.$confirmation.');"><i class="material-icons">lock</i></a>';
          }
      })

    ->addColumn('action', function($users) {
        $delete_confirmation          = '\'Do You Want to Delete '.$users->name.' ? \'';
                 return '
                 <a href="'.route('work.user.csv',$users->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
                 <a href="'.route('work.user.edit',$users->id).'" class="btn btn-warning btn-xs" title="Edit"><i class="material-icons">mode_edit</i></a>';
                })
    ->rawColumns(['product_count','image','profile','active','action','view_users'])
      // onclick="return confirm('Are you sure you want to Remove?');"
    ->make(true);
	}

    public function index()
    {
      // dd(Auth::guard());
      // dd(Auth::guard('user')->user());
      // dd(Auth::guard('user')->user()->toArray());
      // $user = Auth::guard('user')->user()->toArray();
      //   return view('work.index')
      //   ->with('user', $user)
      //   ;
    }

    //-
    public function view_companys()
    {
      $user = Auth::guard('admin')->user()->toArray();
       return view('work.company.companys')
        ->with('user', $user)
        ;
    }

     public function view_companys_users()
    {
      $user = Auth::guard('admin')->user()->toArray();
       return view('work.company.companyUsers')
        ->with('user', $user)
        ;
    }
    public function csv($id)
    {
      $user = User::find([$id]); // All users
     $csvExporter = new \Laracsv\Export();
     $csvExporter->build($user, ['name',
                                  'email',
                                  'contact_name',
                                  'url',
                                  'credit',
                                  'active',
                                  'created_at',
                                  '',
                                  ])->download();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $user = User::where('role_id', '=','1')->get();
      return view('work.company.create')->with('users',$user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

$count_name =strlen($request->name);
$count_password =strlen($request->password);
if ($count_name<3) {
      Session::flash('warning','UserName Cannot Be less than 3');
    return redirect()->back();
}
if ($count_password<6) {
      Session::flash('warning','Password Cannot Be less than 6');
    return redirect()->back();
}
      $this->validate($request,[
      'name'=>'required',
      'contact_name'=>'required|min:3',
      'email'=>'required|email',
      'password'=>'required|min:6'
    ]);
// dd($request->all());
if (User::where('name', '=',$request->name)->exists()) {
      Session::flash('warning','Merchant UserName Already Exists');
    return redirect()->back();
}
if (User::where('email', '=',$request->email)->exists()) {
      Session::flash('warning','Merchant Email Already Exists');
    return redirect()->back();
}


//
// $requestValues = $request->all();
//     User::create([
//         'practice_name' => $requestValues['practice_name']
//     ]);
    /*  $image = $request->image;
      $image_new_name=time().$image->getClientOriginalName();
      $image->move('uploads/merchants/',$image_new_name);*/

    $user = User::create([
        'name'=>str_slug ($request->name),
        'contact_name'=>$request->contact_name,
        'email'=>$request->email,
        'active'=>$request->active,
        'company_id'=>'0',
        'role_id'=>'1',
        'password'=>bcrypt($request->password)
      ]);
      Session::flash('success','Merchant has been Created');
      // dd($request->all());
        return redirect()->route('companys');

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

        $user=User::find($id);
      return view('work.user.edit')
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
    public function update(Request $request, $id)
    {
      $user=User::find($id);
      // dd($request->all());
      $this->validate($request,[
      'name'=>'required|min:3',
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
$user-> credit = $request->credit;
$user-> price_update_block = $request->price_update_block;
$user-> price_update_element = $request->price_update_element;
$user-> description_update_element = $request->description_update_element;
$user-> address = $request->address;
$user-> about = $request->about;
$user-> currency_id = $request->currency_id;
$user-> active = $request->active;
$user->save();
Session::flash('success','User: has been Successfully Updated');
return redirect()->route('users');
    }


    public function update_products($id){
      $setting =Setting::first();
      if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
      error_reporting(0);
      ini_set('max_execution_time', -1);
      $user = User::find($id);
      if (empty($user->price_update_element)){
        Session::flash('warning', "Price Update Element is Empty");
        return redirect()->back();
      }
      //check if user has Products
      $user_pro_count = count(User::find($user->id)->products);
      if ($user_pro_count==0){
        Session::flash('warning', "NO Product was found for this user");
        return redirect()->back();
      }

      ///------------------------------Session-----------------------------///
      Session::put('counter', 0);
      ///------------------------------Session-----------------------------///
      foreach ($user->products as $product) {
        sleep(rand(1,2)) ;
        //skip if product url is empty
        if (empty($product->original_url)){
          continue;
        }

        $product_url ="$product->original_url";
        $product_price_element = "$user->price_update_element";
        $product_description_element = "$user->description_update_element";

        $client = new Client();
        $client->setHeader("user-agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0");
        $crawler = $client->request('GET', $product_url);
        if (false == ( $crawler = $client->request('GET', $product_url))) {
          // Error
            continue;
        }
        if (empty($crawler)){
          // Error
            continue;
        }

      if ($crawler->filter($product_price_element)->count() > 0 ) {
        $product_description='';
        $product_price='';
          $price = $crawler->filter($product_price_element)->text();
          $product_price              = str_replace(',', '', $price);
          $product_price              = str_replace(' ', '', $product_price);
          $product_price              = preg_replace("/[^0-9.,]/", "", $product_price);
          $product_price              = preg_replace('/D/', '', $product_price);//floatval($num);

        if ($crawler->filter($product_description_element)->count() > 0 ) {//a.vip
            $product_description = $crawler->filter($product_description_element)->html();
        }
        //if the new price is empty
        if (empty($product_price)){
          // return true;//skip
          continue;
        }
        // if description has value
        if (!empty($product_description)){
          $product->description = $product_description;
        }

        // dd('here');
        if (!empty($product_price)){
          //gain calculation
          $product->amount = $product_price;
          $product->save();
          ///------------Session-----------///
          $counter = Session::get('counter');
          $counter++;
          Session::put('counter', $counter);
          ///------------Session-----------///
        }


      }else{
      Session::flash('warning',"Error Check Regex");
      return redirect()->back();
      }

      }
      ///------------Session-----------///
      $counter = Session::get('counter');
      //-----------UNSET----------------//
      Session::forget('counter');
      ///------------Session-----------///

      Session::flash('success',"Success: Updated ($counter) Products");
      // return redirect()->route('products');
      return redirect()->back();

}
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $setting =Setting::first();
      if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }

      $user = User::find($id);
      //find and deletes products of the user
      foreach ($user->products as $product) {
        if (file_exists($product->image)){
          unlink($product->image);
        }
        $product->forceDelete();
      }
      //deletes invoices
      foreach ($user->invoice as $invoice) {
        $invoice->forceDelete();
      }
      foreach ($user->report as $report) {
        $report->forceDelete();
      }

      if (file_exists($user->image)){
        unlink($user->image);
      }
      User::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();
    }
    public function profile($id)
    {
      $user=User::find($id);
      $currency=Currency::find($user->currency_id);
      $user_pro_count = count(User::find($user->id)->products);
    return view('work.user.profile')
    ->with('user',$user)
    ->with('currency',$currency)
    ->with('user_pro_count',$user_pro_count);
    }
    public function activate($id)
    {
      $settings =Setting::first();
      if($settings->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
        $user = User::find($id);
        $user->active=1;
        $user->validation_code = 0;
        $user->save();
        Session::flash('success','Success: Activated');


        $email_data = array(
          'name' => $user['name'],
          'email' => $user['email'],
          'url' => $user['url'],
          'contact_name' => $user['contact_name'],
          'settings' => $settings,
       );

       try{
        Mail::send('emails.welcome', $email_data, function($message)use($email_data,$settings)  {
            $message->from($settings->site_email,$settings->site_name);
            $message->to($email_data['email'], $email_data['name']);
            $message->subject('Account Activated');
        });
          return redirect()->back();
        }catch(\Exception $e){
          Session::flash('error','mail Error Check .ENV File');
          return redirect()->back();
        }


    }
    public function deactivate($id)
    {
      $settings =Setting::first();
      if($settings->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
        $user = User::find($id);
        $user->active=0;
        $user->save();
        Session::flash('success','Success: Deactivated');

        $email_data = array(
          'name' => $user['name'],
          'email' => $user['email'],
          'url' => $user['url'],
          'contact_name' => $user['contact_name'],
          'settings' => $settings,
       );
       try{
        Mail::send('emails.deactivated', $email_data, function($message)use($email_data,$settings)  {
            $message->from($settings->site_email,$settings->site_name);
            $message->to($email_data['email'], $email_data['name']);
            $message->subject('Account De-activated');
        });
        return redirect()->back();
      }catch(\Exception $e){
        Session::flash('error','mail Error Check .ENV File');
        return redirect()->back();
      }
    }
}
