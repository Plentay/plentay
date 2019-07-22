<?php

namespace App\Http\Controllers\work;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\Admin;
use App\Product;
use App\User;
use App\Invoice;
use App\Setting;
use Session;
use DataTables;
class AdminsController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth:admin');
      // $site_settings = Setting::first();
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //  public function datatables()
    //  {
    //      return view('work.admin.datatables');
    // }
    ///////////////////get DT //////////////////////////
    // public function get_admins_data(){
    //get
    //   $admins = Admin::select(
    //                       'name',
    //                       'email',
    //                       'updated_at',
    //                     'created_at');
    //   return Datatables::of($admins)->make(true);
    // }
    ////////Get DT //////////////////////////////////
    public function get_admins_data(Request $request){
      //post
      // dump($request->all());
  // print_r($request->all());
  $admins = Admin::select([
                        'id',
                        'name',
                        'email',
                        'updated_at',
                        'created_at'])->get();

    return Datatables::of($admins)
    // ->addColumn('action', '<a href="#">Html Column</a>');
    ->addColumn('action', function($admins) {
      $delete_confirmation          = '\'Do You Want to Delete '.$admins->name.' ? \'';
                if(Auth::id()!==$admins->id){
                 return '
                 <a href="'.route('work.admin.csv',$admins->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
                 <a href="'.route('work.admin.edit',$admins->id).'" class="btn btn-warning btn-xs" title="Edit"><i class="material-icons" >mode_edit</i></a>
                 <a href="'.route('work.admin.delete',$admins->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');"><i class="material-icons">delete</i></a>';
               }
                })
    ->make(true);
	}

    public function index()
    {
      $admin = Auth::user();
      $settings =Setting::first();
      $products = Product::all();
      $products_count = Product::all()->count();
      $products_impressions = collect($products)->sum('views_count');
      $products_clicks = collect($products)->sum('click_count');
      $users = User::all();
      $users_count = User::where('role_id', '!=',0)->where('active', '=',1)->count();
      $users_credits = collect($users)->sum('credit');
      $inactive_users =User::where('active', '=',0)->count();
      $marchant_users =User::where('role_id', '=',0)->where('active', '=',1)->count();

        return view('work.index')
        ->with('admin', $admin)
        ->with('settings', $settings)
        ->with('products_count', $products_count)
        ->with('products_impressions', $products_impressions)
        ->with('products_clicks', $products_clicks)
        ->with('inactive_users', $inactive_users)
        ->with('users_count', $users_count)
        ->with('users_credits', $users_credits)
        ->with('marchant_users', $marchant_users)
        ->with('users',User::all())
        ->with('invoices',Invoice::take(10)->get())
        // ->with('invoices',Invoice::take(10)->orderBy('id', 'desc')->get())

        ;
    }
    // public function user()
    // {
    //     $admin = Auth::user();
    //     Auth::share('admin', $admin);
    // }
    public function view_admins()
    {
         $admin = Auth::user();
        return view('work.admin.admins')
        ->with('admin', $admin)
        ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('work.admin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      $this->validate($request,[
      'name'=>'required',
      'display_name'=>'required',
      'email'=>'required|email',
      'password'=>'required|min:6'
    ]);
    if (Admin::where('name', '=',$request->name)->exists()) {
      Session::flash('warning','Admin UserName Already Exists');
        return redirect()->back();
}
if (Admin::where('email', '=',$request->email)->exists()) {
  Session::flash('warning','Admin User Email Already Exists');
    return redirect()->back();
}
      // dd($request->all());
    $admin = Admin::create([
        'name'=>$request->name,
        'display_name'=>$request->display_name,
        'email'=>$request->email,
        'password'=>bcrypt('password')
      ]);
      Session::flash('success','Admin User has been Created');
        return redirect()->route('admins');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function csv($id)
     {

       // $admin = Admin::get(); // All users
       $admin = Admin::find([$id]); // All users
      $csvExporter = new \Laracsv\Export();
      $csvExporter->build($admin, ['name', 'email', 'created_at'])->download();
         // $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
         //
         // $csv->insertOne(\Schema::getColumnListing('id','name','email'));
         // // Admin::all()->each(function($admin) use($csv) {
         // Admin::find($id)->each(function($admin) use($csv) {
         //     $csv->insertOne($admin->toArray());
         // });
         //
         // $csv->output('Admin.csv');
     }
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

        $admin=Admin::find($id);
      return view('work.admin.edit')->with('admin',$admin);
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
      $setting =Setting::first();
      ////////////////demo//////////////
if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
      $this->validate($request,[
      'name'=>'required',
      'display_name'=>'required',
      'email'=>'required|email'
      // 'password'=>'required|min:6'
    ]);

// if (Admin::where('name', '=',$request->name)->exists()) {
//       Session::flash('warning','Admin UserName Already Exists');
//       return redirect()->back();
// }
// if (Admin::where('email', '=',$request->email)->exists()) {
//   Session::flash('warning','Admin User Email Already Exists');
//     return redirect()->back();
// }
    $admin = Admin::find($id);
    if (!empty($request->password)){
  $password = $request->password;
  $admin->password = bcrypt($password);
}
$admin -> name = $request->name;
$admin -> display_name = $request->display_name;
$admin -> email = $request->email;
$admin->save();
Session::flash('success','Admin: has been Successfully Updated');
return redirect()->route('admins');
    }
    public function admin_profile()
    {
    $admin =   Auth::user();
    return view('work.admin.profile')
    ->with('admin',$admin);
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
      ////////////////demo//////////////
      if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }

      // get logged in Admin Info
      $session_admin_id = Auth::user()->id;
      if($session_admin_id==$id){
        Session::flash('error', 'Can Delete Own Account');
        return redirect()->back();
      }

      Admin::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();
    }
}
