<?php

namespace App\Notifications;

use App\Channels\PushChannel;
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
        return ['database', 'broadcast', PushChannel::class];
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
                        $url = '/askCommunity/major/'.$source->question_id;
                        break;
                    case 2:
                        $url = '/askCommunity/interaction/'.$source->id;
                        break;
                }
                $notification_type = NotificationModel::NOTIFICATION_TYPE_NOTICE;
                $title = $this->support->user->name.'赞了您的回答';
                $avatar = $this->support->user->avatar;
                break;
            default:
                return;
        }
        return [
            'url'    => $url,
            'notification_type' => $notification_type,
            'avatar' => $avatar,
            'title'  => $title,
            'body'   => $source->getContentText(),
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
                break;
            default:
                return null;
        }
        return [
            'title' => $title,
            'body'  => $source->getContentText(),
            'payload' => ['object_type'=>$object_type,'object_id'=>$object_id],
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
