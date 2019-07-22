<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
// use App\Setting;
// use App\Invoice;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
     Schema::defaultStringLength(191);
      // Using view composer to set following variables globally
        // view()->composer('*',function($view) {
        //     // $view->with('user', Auth::user());
        //     // $view->with('site_settings', Setting::first());
        //     // if you need to access in controller and views:
        //       // view()->share('site_settings', $site_settings);
        //     $site_settings = Setting::first();
        //     Setting::set('site_settings', $site_settings);
        //     // ->with('invoices',Invoice::take(10)->orderBy('created_at', 'desc')->get());
        // });
        // app('site_settings')
        // $site_settings = Setting::first();
        // view()->share('site_settings', $site_settings);
        // ->with('invoices',Invoice::take(10)->orderBy('created_at', 'desc')->get());

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
