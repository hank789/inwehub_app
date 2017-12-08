<?php

namespace App\Events\Frontend\Auth;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserLoggedIn.
 */
class UserLoggedIn
{
    use SerializesModels;

    /**
     * @var
     */
    public $user;

    public $loginFrom;

    /**
     * @param $user
     * @param $loginFrom
     */
    public function __construct($user,$loginFrom='App')
    {
        $this->user = $user;
        $this->loginFrom = $loginFrom;
    }
}
