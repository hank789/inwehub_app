<?php

namespace App\Notifications\Readhub;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentReplied extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable;

    protected $message;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, array $message)
    {
        $this->user_id = $user_id;
        $this->message = $message;
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
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_commented']??true)){
            $via[] = PushChannel::class;
            $via[] = WechatNoticeChannel::class;
        }
        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
                     ->title($this->comment->owner->username.' replied to your comment on "'.$this->submission->title.'":')
                     ->subject('Your comment on"'.$this->submission->title.'" just got a new reply.')
                     ->line('"'.$this->comment->body.'"')
                     ->action('Reply', config('app.url').'/'.$this->submission->slug)
                     ->line('Thank you for being a part of our alpha program!');
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
        return $this->message;
    }

    public function toPush($notifiable)
    {
        $title = $this->message['title'];
        $body = $this->message['body'];
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>'readhub_comment_replied','object_id'=>$this->message['url']],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您好，您的回复收到一条评论';
        $object = Comment::find($this->message['comment_id']);
        $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
        $keyword3 = $object->formatContent();
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
