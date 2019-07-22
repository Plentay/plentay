<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Setting;
use App\Product;
use App\Gateway;
use App;
use URL;
use Session;
class GlobalSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'globalsitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates SiteMap';

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
      // create new sitemap object
$sitemap = App::make('sitemap');

// get all products from db (or wherever you store them)
$products = \DB::table('products')->orderBy('created_at', 'desc')->get();

// counters
$counter = 0;
$sitemapCounter = 0;
$productCounter = 0;
// add every product to multiple sitemaps with one sitemap index
foreach ($products as $product) {
if ($counter == 50000) {
  // generate new sitemap file
  $sitemap->store('xml', 'sitemap-' . $sitemapCounter);
  // add the file to the sitemaps array
  $sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
  // reset items array (clear memory)
  $sitemap->model->resetItems();
  // reset the counter
  $counter = 0;
  // count generated sitemap
  $sitemapCounter++;
}

// add product to items array
$product_url = url('/').'/'.$product->slug.'-'.$product->id;
$sitemap->add($product_url, $product->created_at, '1.0', 'daily');
// count number of elements
$counter++;
$productCounter++;
}

// you need to check for unused items
if (!empty($sitemap->model->getItems())) {
// generate sitemap with last items
$sitemap->store('xml', 'sitemap-' . $sitemapCounter);
// add sitemap to sitemaps array
$sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
// reset items array
$sitemap->model->resetItems();
}
$Totalsitemaps = $sitemapCounter+1;
// generate new sitemapindex that will contain all generated sitemaps above
$sitemap->store('sitemapindex', 'sitemap');

\Log::info('Ending General Product Update Now @'.\Carbon\Carbon::now());
$this->info('Successfully Ran Update @ '.\Carbon\Carbon::now());
$this->info("Added $productCounter Products");
$this->info("Successfully Created $Totalsitemaps Sitemap(s)");
  }
}
