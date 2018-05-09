<?php

namespace App\Providers;

use App\Services\CronService; 
use App\Services\WechatService; 
use App\Repositories\Eloquent\SmsRepository as Sms;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        \Schema::defaultStringLength(191); 
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('cronservice', function($app){ 
            return new CronService(new Sms($app));
        });  
        $this->app->singleton('wechatservice', function($app){
            return new WechatService(new Sms($app));
        });  
    }
}
