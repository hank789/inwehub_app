<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    protected $admin_namespace = 'App\Http\Controllers\Admin';

    protected $api_namespace = 'App\Api\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        // vendor
        $this->mapVendorRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware(['web'])
             ->prefix('manager')
             ->namespace($this->namespace)
             ->group(base_path('routes/operator.php'));
        Route::middleware(['web','auth','auth.admin'])
            ->prefix('admin')
            ->namespace($this->admin_namespace)
            ->group(base_path('routes/admin.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->prefix('api')
            ->namespace($this->api_namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapVendorRoutes()
    {
        $files = config('component_routes', []);
        foreach ($files as $file) {
            include_once $file;
        }
    }
}
