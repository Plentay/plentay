<?php

namespace App\Http\Controllers\work;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Slider;
use App\Setting;
use Session;
use DataTables;
class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
     {
         $this->middleware('auth:admin');
         // $site_settings = Setting::first();
     }
    public function index()
    {
        return view('work.slide.index')
        ->with('slides',Slider::all())
        ->with('last_slide',Slider::orderBy('created_at','desc')->first());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('work.slide.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request,[
      'image'=>'required|image',
    ]);
    $image = $request->image;
    $image_new_name=time().$image->getClientOriginalName();
    $image->move('uploads/slider/',$image_new_name);

  $slide = Slider::create([
      'url'=>$request->url,
      'image'=>'uploads/slider/'.$image_new_name,
    ]);
    Session::flash('success','Successfully Uploaded');
      return redirect()->route('work.slides');
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
      $slide =Slider::find($id);
      if (file_exists($slide->image)){
        unlink($slide->image);
      }
      Slider::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();
    }
}
