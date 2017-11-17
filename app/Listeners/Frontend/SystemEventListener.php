<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\System\FuncZan;
use App\Events\Frontend\System\SystemNotify;
use App\Models\Credit as CreditModel;
use App\Events\Frontend\System\Credit;
use App\Events\Frontend\System\Feedback;
use App\Events\Frontend\System\Push;
use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserDevice;
use App\Notifications\IntegralLog;
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
        \Slack::to(config('slack.ask_activity_channel'))->send('用户['.$event->user->name.']['.$event->user->mobile.']['.$event->title.']:'.$event->content);
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
     * @param systemNotify $event
     */
    public function systemNotify($event){
        try {
            \Slack::to(config('slack.ask_activity_channel'))
                ->attach(
                    [
                        'fields' => $event->fields
                    ]
                )
                ->send($event->message);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * 推送事件
     * @param Push $event
     */
    public function push($event){
        $devices = UserDevice::where('user_id',$event->user_id)->where('status',1)->get();

        //最长2048个字符
        $body = str_limit($event->body);
        $data = [
            'title' => $event->title,
            'body'  => $body,
            'text'  => $body,
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
            $user_data = UserData::find($user_id);
            /*记录详情数据*/
            $credit = CreditModel::create([
                'user_id' => $user_id,
                'action' => $action,
                'source_id' => $source_id,
                'source_subject' => $source_subject,
                'coins' => $coins,
                'credits' => $credits,
                'current_coins' => $user_data->coins,
                'current_credits' => $user_data->credits,
                'created_at' => Carbon::now()
            ]);

            /*修改用户账户信息*/
            UserData::find($user_id)->increment('coins',$coins);
            UserData::find($user_id)->increment('credits',$credits);
            DB::commit();
            $user = User::find($user_id);
            //更新用户等级
            $next_level = $user->getUserLevel();
            if ($next_level != $user->userData->user_level) {
                $user->userData->user_level = $next_level;
                $user->userData->save();
                ReadHubUser::syncUser($user);
            }
            $user_data->user->notify(new IntegralLog($user_id,$credit));
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            app('sentry')->captureException($e);
            return false;
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
            SystemNotify::class,
            'App\Listeners\Frontend\SystemEventListener@systemNotify'
        );
    }
}
