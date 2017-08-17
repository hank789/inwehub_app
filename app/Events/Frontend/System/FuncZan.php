<?php

namespace App\Events\Frontend\System;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

/**
 * Class UserRegistered.
 */
class FuncZan implements ShouldBroadcast
{

    use InteractsWithSockets, SerializesModels;

    public $content;


    public function __construct($content)
    {
        $this->content = $content;

        $this->broadcastToEveryone();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        \Log::info('test',[1234]);
        return new PrivateChannel('notification.1');
    }

}
