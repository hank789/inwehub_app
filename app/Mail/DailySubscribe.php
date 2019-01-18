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
        $list = [];
        foreach ($recommends as $recommend) {
            $item = Submission::find($recommend->source_id);
            $domain = $item->data['domain']??'';
            $link_url = $item->data['url']??'';
            if ($domain == 'mp.weixin.qq.com') {
                if (!(str_contains($link_url, 'wechat_redirect') || str_contains($link_url, '__biz=') || str_contains($link_url, '/s/'))) {
                    $link_url = config('app.url').'/articleInfo/'.$item->id.'?inwehub_user_device=wechat';
                }
            }

            $img = $item->data['img']??'';
            if (is_array($img)) {
                if ($img) {
                    $img = $img[0];
                } else {
                    $img = '';
                }
            }
            $list[] = [
                'id'    => $item->id,
                'title' => strip_tags($item->data['title']??$item->title),
                'type'  => $item->type,
                'domain'    => $domain,
                'img'   => $img,
                'slug'      => $item->slug,
                'category_id' => $item->category_id,
                'is_upvoted'     => 0,
                'link_url'  => $link_url,
                'rate'  => (int)(substr($item->rate,8)?:0),
                'comment_number' => $item->comments_number,
                'support_number' => $item->upvotes,
                'share_number' => $item->share_number,
                'tags' => [],
                'created_at'=> (string)$item->created_at
            ];
        }
        return $this->from('no-reply@mail.inwehub.com','Inwehub每日热门')->view('emails.daily_subscribe')->with('date',$this->date)->with('items',$list)->subject('今日热门推荐');
    }
}
