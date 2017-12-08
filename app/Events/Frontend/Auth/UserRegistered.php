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

    public $from;

    /**
     * @param $user
     * @param $oauthDataId
     * @param $from
     */
    public function __construct($user, $oauthDataId = '',$from = 'App')
    {
        $this->user = $user;
        $this->oauthDataId = $oauthDataId;
        $this->from = $from;
    }
}
