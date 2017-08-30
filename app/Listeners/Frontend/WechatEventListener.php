<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\Wechat\Notice;
use App\Models\UserOauth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


/**
 * Class UserEventListener.
 */
class WechatEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 微信推送事件
     * @param Notice $event
     */
    public function notice($event){
        //微信通知
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('user_id',$event->user_id)->where('status',1)->orderBy('updated_at','desc')->first();
        if($oauthData) {
            $wechat = app('wechat');
            $notice = $wechat->notice;
            $wx_notice_data = [
                "first"  => $event->title,
                "keyword1"   => str_limit($event->keyword1,100),
                "keyword2"   => $event->keyword2,
                "keyword3"   => $event->keyword3,
                "remark" => $event->remark
            ];
            $notice->uses($event->template_id)->withUrl($event->target_url)->andData($wx_notice_data)->andReceiver($oauthData->openid)->send();
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Notice::class,
            'App\Listeners\Frontend\WechatEventListener@notice'
        );
    }
}
