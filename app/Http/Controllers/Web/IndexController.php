<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Scraper\WechatWenzhangInfo;
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
        return 'hello world';
    }

    public function articleInfo($id, Request $request)
    {
        $article = WechatWenzhangInfo::where('topic_id',$id)->where('status',2)->first();
        if (!$article || $article->source_type != 1) return 'bad request';
        if (in_array($request->input('inwehub_user_device','web'),['web','wechat']) || str_contains($article->content_url, 'wechat_redirect') || str_contains($article->content_url, '__biz=')) {
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