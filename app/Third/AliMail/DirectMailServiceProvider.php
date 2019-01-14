<?php namespace App\Third\AliMail;
/**
 * @author: wanghui
 * @date: 2019/1/14 下午2:50
 * @email:    hank.HuiWang@gmail.com
 */

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
/**
 * Class DirectMailServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class DirectMailServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->extend('swift.transport', function () {
            return new TransportManager($this->app);
        });
    }
}