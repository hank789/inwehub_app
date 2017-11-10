<?php

namespace App\Events\Frontend\Auth;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered.
 */
class UserRegistered
{
    use SerializesModels;

    /**
     * @var
     */
    public $user;

    public $oauthDataId;

    /**
     * @param $user
     * @param $oauthDataId
     */
    public function __construct($user, $oauthDataId = '')
    {
        $this->user = $user;
        $this->oauthDataId = $oauthDataId;
    }
}
