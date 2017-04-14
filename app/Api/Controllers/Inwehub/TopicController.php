<?php namespace App\Api\Controllers\Inwehub;
use App\Api\Controllers\Controller;
use App\Models\Inwehub\News;
use App\Models\Inwehub\Topic;
use App\Models\Inwehub\WechatWenzhangInfo;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/12 下午9:07
 * @email: wanghui@yonglibao.com
 */

class TopicController extends Controller {

    public function index(Request $request){
        $pageSize = $request->input('pageSize',10);
        $lastCursor = $request->input('lastCursor',0);
        $lastCursor = is_numeric($lastCursor) ? $lastCursor :0;
        $query = Topic::query();
        $query->where('status',1);
        if($lastCursor){
            $query->where('id','<',$lastCursor);
        }
        $articles = $query->orderBy('id','desc')->paginate($pageSize);
        $list = [];
        foreach($articles as $article){
            $item = [];
            $news = News::where('topic_id',$article->id)->get();
            $wechat_articles = WechatWenzhangInfo::where('topic_id',$article->id)->get();
            if($news->count() <=0 && $wechat_articles->count() <=0) continue;
            $item['id'] = $article->id;
            $item['title'] = $article->title;
            $item['summary'] = $article->summary;
            $item['weiboArray'] = null;
            $item['wechatArray'] = null;
            $item['relatedTopicArray'] = null;
            $item['order'] = $article->id;
            $item['publishDate'] = date('Y-m-d H:i:s',strtotime($article->publish_date));
            $item['createdAt']   = date('Y-m-d H:i:s',strtotime($article->created_at));
            $item['updatedAt']   = date('Y-m-d H:i:s',strtotime($article->updated_at));
            $newsArray = [];
            foreach($news as $val){
                $o = [];
                $o['id']=$val->id;
                $o['url']=$val->url;
                $o['title']=$val->title;
                $o['userId']=$val->user_id;
                $o['siteName']=$val->site_name;
                $o['mobileUrl']=$val->mobile_url;
                $o['authorName']=$val->author_name;
                $o['publishDate']=$val->publish_date;
                $newsArray[] = $o;
            }
            foreach($wechat_articles as $wechat_article){
                $o = [];
                $o['id']=$wechat_article->_id;
                $o['url']=$wechat_article->content_url;
                $o['title']=$wechat_article->title;
                $o['userId']=$wechat_article->mp_id;
                $o['siteName']=$wechat_article->withAuthor()->name;
                $o['mobileUrl']=$wechat_article->content_url;
                $o['authorName']=$wechat_article->author;
                $o['publishDate']=$wechat_article->date_time;
                $newsArray[] = $o;
            }
            $item['newsArray'] = $newsArray;
            $list[] = $item;
        }

        $data = [
            'totalItems'    => $articles->total(),
            'totalPages'    => $articles->lastPage(),
            'pageSize' => $pageSize+0,
            'data'    => $list,
        ];

        return response()->json($data);

    }

    public function newCount(Request $request){
        $latestCursor = $request->input('latestCursor',0);
        $latestCursor = is_numeric($latestCursor) ? $latestCursor :0;
        $count = 0;
        if($latestCursor){
            $query = Topic::query();
            $count = $query->where('id','>',$latestCursor)->count();
        }
        $data = [
            'count' => $count
        ];
        return response()->json($data);
    }

}