<?php

namespace App\Events\Frontend\Auth;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserLoggedOut.
 */
class UserLoggedOut
{
    use SerializesModels;

    /**
     * @var
     */
    public $user;

    public $from;

    /**
     * @param $user
     * @param $from
     */
    public function __construct($user, $from='App')
    {
        $this->user = $user;
        $this->from = $from;
    }
}
