<?php

namespace App\Mail;

use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailySubscribe extends Mailable
{
    use Queueable, SerializesModels;

    protected $date;

    protected $uid;

    protected $list;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($date,$uid,$list)
    {
        $this->date = $date;
        $this->uid = $uid;
        $this->list = $list;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (count($this->list) <= 4) return;
        foreach ($this->list as &$item) {
            $item['link_url'] .= $this->uid;
        }
        return $this->from('notice@inwehub.com','Inwehub每日热门')->view('emails.daily_subscribe')->with('date',$this->date)->with('items',$this->list)->subject('今日热门推荐');
    }
}
