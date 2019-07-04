<?php

namespace App\Notifications\Readhub;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UsernameCommentMentioned extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable;

    protected $comment;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Comment $comment)
    {
        $this->user_id = $user_id;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_mentioned']??true)){
            $via[] = PushChannel::class;
            $via[] = WechatNoticeChannel::class;
        }
        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        $source_type = $this->comment->source_type;
        $source = $this->comment->source;
        switch ($source_type) {
            case 'App\Models\Submission':
                $url = '/c/'.$source->category_id.'/'.$source->slug;
                break;
        }
        return [
            'url'    => $url,
            'name'   => $this->comment->user->username,
            'avatar' => $this->comment->user->avatar,
            'title'  => $this->comment->user->username.'提到了你',
            'body'   => $this->comment->body,
            'comment_id' => $this->comment->id,
            'extra_body' => '原文：'.$source->title
        ];
    }

    public function toPush($notifiable)
    {
        $title = $this->message['title'];
        $body = $this->message['body'];
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>'readhub_username_mentioned','object_id'=>$this->message['url']],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您好，'.$this->message['name'].'在回复中提到了你';
        $object = Comment::find($this->message['comment_id']);
        $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
        $keyword3 = $object->body;
        $remark = '请点击查看详情！';
        $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
        if (config('app.env') != 'production') {
            $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
        }
        $target_url = config('app.mobile_url').'#'.$this->message['url'];
        return [
            'first'    => $first,
            'keyword1' => $this->message['name'],
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
