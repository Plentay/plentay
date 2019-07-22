<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Auth;
use App\Category;
use App\Product;
use App\User;
use App\Currency;
use App\Setting;
use Session;
use DataTables;
use Goutte\Client;
use Sunra\PhpSimple\HtmlDomParser;
class GlobalUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'globalupdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Global Product Update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      \Log::info('Starting General Product Update  @'.\Carbon\Carbon::now());
        ini_set('max_execution_time', -1);
        // error_reporting(0);
        $settings =Setting::first();
        $active_users = 0;
        ///------------------------------Session-----------------------------///
        Session::put('counter', 0);
        ///------------------------------Session-----------------------------///
          $users = User::all();
       foreach ($users as $user){//start for each user
         if ($users->active = 0){
           \Log::info("Skipped|$user->name|inactive");
           continue;//if user is inactive skip
         }
         $active_users++;//active users that was processed

         if (empty($user->price_update_element)){
           \Log::info("Skipped|$user->name|Price Update Element is Empty");
           continue;//skip if the elements is empty
         }

         $user_pro_count = count(User::find($user->id)->products);
         if ($user_pro_count==0){
           \Log::info("Skipped|$user->name|NO Product was found for this user");
            continue;//check if user has Products
         }


         //RUN

         foreach ($user->products as $product) {//start for each product
           sleep(rand(1,2)) ;
           if (empty($product->original_url)){
             \Log::info("Skipped|$product->name| Product url is empty");
             continue;
           }
           $product_url ="$product->original_url";
           $product_price_element = "$user->price_update_element";
           $product_description_element = "$user->description_update_element";

           $client = new Client();
           $client->setHeader("user-agent", "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0");
           $crawler = $client->request('GET', $product_url);
           if (false == ( $crawler = $client->request('GET', $product_url))) {
             // Error
             \Log::info("Skipped|$product->name|HtmlDomParser| Error");
               continue;
           }
           if (empty($crawler)){
             // Error
             \Log::info("Skipped|$product->name|Html| Error");
               continue;
           }
           $products = [];
                   // Find item link element

                   if ($crawler->filter($product_price_element)->count() > 0 ) {
                     $product_description='';
                     $product_price='';
                       $price = $crawler->filter($product_price_element)->text();
                       $product_price              = str_replace(',', '', $price);
                       $product_price              = str_replace(' ', '', $product_price);
                       $product_price              = preg_replace("/[^0-9.,]/", "", $product_price);
                       $product_price              = preg_replace('/D/', '', $product_price);//floatval($num);

                     if ($crawler->filter($product_description_element)->count() > 0 ) {//a.vip
                         $product_description = $crawler->filter($product_description_element)->html();
                     }


                     $product_user = $product->user->name;//get user name
                     //if the new price is empty
                     if (empty($product_price)){
                       \Log::info("Skipped|$product_user|$product->name| new price is empty");
                       continue;
                     }
                     // if description has value
                     if (!empty($product_description)){
                       $product->description = $product_description;
                     }

                     if (!empty($product_price)){
                         \Log::info("Updated|$product_user|$product->name|new price: $product_price");
                       //save
                       $product->amount = $product_price;
                       $product->save();

                       ///------------Session-----------///
                       $counter = Session::get('counter');
                       $counter++;
                       Session::put('counter', $counter);
                       ///------------Session-----------///
                     }


                };


       }//end for each product



     }//end for each user
     ///------------Session-----------///
     $counter = Session::get('counter');
     //-----------UNSET----------------//
       Session::forget('counter');
     ///------------Session-----------///
   \Log::info('Ending General Product Update Now @'.\Carbon\Carbon::now());

   $this->info('Successfully Ran Update @ '.\Carbon\Carbon::now());
   $this->info("Active Merchants:($active_users)");
   $this->info("Success: Updated ($counter) Products");
    }
}
