<?php

namespace App\Http\Controllers\work;

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
         $this->middleware('auth:admin');
     }
     public function index()
     {
       return view('work.finance.reports');
     }
     public function get_reports_data(){

   $reports = Report::select([
                         'id',
                         'ip_address',
                         'source',
                         'destination',
                         'user_id',
                         'created_at',
                         ])->get();



     return Datatables::of($reports)
     ->addColumn('date', function($reports) {
       // $reports_date = date('d-m-Y', strtotime($reports->date));
       $reports_date = \Carbon\Carbon::parse($reports->created_at)->format('d-m-Y');
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
                  <a href="'.route('work.report.csv',$reports->id).'" class="btn btn-primary btn-xs" title="Download CSV"><i class="material-icons">file_download</i></a>
                  <a href="'.route('work.report.delete',$reports->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');" ><i class="material-icons">delete</i></a>';
                 })
     ->rawColumns(['date','ip','source','destination','merchant','action'])
       // onclick="return confirm('Are you sure you want to Remove?');"
     ->make(true);
 	}



  public function csv($id)
  {
    $invoice = Report::find([$id]);
   $csvExporter = new \Laracsv\Export();
   $csvExporter->build($invoice, [
                                'ip_address',
                                'source',
                                'destination',
                                'user_id',
                                'created_at',

                                ])->download();

  }
  //exports all reports
  public function export()
  {
      $invoice = Report::all();
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
            \App\Report::query()->delete();
           Session::flash('success', ' Successfully, Deleted All Reports');
           return redirect()->route('work.reports');
     }
    public function destroy($id)
    {
          Report::destroy($id);
          Session::flash('success', 'Deleted Successfully');
          return redirect()->route('work.reports');
    }
}
