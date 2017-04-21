<?php

namespace App\Events\Frontend\System;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered.
 */
class Feedback
{

    use SerializesModels;

    /**
     * @var
     */
    public $user;

    public $content;


    public function __construct($user,$content)
    {
        $this->user = $user;

        $this->content = $content;
    }


}
