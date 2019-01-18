<?php

namespace App\Mail;

use App\Models\RecommendRead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailySubscribe extends Mailable
{
    use Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $begin = date('Y-m-d 00:00:00',strtotime($this->date));
        $end = date('Y-m-d 23:59:59',strtotime($this->date));
        $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->orderBy('rate','desc')->take(10)->get();
        return $this->from('no-reply@mail.inwehub.com','Inwehub每日热门')->view('emails.daily_subscribe')->with('date',$this->date)->with('items',$recommends)->subject('今日热门推荐');
    }
}
