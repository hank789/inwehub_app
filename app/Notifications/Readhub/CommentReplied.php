<?php

namespace App\Notifications\Readhub;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentReplied extends Notification implements ShouldBroadcast
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
        $via = ['database', 'broadcast'];
        if ($notifiable->site_notifications['push_notify_comments_replied']??true){
            $via[] = PushChannel::class;
        }
        if ($notifiable->site_notifications['wechat_notify_comments_replied']??true){
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

        return [
            'content' => $this->message['name'],
            'object_type'  => 'readhub_comment_replied',
            'object_id' => $this->message['comment_id'],
            'target_url' => $this->message['url']
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
