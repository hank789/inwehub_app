<?php

namespace App\Events\Frontend\Notification;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserConfirmed.
 */
class MarkAsRead
{
    use SerializesModels;

    /**
     * @var
     */
    public $user_id;

    public $notification_type;


    public function __construct($user_id, $notification_type)
    {
        $this->user_id = $user_id;
        $this->notification_type = $notification_type;
    }
}
