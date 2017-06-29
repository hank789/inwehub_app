<?php namespace App\Console\Commands\Wechat;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Console\Command;

class AddMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wechat:add_menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加微信菜单';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $wechat = app('wechat');
        $menu = $wechat->menu;
        $url = config('app.url');
        $mobile_url = config('app.mobile_url');
        $buttons = [
            [
                "name"       => "平台服务",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "马上提问",
                        "url"  => $url."/wechat/oauth?redirect=/home"
                    ],
                    [
                        "type" => "view",
                        "name" => "顾问名片",
                        "url" => $url."/wechat/oauth?redirect=/my"
                    ],
                    [
                        "type" => "view",
                        "name" => "推荐专家",
                        "url" => "http://cn.mikecrm.com/tgx3vq8"
                    ],
                    [
                        "type" => "view",
                        "name" => "注册申请",
                        "url" => "http://cn.mikecrm.com/ovYy1u4"
                    ],
                ],
            ],
            [
                "name"       => "关于我们",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "公司官网",
                        "url"  => "https://www.inwehub.com"
                    ]
                ],
            ],
        ];
        $menu->add($buttons);
    }

}