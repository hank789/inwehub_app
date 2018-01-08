<?php

namespace App\Listeners\Frontend\Auth;
use App\Events\Frontend\Auth\UserRegistered;
use App\Logic\TaskLogic;
use App\Models\Attention;
use App\Models\Feed\Feed;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Readhub\ReadHubUser;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\UserOauth;
use App\Notifications\NewInviteUserRegister;
use App\Notifications\NewMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

/**
 * Class UserEventListener.
 */
class UserEventListener implements ShouldQueue
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
    public function onLoggedIn($event)
    {
        \Slack::send('用户登录: '.formatSlackUser($event->user).';设备:'.$event->loginFrom);
    }

    /**
     * @param $event
     */
    public function onLoggedOut($event)
    {
        \Slack::send('用户登出: '.formatSlackUser($event->user).';设备:'.$event->from);
    }

    /**
     * @param UserRegistered $event
     */
    public function onRegistered($event)
    {
        // 生成新手任务
        // 完善用户信息
        TaskLogic::task($event->user->id,'newbie_complete_userinfo',0,Task::ACTION_TYPE_NEWBIE_COMPLETE_USERINFO);
        // 阅读评论
        TaskLogic::task($event->user->id,'newbie_readhub_comment',0,Task::ACTION_TYPE_NEWBIE_READHUB_COMMENT);
        // 发起提问
        TaskLogic::task($event->user->id,'newbie_ask',0,Task::ACTION_TYPE_NEWBIE_ASK);
        if ($event->oauthDataId) {
            $oauthData = UserOauth::find($event->oauthDataId);
            $event->user->avatar = saveImgToCdn($oauthData->avatar);
            $event->user->save();
        }
        $title = '';
        if ($event->user->company) {
            $title .= '；公司：'.$event->user->company;
            Redis::connection()->hset('user_company_level',$event->user->id,$event->user->company);
        }
        if ($event->user->title) {
            $title .= '；职位：'.$event->user->title;
        }
        if ($event->user->email) {
            $title .= '；邮箱：'.$event->user->email;
        }
        if ($event->user->rc_uid) {
            $rc_user = User::find($event->user->rc_uid);
            $title .= '；邀请者：'.formatSlackUser($rc_user);
            //给邀请者发送通知
            $rc_user->notify(new NewInviteUserRegister($rc_user->id,$event->user->id));
        }
        //客服欢迎信息
        //客服
        $contact_id = Role::getCustomerUserId();
        $contact = User::find($contact_id);
        $room = Room::create([
            'user_id' => $contact_id,
            'r_type'  => Room::ROOM_TYPE_WHISPER
        ]);

        $message = $contact->messages()->create([
            'data' => ['text'=>'您好，欢迎您加入InweHub，欢迎体验社区的各种功能，找到您感兴趣的专家、用户或者问答，希望您使用愉快！如有任何疑问或建议，请随时联系我！'],
            'room_id' => $room->id
        ]);

        MessageRoom::create([
            'room_id' => $room->id,
            'message_id' => $message->id
        ]);

        RoomUser::create([
            'user_id' => $contact_id,
            'room_id' => $room->id
        ]);
        RoomUser::create([
            'user_id' => $event->user->id,
            'room_id' => $room->id
        ]);
        //客服关注新注册用户
        Attention::create([
            'user_id'     => $contact_id,
            'source_id'   => $event->user->id,
            'source_type' => get_class($contact),
        ]);
        $event->user->userData->increment('followers');
        //产生一条关注的feed
        feed()
            ->causedBy($contact)
            ->performedOn($event->user)
            ->withProperties([
                'follow_user_id' => $event->user->id
            ])
            ->log($contact->name.'关注了新的朋友', Feed::FEED_TYPE_FOLLOW_USER);

        // broadcast the message to the other person
        $event->user->notify(new NewMessage($event->user->id,$message));

        \Slack::send('新用户注册: '.formatSlackUser($event->user).'；设备：'.$event->from.$title);
    }

    /**
     * @param $event
     */
    public function onConfirmed($event)
    {
        \Slack::send('User Confirmed: '.$event->user->name);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedIn::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedIn'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedOut::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedOut'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserRegistered::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onRegistered'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserConfirmed::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onConfirmed'
        );
    }
}
