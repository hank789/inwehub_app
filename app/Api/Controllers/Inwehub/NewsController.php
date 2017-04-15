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
            $query->where('id','<',$lastCursor);
        }
        $articles = $query->orderBy('id','desc')->paginate($pageSize);
        $list = [];
        foreach($articles as $article){
            $item = [];
            $item['id'] = $article->id;
            $item['order'] = $article->id;
            $item['title'] = $article->title;
            $item['summary'] = $article->title;
            $item['publishDate'] = date('Y-m-d H:i:s',strtotime($article->publish_date));
            $item['url']=$article->url;
            $item['siteName']=$article->site_name;
            $item['authorName']=$article->author_name;

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
            $count = $query->where('id','>',$latestCursor)->count();
        }
        $data = [
            'count' => $count
        ];
        return response()->json($data);
    }
}