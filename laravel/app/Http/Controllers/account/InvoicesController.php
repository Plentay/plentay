<?php

namespace App\Http\Controllers\account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\Invoice;
use App\Credit;
use App\Setting;
use App\Gateway;
use Session;
use DataTables;
use PDF;
use Dompdf\Dompdf;

class InvoicesController extends Controller
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
      return view('account.invoice.index')
      ->with('user',$user);
    }
    ////////Get DT //////////////////////////////////
    public function get_invoice_data(){
  $user = Auth::user();
  $invoices = Invoice::select([
                        'id',
                        'user_id',
                        'invoice_number',
                        'payment_method',
                        'amount',
                        'status',
                        'created_at',
                        ])->
                        where('user_id',$user->id)->get();



    return Datatables::of($invoices)
    ->addColumn('invoice_number', function($invoices) {
      return "<b>$invoices->invoice_number</b>";
    })
    ->addColumn('invoice_amount', function($invoices) {
      $settings =Setting::first();
      return $settings->currency_symbol.''.number_format($invoices->amount,2);
    })
    ->addColumn('invoice_date', function($invoices) {
      $invoices_date = \Carbon\Carbon::parse($invoices->created_at)->format('d-m-y');

      return "$invoices_date";
    })
    ->addColumn('invoice_status', function($invoices) {
            if($invoices->status==1){
              return '<a href="" class="btn btn-success btn-xs disabled" disabled="disabled" title="Invoice Has Been Approved " >Paid</a>
              <a href="" class="btn btn-success btn-xs disabled" disabled="disabled" title="Invoice Has Been Approved " >'.$invoices->payment_method.'</a>';
            }elseif($invoices->status==0){
              $Stripe_confirmation          = '\'Do You Want to Pay Via Stripe  ? \'';
              $Paypal_confirmation          = '\'Do You Want to Pay Via PayPal  ? \'';
              $VoguePay_confirmation        = '\'Do You Want to Pay Via VoguePay  ? \'';
                 return '<button class="btn btn-danger btn-xs" title="Invoice is Unpaid, Please view invoice for Payment Options" >Unpaid</button>';
          }
      })

    ->addColumn('action', function($invoices) {
      return '<a href="'.route('account.invoice.csv',$invoices->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
      <a href="'.route('account.invoice.view',$invoices->id).'" class="btn btn-primary btn-xs" title="View Invoice"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                })
    ->rawColumns(['invoice_number','invoice_amount','invoice_date','invoice_status','action'])
      // onclick="return confirm('Are you sure you want to Remove?');"
    ->make(true);
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   $settings =Setting::first();
        $user = Auth::user();
        return view('account.invoice.create')
        ->with('user',$user)
        ->with('settings',$settings)
        ->with('credit',Credit::first());
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
        'user_id'=>'required',
        'amount'=>'required|numeric|between:0,999999999999999999999999999.99',
      ]);
      //Generate Invoice Number
      //count invoices to get last row
      $last_count = 1+count(Invoice::all());
      $invoice_number = 'INV'.'-'.time().'-'.$last_count;
      // dd($invoice_number);
      //vat
      // $amount_raw = $request->amount;
      // $mini_amount_temp = $request->amount/100;
      // $mini_amount = $mini_amount_temp*$request->vat;
      // $amount=$request->amount+$mini_amount;
      //vat
      $invoice = Invoice::create([
          'package_name'=>$request->package_name,
          'description'=>$request->description,
          'user_id'=>$request->user_id,
          'invoice_number'=>$invoice_number,
          'rate_per_click'=>$request->rate_per_click,
          'amount'=>$request->amount,
          // 'vat'=>$request->vat,
        ]);


        Session::flash('success','Success');
          return redirect()->route('account.invoices');

    }
    public function csv($id)
    {
      $user = Auth::user();
      $invoice = Invoice::find([$id]);//for csv
      $invoice_sec = Invoice::find([$id])->first();//for security
      if($user->id != $invoice_sec->user_id){
        Session::flash('warning', 'Access Restricted');
        return redirect('/account');
      }

     $csvExporter = new \Laracsv\Export();
     $csvExporter->build($invoice, [
                                  'invoice_number',
                                  'payment_method',
                                  'amount',
                                  'status',
                                  'created_at',

                                  ])->download();

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
    public function view($id)
    {
      $user = Auth::user();
      $invoice = Invoice::find([$id])->first();//for security
      if($user->id !=$invoice->user_id){
        Session::flash('warning', 'Access Restricted');
        return redirect('/account');
        // return redirect()->route('account.user.profile');
      }
      ////////////////////////////////////////
      //////////////////////////////////////
      ////////////////////////////////////
      $settings=Setting::first();
      $gateway = Gateway::first();
      $invoice_user = Invoice::find($invoice->id)->user;
    return view('account.invoice.details')
    ->with('invoice',$invoice)
    ->with('user',$user)
    ->with('settings',$settings)
    ->with('gateway',$gateway)
    ->with('invoice_user',$invoice_user);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


}
