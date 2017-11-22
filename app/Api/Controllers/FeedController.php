<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/10/26 下午4:43
 * @email: wanghui@yonglibao.com
 */

use App\Models\Feed\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FeedController extends Controller
{

    public function index(Request $request) {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = Feed::where('audit_status', Feed::AUDIT_STATUS_SUCCESS);

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $feeds = $query->orderBy('id','desc')
            ->simplePaginate(Config::get('api_data_page_size'));
        $return = [];
        foreach ($feeds as $feed) {
            $sourceData = $feed->getSourceFeedData();
            if (empty($sourceData)) continue;
            $return[] = [
                'id' => $feed->id,
                'title' => $feed->data['feed_content'],
                'user'  => [
                    'id'    => $feed->is_anonymous ? 0 : $feed->user->id ,
                    'uuid'  => $feed->is_anonymous ? '' : $feed->user->uuid,
                    'name'  => $feed->is_anonymous ? '匿名': $feed->user->name,
                    'is_expert' => $feed->is_anonymous ? 0 : $feed->user->userData->authentication_status == 1 ? 1 : 0,
                    'avatar'=> $feed->is_anonymous ? config('image.user_default_avatar'):$feed->user->avatar
                ],
                'feed'  => $sourceData['feed'],
                'url'   => $sourceData['url'],
                'feed_type'  => $feed->feed_type,
                'created_at' => (string)$feed->created_at
            ];
        }

        return self::createJsonData(true,$return);
    }

}