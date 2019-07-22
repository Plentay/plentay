<?php

namespace App\Http\Controllers\work;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Page;
use App\Setting;
use Session;
use DataTables;

class PagesController extends Controller
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
          return view('work.page.index');
    }
    public function get_pages_data(){
      $page = Page::select([
                            'id',
                            'title',
                            'created_at',
                            'slug',
                            ])->get();

        return Datatables::of($page)
        ->addColumn('action', function($page) {
          $delete_confirmation          = '\'Do You Want to Delete this Page ? \'';
                    return '
                     <a href="'.route('single_page', ['slug'=>$page->slug]).'" class="btn btn-primary btn-xs" title="View"><i class="material-icons">remove_red_eye</i></a>
                     <a href="'.route('work.page.edit',$page->id).'" class="btn btn-warning btn-xs" title="Edit"><i class="material-icons">mode_edit</i></a>
                     <a href="'.route('work.page.delete',$page->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');"><i class="material-icons">delete</i></a>';

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
        return view('work.page.create');
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
      'title'=>'required',
      'content'=>'required',
    ]);
    if (Page::where('slug', '=',str_slug($request->title))->exists()) {
          Session::flash('warning','Page Slug Already Exists');
        return redirect()->back();
    }
    // dd(str_slug($request->title));
    $page = Page::create([
        'title'=>$request->title,
        'slug'=>str_slug($request->title),
        'content'=>$request->content,
      ]);
      Session::flash('success','Success');
      return redirect()->route('work.pages');
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
      $page=page::find($id);
    return view('work.page.edit')->with ('page',$page);
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
      $page=Page::find($id);

      $this->validate($request,[
        'title'=>'required',
        'content'=>'required',
    ]);


    $page-> title = $request->title;
    $page-> content = $request->content;
    $page->save();
    Session::flash('success','Successfully Updated');
    return redirect()->route('work.pages');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $setting =Setting::first();
      ////////////////demo//////////////
if($setting->live_production==0){
        Session::flash('info', 'demo');
        return redirect()->back();
      }
      if($id==1||$id==2||$id==3){
        Session::flash('error', 'Cant Delete Default Page Category');
        return redirect()->back();
      }
      $page =Page::find($id);
      Page::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();

    }
}
