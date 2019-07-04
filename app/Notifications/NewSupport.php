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
        $via = ['database'];
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
                $titleType = $source->type == 'link' ? '文章':'分享';
                $url = '/c/'.$source->category_id.'/'.$source->slug;
                if ($source->type == 'review') {
                    $titleType = '点评';
                    $url = '/dianping/comment/'.$source->slug;
                }
                $notification_type = NotificationModel::NOTIFICATION_TYPE_READ;
                $title = $this->support->user->name.'赞了您的'.$titleType;
                $avatar = $this->support->user->avatar;
                $body = strip_tags($source->title);
                if ($source->type == 'link') {
                    $body = strip_tags($source->data['title']);
                }
                break;
            case 'App\Models\Comment':
                $notification_type = NotificationModel::NOTIFICATION_TYPE_TASK;
                $avatar = $this->support->user->avatar;

                $sourceS = $source->source;
                switch ($source->source_type) {
                    case 'App\Models\Answer':
                        $url = '/ask/offer/'.$sourceS->id;
                        break;
                    case 'App\Models\Submission':
                        $url = '/c/'.$sourceS->category_id.'/'.$sourceS->slug;
                        if ($sourceS->type == 'review') {
                            $url = '/dianping/comment/'.$sourceS->slug;
                        }
                        break;
                }

                $title = $this->support->user->name.'赞了您的评论';
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
                $titleType = $source->type == 'link' ? '文章':'分享';
                $object_id = '/c/'.$source->category_id.'/'.$source->slug;
                if ($source->type == 'review') {
                    $titleType = '点评';
                    $object_id = '/dianping/comment/'.$source->slug;
                }
                $object_type = 'readhub_submission_upvoted';
                $title = $this->support->user->name.'赞了您的'.$titleType;
                $body = $source->formatTitle();

                break;
            case 'App\Models\Comment':
                $sourceS = $source->source;
                switch ($source->source_type) {
                    case 'App\Models\Answer':
                        $object_type = 'free_answer_new_support';
                        $object_id = $sourceS->id;
                        break;
                    case 'App\Models\Submission':
                        $object_id = '/c/'.$sourceS->category_id.'/'.$sourceS->slug;
                        if ($sourceS->type == 'review') {
                            $object_id = '/dianping/comment/'.$sourceS->slug;
                        }
                        $object_type = 'readhub_submission_upvoted';
                        break;
                }

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
