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

    public $title;

    public $content;


    public function __construct($user,$title,$content)
    {
        $this->user = $user;

        $this->title = $title;

        $this->content = $content;
    }


}
