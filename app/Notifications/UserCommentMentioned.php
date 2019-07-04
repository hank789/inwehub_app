<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCommentMentioned extends Notification implements ShouldBroadcast,ShouldQueue
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
        $extra_body = '';
        switch ($source_type) {
            case 'App\Models\Submission':
                $url = '/c/'.$source->category_id.'/'.$source->slug;
                $notification_type = NotificationModel::NOTIFICATION_TYPE_READ;
                $extra_body = '原文：'.$source->formatTitle();
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
                break;
        }
        return [
            'url'    => $url,
            'notification_type' => $notification_type,
            'name'   => $this->comment->user->username,
            'avatar' => $this->comment->user->avatar,
            'title'  => $this->comment->user->name.'在回复中提到了你',
            'body'   => $this->comment->formatContent(),
            'comment_id' => $this->comment->id,
            'extra_body' => $extra_body
        ];
    }

    public function toPush($notifiable)
    {
        $title = $this->comment->user->name.'在回复中提到了你';
        $body = $this->comment->formatContent();
        $source_type = $this->comment->source_type;
        $source = $this->comment->source;
        switch ($source_type) {
            case 'App\Models\Submission':
                $url = '/c/'.$source->category_id.'/'.$source->slug;
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
                break;
        }
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>'readhub_username_mentioned','object_id'=>$url],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您好，'.$this->comment->user->name.'在回复中提到了你';
        $keyword2 = date('Y-m-d H:i:s',strtotime($this->comment->created_at));
        $keyword3 = $this->comment->formatContent();
        $remark = '请点击查看详情！';
        $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
        if (config('app.env') != 'production') {
            $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
        }

        $source_type = $this->comment->source_type;
        $source = $this->comment->source;
        switch ($source_type) {
            case 'App\Models\Submission':
                $url = '/c/'.$source->category_id.'/'.$source->slug;
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
                break;
        }

        $target_url = config('app.mobile_url').'#'.$url;
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
