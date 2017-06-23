<?php

namespace App\Third\Wechat\ServiceProviders;

use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as LaravelRouteServiceProvider;

class RouteServiceProvider extends LaravelRouteServiceProvider
{
    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     */
    public function map(Router $router)
    {
        $router->group([
            'namespace' => 'App\Third\Wechat\Controllers'
        ], function (Router $router) {
            $router->post(config('wechat.open_platform.serve_url'), 'EasyWeChatController@openPlatformServe');
        });
    }
}
