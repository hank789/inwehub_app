<?php namespace App\Third\Weapp;

use Illuminate\Support\ServiceProvider;

class WxxcxServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('wxxcx', function ()
        {
            return new WeApp(config('weapp.appid', ''),config('weapp.secret', ''),storage_path('app/weapp/'));
        });

        $this->app->alias('wxxcx', WeApp::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['wxxcx', WeApp::class];
    }
}
