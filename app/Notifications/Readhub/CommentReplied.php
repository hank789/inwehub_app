<?php

namespace App\Notifications\Readhub;

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
        return ['database', 'broadcast'];
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

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
