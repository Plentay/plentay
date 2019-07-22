<?php

namespace App\Http\Controllers\account\gateway;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
/** All Paypal Details class **/
use Auth;
use Validator;
use URL;
use Redirect;
use Input;
use App\User;
use App\Invoice;
use App\Credit;
use App\Setting;
use App\Gateway;
use Session;
use Mail;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

class PayPalController extends Controller
{
   private $_api_context;
   private $org_invoice_id;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
     {
          $this->middleware('auth');
          $this->org_invoice_id = 0;
          //setting//
          $paypal_Settings  = array(
            /**
            * Available option 'sandbox' or 'live'
            */
            'mode' => 'live',//live  //sandbox
            /**
            * Specify the max request time in seconds
            */
            'http.ConnectionTimeOut' => 1000,
            /**
            * Whether want to log to a file
            */
            'log.LogEnabled' => true,
            /**
            * Specify the file that want to write on
            */
            'log.FileName' => storage_path() . '/logs/paypal.log',
            /**
            * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
            *
            * Logging is most verbose in the 'FINE' level and decreases as you
            * proceed towards ERROR
            */
            'log.LogLevel' => 'ERROR'
          );
          $gataway =Gateway::first();
          // parent::__construct();
        /** setup PayPal api context **/
        // $paypal_conf = \Config::get('paypal');

        $this->_api_context = new ApiContext(new OAuthTokenCredential($gataway->paypal_client_id, $gataway->paypal_client_secret));
        $this->_api_context->setConfig($paypal_Settings);
     }
    public function payment(Request $request)
    {
      // dd($request->all());
      Session::put('org_invoice_id', $request->item_number);

      /** set your paypal credential **/
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

    	$item_1 = new Item();

        $item_1->setName($request->item_name) /** item name **/
            ->setCurrency($request->currency_code)
            ->setQuantity(1)
            ->setPrice($request->get('amount')); /** unit price **/

        $item_list = new ItemList();
        $item_list->setItems(array($item_1));

        $amount = new Amount();
        $amount->setCurrency($request->currency_code)
            ->setTotal($request->get('amount'));

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription($request->item_name);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('account.gateway.paypal_status')) /** Specify return URL **/
            ->setCancelUrl(URL::route('account.gateway.paypal_status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
            /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
                \Session::put('error','Connection timeout');
                return Redirect::route('account.invoices');
                /** echo "Exception: " . $ex->getMessage() . PHP_EOL; **/
                /** $err_data = json_decode($ex->getData(), true); **/
                /** exit; **/
            } else {
                \Session::put('error','Some error occur, sorry for inconvenient');
                return Redirect::route('account.invoices');
                /** die('Some error occur, sorry for inconvenient'); **/
            }
        }

        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        /** add payment ID to session **/
        Session::put('paypal_payment_id', $payment->getId());

        if(isset($redirect_url)) {
            /** redirect to paypal **/
            return Redirect::away($redirect_url);
        }

        \Session::put('error','Unknown error occurred');
    	return Redirect::route('account.invoices');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paypal_status()
    {
      /** Get the payment ID before session clear **/
      $payment_id = Session::get('paypal_payment_id');
      /** clear the session payment ID **/
      Session::forget('paypal_payment_id');
      if (empty(request()->PayerID) || empty(request()->token)) {
          \Session::put('error','Payment failed');
          return Redirect::route('account.invoices');
      }
      $payment = Payment::get($payment_id, $this->_api_context);
      /** PaymentExecution object includes information necessary **/
      /** to execute a PayPal account payment. **/
      /** The payer_id is added to the request query parameters **/
      /** when the user is redirected from paypal back to your site **/
      $execution = new PaymentExecution();
      $execution->setPayerId(request()->PayerID);
      /**Execute the payment **/
      $result = $payment->execute($execution, $this->_api_context);
      /** dd($result);exit; /** DEBUG RESULT, remove it later **/
      if ($result->getState() == 'approved') {

        //update account here
        $org_invoice_id = Session::get('org_invoice_id');
        /** clear the session payment ID **/
        Session::forget('org_invoice_id');
        $invoice = Invoice::find($org_invoice_id);
        if ($invoice->status==1) {
              Session::flash('warning','Already Approved, Please Contact Admin');
            return Redirect::route('account.invoices');
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
        $invoice->payment_method='PayPal';
        $invoice->save();

        //Email here
            $settings = Setting::first();
            $data = array(
              'name'=>$user->name,
              'contact_name'=>$user->contact_name,
              'email'=>$user->email,
              'subject'=>'Invoice Approved PayPal',
              'amount'=>$invoice->amount,
              'currency'=>$settings->currency_symbol,
              'time'=> date('Y-m-d H:i:s'),
              'invoice_number'=>$invoice->invoice_number,
              'processor'=>'PayPal',
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
            return redirect("account/invoice/view/$org_invoice_id");
          // \Session::put('success','Payment Successful');
          // return Redirect::route('account.invoices');
      }
      \Session::put('error','Payment failed');

      return Redirect::route('account.invoices');

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
