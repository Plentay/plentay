<?php

namespace App\Http\Controllers\work;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\Invoice;
use App\Credit;
use App\Setting;
use Session;
use DataTables;
use PDF;
use Mail;
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
         $this->middleware('auth:admin');
     }
    public function index()
    {
      return view('work.finance.invoices');
    }
    ////////Get DT //////////////////////////////////
    public function get_invoice_data(){

  $invoices = Invoice::select([
                        'id',
                        'user_id',
                        'invoice_number',
                        'payment_method',
                        'amount',
                        'status',
                        'created_at',
                        ])->get();



    return Datatables::of($invoices)
    ->addColumn('invoice_merchant', function($invoices) {
    $user = Invoice::find($invoices->id)->user;
      return "$user->name";
    })
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
              //if activated
                  // $confirmation          = '\'Do You Want to Disapprove ? \'';
                 return '<a href="'.route('work.invoice.disapprove',$invoices->id).'" class="btn btn-success btn-xs disabled" disabled="disabled" title="Invoice Has Been Approved " >Paid</a>';
            }elseif($invoices->status==0){
              //if deactivated
                  $confirmation          = '\'Do You Want to Approve ? \'';
                 return '
                 <button class="btn btn-danger btn-xs" title="Invoice" >Unpaid</button>
                 <a href="'.route('work.invoice.approve',$invoices->id).'" class="btn btn-default btn-xs" title="Invoice is Unpaid Click to Approve" onclick="return confirm('.$confirmation.');"><i class="material-icons">check</i> Approve</a>';
          }
      })

    ->addColumn('action', function($invoices) {
        $delete_confirmation          = '\'Do You Want to Delete This Invoice ? \'';
                 return '
                 <a href="'.route('work.invoice.csv',$invoices->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
                 <a href="'.route('work.invoice.view',$invoices->id).'" class="btn btn-primary btn-xs" title="View Invoice"><i class="material-icons">remove_red_eye</i></a>
                 <a href="'.route('work.invoice.delete',$invoices->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');" ><i class="material-icons">delete</i></a>';
                })
    ->rawColumns(['invoice_merchant','invoice_number','invoice_amount','invoice_date','invoice_status','action'])
      // onclick="return confirm('Are you sure you want to Remove?');"
    ->make(true);
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $settings=Setting::first();
      return view('work.finance.create_invoice')
        ->with('users',User::all())
        ->with('settings',$settings)
        // ->with('credit',Credit::all());
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
          return redirect()->route('work.invoices');

    }
    public function csv($id)
    {
      $invoice = Invoice::find([$id]); // All users
     $csvExporter = new \Laracsv\Export();
     $csvExporter->build($invoice, ['user_id',
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
      $invoice=Invoice::find($id);
      $settings=Setting::first();
      $invoice_user = Invoice::find($invoice->id)->user;
    return view('work.finance.invoice_details')
    ->with('invoice',$invoice)
    ->with('settings',$settings)
    ->with('invoice_user',$invoice_user);
    }
    public function pdf($id)
    {


      // $invoice=Invoice::find($id);
      // $settings=Setting::first();
      // $invoice_user = Invoice::find($invoice->id)->user;
      // $data =[
      //       'invoice' => $invoice,
      //       'invoice_user' => $invoice_user,
      //       'settings' => $settings,
      //   ];
      //
      // $pdf = PDF::loadView('work.finance.invoice_details',$data);
      // return $pdf->download('invoice_info.pdf');
      // // $pdf = Invoice::make('dompdf.wrapper');
      // // $pdf->loadHTML('<h1>Test</h1>');
      // // return $pdf->stream();
      // dd($id);

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
    $settings=Setting::first();
      Invoice::destroy($id);
      Session::flash('success', 'Deleted Successfully');
      return redirect()->route('work.invoices');
    }
    public function approve($id)
    {
    $settings=Setting::first();
        $invoice = Invoice::find($id);
        if ($invoice->status==1) {
              Session::flash('warning','Already Approved');
            return redirect()->back();
        }
        //credits user
        $invoice->amount;
        $invoice->user_id;
        $user = User::where('id',$invoice->user_id)->first();
        $user->credit=$user->credit+$invoice->amount;
        $user->save();
        //credits user
        $invoice->status=1;
        $invoice->payment_method='Admin';
        $invoice->save();

    	//Email here
    $data = array(
      'name'=>$user->name,
      'contact_name'=>$user->contact_name,
      'email'=>$user->email,
      'subject'=>'Invoice Approved',
      'amount'=>$invoice->amount,
      'currency'=>$settings->currency_symbol,
      'time'=> date('Y-m-d H:i:s'),
      'invoice_number'=>$invoice->invoice_number,
      'processor'=>'Admin',
      'settings' => $settings,

   );
   try{
    Mail::send('emails.invoice_approved',$data, function($message) use($data,$settings){
      $message->from($settings->site_email,$settings->site_name);
      $message->to($data['email'],$data['name']);//sends to user
      $message->subject($data['subject']);
      $message->bcc($settings->site_email,'Admin');//sends to admin
      // $message->reply_to();
      // $message->cc();
    });

        Session::flash('info',  "Credited $invoice->amount to $user->name's Account");
        Session::flash('success','Success: Approved');
        return redirect()->back();
      }catch(\Exception $e){
        Session::flash('error','Mail Error Check .ENV File');
        Session::flash('info',  "Credited $invoice->amount to $user->name's Account");
        Session::flash('success','Success: Approved');
        return redirect()->back();
      }
    }
}
