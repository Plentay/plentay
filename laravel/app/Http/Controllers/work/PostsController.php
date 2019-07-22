<?php

namespace App\Http\Controllers\work;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;
use Auth;
use App\Admin;
use App\Setting;
use Session;
use DataTables;
class PostsController extends Controller
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
          return view('work.post.index');
    }
    public function get_posts_data(){
      $post = Post::select([
                            'id',
                            'title',
                            'author',
                            'image',
                            'slug',
                            'created_at',
                            ])->get();

        return Datatables::of($post)
        ->addColumn('title', function($post) {
          $post_title      = strlen($post->title) > 20 ? substr($post->title,0,20)."..." : $post->title;
                    return "$post_title";

          })
          ->addColumn('image', function($post) {
                      return '<img src="'.asset($post->image).'"  alt=""  width="100" height="50" /></td>';

            })
          ->addColumn('author', function($post) {
                        return "$post->author";

          })
        ->addColumn('action', function($post) {
          $delete_confirmation          = '\'Do You Want to Delete this Post ? \'';
                    return '
                     <a href="'.route('post_page', ['slug'=>$post->slug]).'" class="btn btn-primary btn-xs" title="View"><i class="material-icons">remove_red_eye</i></a>
                     <a href="'.route('work.post.edit',$post->id).'" class="btn btn-warning btn-xs" title="Edit"><i class="material-icons">mode_edit</i></a>
                     <a href="'.route('work.post.delete',$post->id).'" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('.$delete_confirmation.');"><i class="material-icons">delete</i></a>';

          })
        ->rawColumns(['title','image','author','action'])
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
        return view('work.post.create');
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
      'title'=>'required',
      'content'=>'required',
      'image'=>'required|image',
    ]);
    if (Post::where('slug', '=',str_slug($request->title))->exists()) {
          Session::flash('warning','Post Slug Already Exists');
        return redirect()->back();
    }
    $image = $request->image;
    $image_new_name=time().$image->getClientOriginalName();
    $image->move('uploads/posts/',$image_new_name);
    //get session admin UserName
    $session_admin_name = Auth::user()->name;

    $post = Post::create([
        'title'=>$request->title,
        'slug'=>str_slug ($request->title),
        'content'=>$request->content,
        'image'=>'uploads/posts/'.$image_new_name,
        'author'=>$session_admin_name,
      ]);
      Session::flash('success','Success');
      return redirect()->route('work.posts');
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
      $post=Post::find($id);
    return view('work.post.edit')->with ('post',$post);
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
      $post=Post::find($id);

      $this->validate($request,[
        'title'=>'required',
        'content'=>'required',
    ]);
    if ($request->hasFile('image')){
        if (file_exists($post->image)){
          unlink($post->image);
        }
        $image = $request->image;
        $image_new_name = time().$image->getClientOriginalName();
        $image->move('uploads/posts/',$image_new_name);
        $post-> image = 'uploads/posts/'.$image_new_name;
    }


    $post-> title = $request->title;
    $post-> content = $request->content;
    $post->save();
    Session::flash('success','Successfully Updated');
    return redirect()->route('work.posts');
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
      $post =Post::find($id);
      if (file_exists($post->image)){
        unlink($post->image);
      }
      Post::destroy($id);
      Session::flash('info', ' Deleted Successfully');
      return redirect()->back();

    }
}
