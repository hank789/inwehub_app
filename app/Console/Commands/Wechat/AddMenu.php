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
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "授权登陆",
                        "url"  => "http://api.ywhub.com/wechat/oauth"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        $menu->add($buttons);
    }

}