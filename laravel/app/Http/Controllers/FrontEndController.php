<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Currency;
use App\Category;
use App\Setting;
use Session;
use App\Product;
use App\Post;
use App\Page;
use App\Slider;
use App;
use Mail;
use URL;
use DataTables;
class FrontEndController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
      //homepage
      $settings =Setting::first();
      //random products
      $products = Product::inRandomOrder()->limit($settings->home_rand_pro)->get();
      //get 4 latest posts
      $posts = Post::orderBy('id', 'desc')->take($settings->home_posts)->get();
      $users = User::where('active', '=',1)->take($settings->home_users)->get();
      // $users = User::orderBy('id', 'desc')->take($settings->home_users)->get();
      return view('index')
    // ->with('title',Setting::first()->site_name)
    // ->with('categories',Category::take(5)->get())
    // ->with('first_post',Post::orderBy('created_at','desc')->first())
    // ->with('more_post',Post::orderBy('created_at','desc')->skip(1)->take(2)->get())
    // ->with('second_post',Post::orderBy('created_at','desc')->skip(1)->take(1)->get()->first())
    // ->with('third_post',Post::orderBy('created_at','desc')->skip(2)->take(1)->get()->first())
    ->with('latest_product',Product::orderBy('id','desc')->first())
    ->with('most_viewed_product',Product::orderBy('views_count','desc')->first())
    ->with('most_clicked_product',Product::orderBy('click_count','desc')->first())
    ->with('categories',(Category::all()))
    ->with('pages',(Page::all()))
    ->with('slides',(Slider::all()))
    ->with('products',$products)
    ->with('posts',$posts)
    ->with('users',$users)
    ->with('settings',$settings);
    }
    public function blogs()
    {
        $settings =Setting::first();
        $posts = Post::orderBy('id', 'desc')->paginate(6);
        return view('blogs')
        ->with('posts',$posts)
        ->with('categories',(Category::all()))
        ->with('pages',(Page::all()))
        ->with('settings',$settings);
    }

    public function post_page($slug)
    {

      $settings =Setting::first();
      $post=Post::where('slug',$slug)->first();
      return view('post_page')
      ->with('post',$post)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);


    }

    public function single_page_api()
    {

      $settings =Setting::first();
      $page=Page::where('slug','about')->first();
      return  $page;


    }

    public function single_page($slug)
    {

      $settings =Setting::first();
      $page=Page::where('slug',$slug)->first();
      return view('page')
      ->with('page',$page)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);


    }
    public function category_page($slug)
    {
      $settings =Setting::first();
      $category_page = Category::where('slug',$slug)->first();
      //get category products
      $products =Product::where('category_id',$category_page->id)->paginate(9);
      return view('category_page')
      ->with('category_page',$category_page)
      ->with('products',$products)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);
    }
    public function merchants()
    {
        $settings =Setting::first();
        $users = User::where('active', '=',1)->paginate(16);
        // dd(count($users));
        // $users = User::orderBy('id', 'desc')->paginate(16);
        return view('merchants')
        ->with('users',$users)
        ->with('categories',(Category::all()))
        ->with('pages',(Page::all()))
        ->with('settings',$settings);
    }
    public function merchant_page($slug)
    {
      $settings =Setting::first();
      $user = User::where('name',$slug)->first();
      //get merchant products
      $products =Product::where('user_id',$user->id)->paginate(9);
      return view('merchant_page')
      ->with('user',$user)
      ->with('products',$products)
      ->with('categories',(Category::all()))
      ->with('pages',(Page::all()))
      ->with('settings',$settings);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function feed()
    {
      // create new feed
$feed = App::make("feed");
$settings =Setting::first();

// multiple feeds are supported
// if you are using caching you should set different cache keys for your feeds

// cache the feed for 60 minutes (second parameter is optional)
$feed->setCache(60, 'laravelFeedKey');

// check if there is cached feed and build new only if is not
if (!$feed->isCached())
{
 // creating rss feed with our most recent 20 posts
 $products = \DB::table('products')->orderBy('created_at', 'desc')->take(20)->get();

 // set your feed's title, description, link, pubdate and language
 $feed->title = $settings->site_name;
 $feed->description = $settings->site_about;
 $feed->logo = asset($settings->logo);
 $feed->link = url('feed');
 $feed->setDateFormat('datetime'); // 'datetime', 'timestamp' or 'carbon'
 $feed->pubdate = $products[0]->created_at;
 $feed->lang = 'en';
 $feed->setShortening(true); // true or false
 $feed->setTextLimit(100); // maximum length of description text

 foreach ($products as $product)
 {
   $product_url = $product->slug.'-'.$product->id;
   $product_image = asset($product->image);
   $enclosure = ['url'=>"$product_image",'type'=>'image/jpeg'];
     // set item's title, author, url, pubdate, description, content, enclosure (optional)*
     $feed->add($product->name,$product->user_id, URL::to($product_url), $product->created_at, $product->description,$product->description,$enclosure);

     // $feed->addArray([
     // 'title' => $product->name,
     // 'author' => $product->user_id,
     // 'url' => $product_url,
     // 'pubdate' => $someDate,
     // 'description' => $product->description,
     // 'content' => $product->description,
     // 'media:content' => [
     //   'url' => $product_image,
     //   'height' => '768',
     //   'width' => '1024'
     // ],
     // ]);
 }


}

// first param is the feed format
// optional: second param is cache duration (value of 0 turns off caching)
// optional: you can set custom cache key with 3rd param as string
return $feed->render('atom');

// to return your feed as a string set second param to -1
// $xml = $feed->render('atom', -1);
}
public function contact()
{

  $settings =Setting::first();
  return view('contact')
  ->with('categories',(Category::all()))
  ->with('pages',(Page::all()))
  ->with('settings',$settings);


}
public function contact_send(Request $request)
{
      // dd($request->all());
      $settings =Setting::first();
      $this->validate($request,[
        'name'=>'required',
        'email'=>'required|email',
        'subject'=>'required',
        'content'=>'required',
		'g-recaptcha-response' => 'required|captcha',
    ]);
    $data = array(
      'name'=>$request->name,
      'email'=>$request->email,
      'subject'=>$request->subject,
      'content'=>$request->content,
      'settings' => $settings,

   );
    Mail::send('emails.contact',$data, function($message) use($data,$settings){
      $message->from($data['email'],'Contact: '.$settings->site_name);
      $message->to($data['email'],$data['name']);//sends to sender
      $message->subject($data['subject']);
      $message->bcc($settings->site_email,'Admin');//sends to admin
      // $message->reply_to();
      // $message->cc();
    });
    Session::flash('success','Thank you for Contacting us,We will get back to you shortly');
    return redirect('/contact');

}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


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
