<?php

namespace App\Http\Controllers\Auth;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use App\Category;
use App\Setting;
use App\Page;
use Session;
class AdminLoginController extends Controller
{

  public function __construct(){
    $this->middleware('guest:admin',['except'=>['logout','adminLogout']]);
  }
  public function showLoginForm(){
    return view('auth.osx')
    ->with('categories',(Category::all()))
    ->with('settings',Setting::first())
    ->with('pages',Page::all());
  }

  public function login(Request $request){
    // dd($request);
    $this->validate($request, [
      'email' =>'required|email',
      'password'=>'required|min:6',
     /* 'g-recaptcha-response' => 'required|captcha',*/
    ]);
    if (Auth::guard ('admin')->attempt(['email'=>$request->email,'password'=>$request->password], $request->remember)){
      return redirect()->intended(url('/work') );
    }
    return redirect()->back()->withInput($request->only('email','remember'));
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function adminLogout(Request $request)
    {
      Auth::guard('admin')->logout();
        // $this->guard()->logout();
        // $request->session()->invalidate();

        return redirect('/');
    }

}
