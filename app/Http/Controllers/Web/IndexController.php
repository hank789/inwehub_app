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
        $article = WechatWenzhangInfo::find($id);
        if ($article->source_type != 1) return 'bad request';
        if (in_array($request->input('inwehub_user_device','web'),['web','wechat']) || str_contains($article->content_url, 'wechat_redirect') || str_contains($article->content_url, '__biz=')) {
            return redirect($article->content_url);
        }
        return view('h5::article')->with('article',$article);
    }
}