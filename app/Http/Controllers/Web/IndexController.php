<?php namespace App\Http\Controllers\Web;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Http\Controllers\Controller;
use App\Models\RecommendRead;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: hank.huiwang@gmail.com
 */

class IndexController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('test',[$request->url()]);
        //return view('inspinia.home.index');
        return '欢迎来到Inwehub';
    }

    public function articleInfo($id, Request $request)
    {
        \Log::info('test',[$request->url()]);
        $from_source = $request->input('inwehub_user_device','weapp_dianping');
        $miniprogram_back = '';
        $logo = '';
        if ($from_source == 'weapp_dianping') {
            $article = WechatWenzhangInfo::find($id);
            $article_source = $request->input('source','');
            if ($article_source) {
                $t = explode('_',$article_source);
                switch ($t[0]) {
                    case 'product':
                        $miniprogram_back = '/pages/majorProduct/majorProduct?id='.$t[1];
                        $tag = Tag::find($t[1]);
                        $logo = $tag->logo;
                        break;
                    case 'album':
                        $miniprogram_back = '/pages/specialDetail/specialDetail?id='.$t[1];
                        break;
                }
            }
        } elseif ($from_source == 'weapp_admin') {
            $article = WechatWenzhangInfo::find($id);
        } else {
            $article = WechatWenzhangInfo::where('topic_id',$id)->where('status',2)->first();
        }
        if (!$article) {
            $submission = Submission::find($id);
            if (!$submission) return 'bad request';
            return redirect($submission->data['url']);
        }
        if ($from_source == 'weapp_dianping') {
            event(new SystemNotify('小程序用户查看了文章:'.$article->title));
        }
        if (in_array($from_source,['web','wechat']) || $article->source_type != 1 || str_contains($article->content_url, '/s/') || str_contains($article->content_url, 'wechat_redirect') || str_contains($article->content_url, '__biz=')) {
            if ($request->input('inwehub_user_device') != 'weapp_dianping') {
                return redirect($article->content_url);
            }
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
        return view('h5::article')->with('article',$article)->with('showDate',$showDate)->with('from_source',$from_source)->with('miniprogram_back',$miniprogram_back)->with('logo',$logo);
    }

    public function trackEmail($type,$id,$uid) {
        $user = User::find($uid);
        switch ($type) {
            case 1:
                //推荐
                $recommend = RecommendRead::find($id);
                $submission = Submission::find($recommend->source_id);
                $url = 'https://www.inwehub.com/c/'.$submission->category_id.'/'.$submission->slug;
                break;
        }
        event(new ImportantNotify(formatSlackUser($user).'打开了邮件链接:'.$url));
        return redirect($url);
    }

    public function unsubscribeEmail($uid) {
        $user = User::find($uid);
        $settings = $user->notificationSettings();
        $settings->set('email_daily_subscribe',0);
        $settings->persist();
        event(new ImportantNotify(formatSlackUser($user).'用户取消了邮件订阅'));
        return '取消订阅成功';
    }

}