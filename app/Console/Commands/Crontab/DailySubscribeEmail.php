<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\Push;
use App\Mail\DailySubscribe;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\User;
use App\Third\AliCdn\Cdn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailySubscribeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:daily:subscribe:email {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日热点推荐邮件推送';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('date');
        if (!$date) {
            $date = date('Y-m-d');
        }
        $begin = date('Y-m-d 00:00:00',strtotime($date));
        $end = date('Y-m-d 23:59:59',strtotime($date));
        $count = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->count();
        if ($count <=4) return;
        $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->orderBy('rate','desc')->take(10)->get();
        $list = [];
        foreach ($recommends as $recommend) {
            $item = Submission::find($recommend->source_id);
            $domain = $item->data['domain']??'';
            $link_url = config('app.url').'/trackEmail/1/'.$recommend->id.'/';

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
        //app推送
        $users = User::where('site_notifications','like','%email_daily_subscribe%@%')->get();
        $emails = [];
        foreach ($users as $user) {
            $email = $user->site_notifications['email_daily_subscribe'];
            if (isset($emails[$email])) continue;
            $this->info($email);
            $emails[$email] = $user->id;
            Mail::to($email)->send(new DailySubscribe($date,$user->id,$list));
        }
    }

}