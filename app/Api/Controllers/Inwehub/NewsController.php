<?php namespace App\Api\Controllers\Inwehub;
/**
 * @author: wanghui
 * @date: 2017/4/15 下午7:02
 * @email: wanghui@yonglibao.com
 */

use App\Api\Controllers\Controller;
use App\Models\Inwehub\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request){
        $pageSize = $request->input('pageSize',10);
        $lastCursor = $request->input('lastCursor',0);
        $lastCursor = is_numeric($lastCursor) ? $lastCursor :0;
        $query = News::query();
        if($lastCursor){
            $query->where('_id','<',$lastCursor);
        }
        $articles = $query->orderBy('_id','desc')->paginate($pageSize);
        $list = [];
        foreach($articles as $article){
            $item = [];
            $item['id'] = $article->_id;
            $item['order'] = $article->_id;
            $item['title'] = $article->title;
            $item['summary'] = $article->description;
            $item['publishDate'] = date('Y-m-d H:i:s',strtotime($article->date_time));
            $item['url']=$article->content_url;
            $item['siteName']=$article->mp_id ? '微信公众号':$article->site_name;
            $item['authorName']=$article->author;

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
            $query = News::query();
            $count = $query->where('_id','>',$latestCursor)->count();
        }
        $data = [
            'count' => $count
        ];
        return response()->json($data);
    }
}