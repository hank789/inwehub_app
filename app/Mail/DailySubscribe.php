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

    protected $dataList;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($date,$uid,$list)
    {
        $this->date = $date;
        $this->uid = $uid;
        $this->dataList = $list;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $weekarray=array("日","一","二","三","四","五","六");
        $weekday = '星期'.$weekarray[date('w',strtotime($this->date))];
        foreach ($this->dataList as &$item) {
            $item['link_url'] .= $this->uid;
        }
        return $this->from('notice@inwehub.com','Inwehub每日热门')->view('emails.daily_subscribe')->with('date',$this->date)->with('items',$this->dataList)->with('weekday',$weekday)->with('uid',$this->uid)->subject('今日热门推荐');
    }
}
