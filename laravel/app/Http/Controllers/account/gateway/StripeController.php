<?php

namespace App\Http\Controllers\account\gateway;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Invoice;
use App\Credit;
use App\Setting;
use App\Gateway;
use Session;
use Stripe\Stripe;
use Stripe\Charge;
use Mail;
class StripeController extends Controller
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
     public function payment(Request $request)
     {
           // dd($request->all());
      if(!empty($request->stripeToken)){
        $settings = Setting::first();
        $gateway = Gateway::first();
        Stripe::setApiKey($gateway->stripe_secret_key);

    $org_amount             = $request->org_amount;
    $org_invoice_id         = $request->org_invoice_id;
    $org_invoice_no         = $request->org_invoice_no;
    $org_desc               = $request->org_desc;

    $charge = Charge::create([
            'amount' => $org_amount * 100,
            'currency' => $settings->currency_code,
            'description' => $org_desc,
            'source' => request()->stripeToken,
        ]);

        //update account here
        $invoice = Invoice::find($org_invoice_id);
        if ($invoice->status==1) {
              Session::flash('warning','Already Approved, Please Contact Admin');
            return redirect()->back();
        }
        //credits user
        $invoice->amount;
        $invoice->user_id;
        //credits user
        $user = User::where('id',$invoice->user_id)->first();
        $user->credit=$user->credit+$invoice->amount;
        $user->save();

        //update invoice
        $invoice->status=1;
        $invoice->payment_method='Stripe';
        $invoice->save();


        //Email here
    $data = array(
      'name'=>$user->name,
      'contact_name'=>$user->contact_name,
      'email'=>$user->email,
      'subject'=>'Invoice Approved Stripe',
      'amount'=>$org_amount,
      'currency'=>$settings->currency_symbol,
      'time'=> date('Y-m-d H:i:s'),
      'invoice_number'=>$org_invoice_no,
      'processor'=>'Stripe',
      'settings' => $settings,

   );
    Mail::send('emails.invoice_approved',$data, function($message) use($data,$settings){
      $message->from($settings->site_email,$settings->site_name);
      $message->to($data['email'],$data['name']);//sends to user
      $message->subject($data['subject']);
      $message->bcc($settings->site_email,'Admin');//sends to admin
      // $message->reply_to();
      // $message->cc();
    });
        Session::flash('info',  "Credited $invoice->amount to $user->name's Account");
        Session::flash('success', 'Thank You!. Payment was successfull.');
         return redirect("account/invoice/view/$request->org_invoice_id");
     }else{
       Session::flash('error','Error Please Contact Admin');
         return redirect()->route('account.invoices');
     }
  }
    public function index()
    {
        // return view('account.finance.banks');
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
}
