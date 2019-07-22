<?php

namespace App\Http\Controllers\work;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Credit;
use Session;
use App\Setting;
use DataTables;
class CreditsController extends Controller
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
      $settings =Setting::first();
        return view('work.finance.credit')->with ('settings',$settings);
    }
    public function get_credit_data(){
      $credit = Credit::select([
                            'id',
                            'package_name',
                            'description',
                            'rate_per_click',
                            'min',
                            'max',
                            ])->get();



        return Datatables::of($credit)

        ->addColumn('action', function($credit) {
            return '<a href="'.route('work.credit.edit',$credit->id).'" class="btn btn-warning btn-xs" title="Edit"> Edit <i class="material-icons">mode_edit</i></a>';
          })
        ->rawColumns(['action'])
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
      $credit=Credit::find($id);
      $settings =Setting::first();
    return view('work.finance.edit_credit')
    ->with ('settings',$settings)
    ->with ('credit',$credit);
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
      // dd($request->all());
      $credit=Credit::find($id);

      $this->validate($request,[
      'package_name'=>'required',
      'rate_per_click'=>'required',
      'rate_per_click'=>'required|numeric|between:0,999999999999999999999999999.99',
    ]);


    $credit-> package_name = $request->package_name;
    $credit-> rate_per_click = $request->rate_per_click;
    $credit-> min = 1;
    $credit-> max = 1;
    $credit-> description = $request->description;
    $credit->save();
    Session::flash('success','Successfully Updated');
    return redirect()->route('work.credits');
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
