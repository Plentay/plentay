<?php

namespace App\Http\Controllers\account\gateway;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bank;
use App\Setting;
use Session;
use DataTables;
use Auth;
class BanksController extends Controller
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
      $settings = Setting::first();
      return view('account.gateway.banks')
      ->with('user',$user)
      ->with('settings',$settings)

      ;
    }
    public function get_banks_data(){
      $bank = Bank::select([
                            'id',
                            'account_name',
                            'account_number',
                            'bank_name',
                            'other_details',
                            ])->get();



        return Datatables::of($bank)
        ->addColumn('other_details', function($bank) {
                    return "$bank->other_details";
        })
        ->rawColumns(['other_details'])
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
      return view('account.finance.create_bank');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $setting =Setting::first();
      ////////////////demo//////////////
     if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
      // dd($request->all());
      $this->validate($request,[
      'account_name'=>'required',
      'account_number'=>'required',
      'bank_name'=>'required',
    ]);
    $bank = Bank::create([
        'account_name'=>$request->account_name,
        'account_number'=>$request->account_number,
        'bank_name'=>$request->bank_name,
        'other_details'=>$request->other_details,
        // 'vat'=>$request->vat,
      ]);
      Session::flash('success','Success');
      return redirect()->route('account.banks');
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
      $bank=Bank::find($id);
    return view('account.finance.edit_bank')->with ('bank',$bank);
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
      $bank=Bank::find($id);

      $this->validate($request,[
        'account_name'=>'required',
        'account_number'=>'required',
        'bank_name'=>'required',
    ]);


    $bank-> account_name = $request->account_name;
    $bank-> account_number = $request->account_number;
    $bank-> bank_name = $request->bank_name;
    $bank-> other_details = $request->other_details;
    $bank->save();
    Session::flash('success','Successfully Updated');
    return redirect()->route('account.banks');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $bank =Bank::find($id);
      Bank::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();
    }
}
