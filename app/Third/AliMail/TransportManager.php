<?php namespace App\Third\AliMail;
/**
 * @author: wanghui
 * @date: 2019/1/14 下午3:40
 * @email:    hank.HuiWang@gmail.com
 */
use GuzzleHttp\Client;
use Illuminate\Mail\TransportManager as LaravelTransportManager;
class TransportManager extends LaravelTransportManager
{
    /**
     * 返回阿里云邮件驱动
     * @apiVersion 1.0.0
     * @return DirectMailTransport
     * @author: Zhengqian.zhu <zhuzhengqian@vchangyi.com>
     */
    public function createDirectmailDriver()
    {
        $config = $this->app['config']->get('services.directmail', []);
        return new DirectMailTransport(new Client($config), $config['key'], $config);
    }
}