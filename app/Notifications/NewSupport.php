<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use App\Models\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSupport extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $support;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Support $support)
    {
        $this->user_id = $user_id;
        $this->support = $support;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database','broadcast'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_upvoted']??true)){
            $via[] = PushChannel::class;
        }
        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $source = $this->support->source;
        switch ($this->support->supportable_type) {
            case 'App\Models\Answer':
                $question = Question::find($source->question_id);
                switch ($question->question_type){
                    case 1:
                        $url = '/ask/offer/'.$source->id;
                        break;
                    case 2:
                        $url = '/ask/offer/'.$source->id;
                        break;
                }
                $notification_type = NotificationModel::NOTIFICATION_TYPE_TASK;
                $title = $this->support->user->name.'赞了您的回答';
                $avatar = $this->support->user->avatar;
                $body = $source->getContentText();
                break;
            case 'App\Models\Submission':
                $notification_type = NotificationModel::NOTIFICATION_TYPE_READ;
                $title = $this->support->user->name.'赞了您的'.($source->type == 'link' ? '文章':'分享');
                $avatar = $this->support->user->avatar;
                $body = $source->formatTitle();
                $url = '/c/'.$source->category_id.'/'.$source->slug;
                break;
            case 'App\Models\Comment':
                $answer = $source->source;
                $url = '/ask/offer/'.$answer->id;
                $notification_type = NotificationModel::NOTIFICATION_TYPE_TASK;
                $title = $this->support->user->name.'赞了您的评论';
                $avatar = $this->support->user->avatar;
                $body = $source->content;
                break;
            default:
                return;
        }
        return [
            'url'    => $url,
            'notification_type' => $notification_type,
            'avatar' => $avatar,
            'title'  => $title,
            'body'   => $body,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $source = $this->support->source;
        switch ($this->support->supportable_type) {
            case 'App\Models\Answer':
                $question = Question::find($source->question_id);
                $object_type = 'pay_answer_new_support';
                $object_id = $source->question_id;
                switch ($question->question_type){
                    case 1:
                        $object_type = 'pay_answer_new_support';
                        break;
                    case 2:
                        $object_type = 'free_answer_new_support';
                        $object_id = $source->id;
                        break;
                    default:
                        return null;
                }
                $title = $this->support->user->name.'赞了您的回答';
                $body = $source->getContentText();
                break;
            case 'App\Models\Submission':
                $object_type = 'readhub_submission_upvoted';
                $title = $this->support->user->name.'赞了您的'.($source->type == 'link' ? '文章':'分享');
                $body = $source->formatTitle();
                $object_id = '/c/'.$source->category_id.'/'.$source->slug;
                break;
            case 'App\Models\Comment':
                $answer = $source->source;
                $object_type = 'free_answer_new_support';
                $object_id = $answer->id;
                $title = $this->support->user->name.'赞了您的评论';
                $body = $source->content;
                break;
            default:
                return null;
        }
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>$object_type,'object_id'=>$object_id],
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
