<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Comment;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewComment extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

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
     * @param  mixed  $notifiable
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
        $source = $this->comment->source;
        switch ($this->comment->source_type) {
            case 'App\Models\Article':
                return;
                break;
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
                $title = $this->comment->user->name.'回复了您的回答';
                break;
            default:
                return;
        }
        return [
            'url'    => $url,
            'notification_type' => $notification_type,
            'avatar' => $this->comment->user->avatar,
            'title'  => $title,
            'body'   => $this->comment->formatContent(),
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $source = $this->comment->source;
        switch ($this->comment->source_type) {
            case 'App\Models\Article':
                return;
                break;
            case 'App\Models\Answer':
                $question = Question::find($source->question_id);
                $object_type = 'pay_answer_new_comment';
                $object_id = $source->question_id;
                switch ($question->question_type){
                    case 1:
                        $object_type = 'pay_answer_new_comment';
                        break;
                    case 2:
                        $object_type = 'free_answer_new_comment';
                        $object_id = $source->id;
                        break;
                }
                $title = $this->comment->user->name.'回复了您的回答';
                break;
            default:
                return;
        }
        return [
            'title' => $title,
            'body'  => $this->comment->formatContent(),
            'payload' => ['object_type'=>$object_type,'object_id'=>$object_id],
        ];
    }

    public function toWechatNotice($notifiable){
        $source = $this->comment->source;
        switch ($this->comment->source_type) {
            case 'App\Models\Article':
                return null;
                break;
            case 'App\Models\Answer':
                $question = Question::find($source->question_id);
                switch ($question->question_type){
                    case 1:
                        $first = '您好，有人回复了您的回答';
                        $remark = '请点击查看详情！';
                        $target_url = config('app.mobile_url').'#/ask/offer/'.$source->id;
                        break;
                    case 2:
                        $first = '您好，有人回复了您的回答';
                        $remark = '请点击查看详情！';
                        $target_url = config('app.mobile_url').'#/ask/offer/'.$source->id;
                        break;
                    default:
                        return null;
                        break;
                }
                break;
            default:
                return null;
        }
        $keyword2 = date('Y-m-d H:i',strtotime($source->created_at));
        $keyword3 = $this->comment->formatContent();
        $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
        if (config('app.env') != 'production') {
            $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
        }
        return [
            'first'    => $first,
            'keyword1' => $this->comment->user->name,
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
