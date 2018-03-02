<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/10/26 下午4:43
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Models\Feed\Feed;
use App\Models\Tag;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FeedController extends Controller
{

    public function index(Request $request) {
        $search_type = $request->input('search_type',2);
        $user = $request->user();
        $query = Feed::query();
        switch ($search_type) {
            case 1:
                //关注
                $followers = $user->attentions()->where('source_type', '=', get_class($user))->pluck('source_id')->toArray();
                $attentionTags = $user->attentions()->where('source_type', '=', Tag::class)->pluck('source_id')->toArray();
                $query = $query->whereIn('user_id', $followers)->where('feed_type', '!=', Feed::FEED_TYPE_FOLLOW_USER);
                if ($attentionTags) {
                    $query = $query->orWhere(function ($query) use ($attentionTags) {
                        foreach ($attentionTags as $attentionTag) {
                            $query->orWhereRaw("locate('[" . $attentionTag . "]',tags)>0");
                        }
                    });
                }
                break;
            case 2:
                //全部
                $query = $query->where('feed_type', '!=', Feed::FEED_TYPE_FOLLOW_USER);
                break;
            case 3:
                //问答
                $query = $query->whereIn('feed_type', [
                    Feed::FEED_TYPE_ANSWER_PAY_QUESTION,
                    Feed::FEED_TYPE_ANSWER_FREE_QUESTION,
                    Feed::FEED_TYPE_CREATE_FREE_QUESTION,
                    Feed::FEED_TYPE_CREATE_PAY_QUESTION,
                    Feed::FEED_TYPE_FOLLOW_FREE_QUESTION,
                    Feed::FEED_TYPE_COMMENT_PAY_QUESTION,
                    Feed::FEED_TYPE_COMMENT_FREE_QUESTION,
                    Feed::FEED_TYPE_UPVOTE_PAY_QUESTION,
                    Feed::FEED_TYPE_UPVOTE_FREE_QUESTION
                ]);
                break;
            case 4:
                //分享
                $query = $query->whereIn('feed_type', [
                    Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE,
                    Feed::FEED_TYPE_COMMENT_READHUB_ARTICLE,
                    Feed::FEED_TYPE_UPVOTE_READHUB_ARTICLE
                ]);
                break;
            case 5:
                //他的动态
                $search_user = User::where('uuid', $request->input('uuid'))->first();
                if (!$search_user) throw new ApiException(ApiException::BAD_REQUEST);
                $query = $query->where('user_id', $search_user->id)->where('feed_type', '!=', Feed::FEED_TYPE_FOLLOW_USER);
                break;
        }

        $feeds = $query->distinct()->orderBy('top', 'desc')->latest()
            ->simplePaginate(Config::get('inwehub.api_data_page_size'));

        $return = $feeds->toArray();
        $data = [];
        foreach ($feeds as $feed) {
            $sourceData = $feed->getSourceFeedData();
            if (empty($sourceData)) continue;
            $data[] = [
                'id' => $feed->id,
                'title' => $feed->data['feed_content'],
                'top' => $feed->top,
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
        $return['data'] = $data;
        $return['per_page'] = count($data);

        return self::createJsonData(true,$return);
    }

}