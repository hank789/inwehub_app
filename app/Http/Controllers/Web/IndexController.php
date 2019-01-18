<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\RecommendRead;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use Illuminate\Http\Request;
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: hank.huiwang@gmail.com
 */

class IndexController extends Controller
{
    public function index()
    {
        $date = date('Y-m-d');
        $begin = date('Y-m-d 00:00:00',strtotime($date));
        $end = date('Y-m-d 23:59:59',strtotime($date));
        $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->orderBy('rate','desc')->take(10)->get();
        return view('emails.daily_subscribe')->with('date',$date)->with('items',$recommends);
    }

    public function articleInfo($id, Request $request)
    {
        $article = WechatWenzhangInfo::where('topic_id',$id)->where('status',2)->first();
        \Log::info('test',[$id]);
        if (!$article) {
            $submission = Submission::find($id);
            if (!$submission) return 'bad request';
            return redirect($submission->data['url']);
        }
        if (in_array($request->input('inwehub_user_device','web'),['web','wechat']) || $article->source_type != 1 || str_contains($article->content_url, 'wechat_redirect') || str_contains($article->content_url, '__biz=')) {
            return redirect($article->content_url);
        }
        $date = strtotime($article->date_time);
        $today = strtotime(date('Y-m-d 00:00:00'));
        $showDate = '';
        if ($date >= $today) {
            $showDate = '今天';
        } elseif ($date >= $today-60*60*24) {
            $showDate = '昨天';
        } elseif ($date >= $today-2*60*60*24) {
            $showDate = '前天';
        } elseif ($date >= $today-3*60*60*24) {
            $showDate = '3天前';
        } elseif ($date >= $today-4*60*60*24) {
            $showDate = '4天前';
        } elseif ($date >= $today-5*60*60*24) {
            $showDate = '5天前';
        } elseif ($date >= $today-6*60*60*24) {
            $showDate = '6天前';
        } elseif ($date >= $today-2*7*60*60*24) {
            $showDate = '1周前';
        } elseif ($date >= strtotime(date('Y-01-01 00:00:00'))) {
            $showDate = date('m月d日',$date);
        } else {
            $showDate = date('Y-m-d',$date);
        }
        return view('h5::article')->with('article',$article)->with('showDate',$showDate);
    }
}