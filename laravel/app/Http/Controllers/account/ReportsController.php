<?php

namespace App\Http\Controllers\account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Report;
use App\Credit;
use App\Setting;
use Session;
use DataTables;
class ReportsController extends Controller
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
       return view('account.finance.reports')
       ->with('user',$user);;
     }
     public function get_reports_data(){
       $user = Auth::user();
   $reports = Report::select([
                         'id',
                         'ip_address',
                         'source',
                         'destination',
                         'user_id',
                         'created_at',
                         ])->where('user_id',$user->id)->get();



     return Datatables::of($reports)
     ->addColumn('date', function($reports) {
       // $reports_date = date('d-m-Y', strtotime($reports->date));
       $reports_date = \Carbon\Carbon::parse($reports->created_at)->format('d-m-y');
       return "$reports_date";
     })
     ->addColumn('ip', function($reports) {
       return "<b>$reports->ip_address</b>";
     })
     ->addColumn('source', function($reports) {
       $report_source      = strlen($reports->source) > 30 ? substr($reports->source,0,30)."..." : $reports->source;
       return "$report_source";
     })
     ->addColumn('destination', function($reports) {
       $report_destination      = strlen($reports->destination) > 30 ? substr($reports->destination,0,30)."..." : $reports->destination;
       return "$report_destination";
     })

     ->addColumn('merchant', function($reports) {
     // $user = Report::find($reports->id)->user;
     $name= $reports->user->name;
       return "$name";
     })

     ->addColumn('action', function($reports) {
         $delete_confirmation          = '\'Do You Want to Delete This Report ? \'';
                  return '
                  <a href="'.route('account.report.csv',$reports->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
                  <a href="'.route('account.report.delete',$reports->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');" ><i class="material-icons">delete</i></a>';
                 })
     ->rawColumns(['date','ip','source','destination','merchant','action'])
       // onclick="return confirm('Are you sure you want to Remove?');"
     ->make(true);
 	}



  public function csv($id)
  {
    $user = Auth::user();
    $invoice = Report::find([$id]);
    $invoice_sec = Report::find([$id])->first();//for security
    if($user->id != $invoice_sec->user_id){
      Session::flash('warning', 'Access Restricted');
      return redirect('/account');
    }
   $csvExporter = new \Laracsv\Export();
   $csvExporter->build($invoice, [
                                'ip_address',
                                'source',
                                'destination',
                                'user_id',
                                'created_at',

                                ])->download();

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

     public function delete_all()
     {
       $user = Auth::user();
       $reports = Report::where('user_id',$user->id)->get();
       foreach ($reports as $report) {
         $report->forceDelete();
       }
        Session::flash('success', ' Successfully, Deleted All Reports');
        return redirect()->route('account.reports');
     }
    public function destroy($id)
    {
          $user = Auth::user();
          $report = Report::find($id);//for security
          if($user->id != $report->user_id){
            Session::flash('warning', 'Access Restricted');
            return redirect('/account');
          }
          Report::destroy($id);
          Session::flash('success', 'Deleted Successfully');
          return redirect()->route('account.reports');
    }
}
