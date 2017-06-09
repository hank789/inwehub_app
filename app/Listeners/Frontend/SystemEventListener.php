<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\System\FuncZan;
use App\Events\LogNotify;
use App\Models\Credit as CreditModel;
use App\Events\Frontend\System\Credit;
use App\Events\Frontend\System\Feedback;
use App\Events\Frontend\System\Push;
use App\Models\UserData;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Getui;
use Illuminate\Support\Facades\Cache;


/**
 * Class UserEventListener.
 */
class SystemEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param $event
     */
    public function feedback($event)
    {
        \Slack::to(config('slack.ask_activity_channel'))->send('用户['.$event->user->name.']['.$event->user->mobile.']对平台的意见反馈:'.$event->content);
    }

    /**
     * @param $event
     */
    public function funcZan($event)
    {
        $key = 'func:zan:'.$event->content;
        $count = Cache::get($key,0);
        $count = $count + 1;
        Cache::forever($key,$count);

        \Slack::to(config('slack.ask_activity_channel'))->send('用户['.$event->user->name.']['.$event->user->mobile.']对平台功能点了赞:'.$event->content);
    }

    /**
     * 推送事件
     * @param Push $event
     */
    public function push($event){
        $devices = UserDevice::where('user_id',$event->user->id)->where('status',1)->get();

        $data = [
            'title' => $event->title,
            'body'  => $event->body,
            'text'  => $event->body,
            'content' => json_encode($event->content),
            'payload' => $event->payload
        ];
        foreach($devices as $device){
            $tmp_id = $event->template_id;
            if($device->device_type == UserDevice::DEVICE_TYPE_IOS){
                $tmp_id = 4;
            }
            Getui::pushMessageToSingle($device->client_id,$data,$tmp_id);

        }
    }

    /**
     * 用户积分
     * @param Credit $event
     */
    public function credit($event){
        try{
            $action = $event->action;
            $user_id = $event->user_id;
            $coins = $event->coins;
            $credits = $event->credits;
            $source_id = $event->source_id;
            $source_subject = $event->source_subject;
            /*用户登陆只添加一次积分*/
            if($action == 'login' && CreditModel::where('user_id','=',$user_id)->where('action','=',$action)->where('created_at','>',Carbon::today())->count()>0){
                return false;
            }
            if($coins ==0 && $credits == 0) return false;
            DB::beginTransaction();
            /*记录详情数据*/
            CreditModel::create([
                'user_id' => $user_id,
                'action' => $action,
                'source_id' => $source_id,
                'source_subject' => $source_subject,
                'coins' => $coins,
                'credits' => $credits,
                'created_at' => Carbon::now()
            ]);

            /*修改用户账户信息*/
            UserData::find($user_id)->increment('coins',$coins);
            UserData::find($user_id)->increment('credits',$credits);
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    /**
     * 错误日志告警
     * @param LogNotify $event
     */
    public function logNotify($event){
        switch($event->level){
            case 'error':
                //Notify team of error
                \Slack::attach([
                    'pretext' => '错误详细信息',
                    'color' => 'bad',
                    'fields' => [
                        [
                            'title' => '',
                            'value' => json_encode($event->context,JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ])->send($event->message);
                break;
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
            Feedback::class,
            'App\Listeners\Frontend\SystemEventListener@feedback'
        );
        $events->listen(
            Push::class,
            'App\Listeners\Frontend\SystemEventListener@push'
        );

        $events->listen(
            Credit::class,
            'App\Listeners\Frontend\SystemEventListener@credit'
        );

        $events->listen(
            FuncZan::class,
            'App\Listeners\Frontend\SystemEventListener@funcZan'
        );

        $events->listen(
            LogNotify::class,
            'App\Listeners\Frontend\SystemEventListener@logNotify'
        );
    }
}
