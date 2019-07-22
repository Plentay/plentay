<?php

namespace App\Http\Controllers\Auth;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Mail;
use Session;
use Illuminate\Http\Request;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'account/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        $this->middleware('guest')->except('logout', 'userLogout');
    }
    public function userLogout()
    {
      // Auth::guard('web')->logout();
        $this->guard()->logout();
        // $request->session()->invalidate();
        return redirect('/');
    }
    public function authenticated(Request $request, $user)
  {
      if ($user->active==0) {
          auth()->logout();
          Session::flash('error','You need to confirm your account. We already sent you an activation code, please check your email.');
          return redirect('/login');
      }
      return redirect()->intended($this->redirectPath());
  }
}
