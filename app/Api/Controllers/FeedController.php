<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/10/26 下午4:43
 * @email: hank.huiwang@gmail.com
 */

use App\Exceptions\ApiException;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Groups\GroupMember;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
                //包括自己
                $followers[] = $user->id;
                $attentionTags = $user->attentions()->where('source_type', '=', Tag::class)->pluck('source_id')->toArray();
                $query = $query->whereIn('user_id', $followers);
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
                $followers = $user->attentions()->where('source_type', '=', get_class($user))->pluck('source_id')->toArray();
                //包括自己
                $followers[] = $user->id;
                $attentionTags = $user->attentions()->where('source_type', '=', Tag::class)->pluck('source_id')->toArray();
                $query = $query->where('public',1)->whereIn('user_id', $followers);
                $groupIds = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('group_id')->toArray();
                if ($groupIds || $attentionTags) {
                    $query = $query->orWhereIn('group_id',$groupIds)->orWhereIn('tags',$attentionTags);

                }
                $this->doing($user,Doing::ACTION_VIEW_FEED_FOLLOW,'',0,'核心页面');
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
                    Feed::FEED_TYPE_UPVOTE_FREE_QUESTION,
                    Feed::FEED_TYPE_ADOPT_ANSWER
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
                $query = $query->where('user_id', $search_user->id);
                if ($search_user->id != $user->id) {
                    $query = $query->where('public',1)->where('is_anonymous',0);
                }
                break;
            case 6:
                //推荐
                $page = $request->input('page',1);
                $attentionTags = $user->attentions()->where('source_type', '=', Tag::class)->pluck('source_id')->toArray();
                $userTags = $user->userTag->pluck('tag_id')->toArray();
                $attentionTags = array_unique(array_merge($attentionTags,$userTags));
                $query = $query->where('public',1);
                if ($attentionTags) {
                    $query = $query->orWhere(function ($query) use ($attentionTags) {
                        foreach ($attentionTags as $attentionTag) {
                            if ($attentionTag <=0) continue;
                            $query->orWhereRaw("locate('[" . $attentionTag . "]',tags)>0");
                        }
                    });
                }
                if ($page == 1) {
                    $count = $query->count();
                    $rand = Config::get('inwehub.api_data_page_size')/$count * 100;
                    $feeds = $query->where(DB::raw('RAND()'),'<=',$rand)->distinct()->orderBy(DB::raw('RAND()'))
                        ->simplePaginate(10);
                } else {
                    $feeds = $query->distinct()->latest()->simplePaginate(Config::get('inwehub.api_data_page_size'));
                }
                break;
        }
        if ($search_type == 6) {
            //推荐
        } else {
            $feeds = $query->distinct()->orderBy('top', 'desc')->latest()
                ->simplePaginate(Config::get('inwehub.api_data_page_size'));
        }

        $return = $feeds->toArray();
        $data = [];
        foreach ($feeds as $feed) {
            $sourceData = $feed->getSourceFeedData($search_type);
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
                'created_at' => $feed->created_at->diffForHumans()
            ];
        }
        $return['data'] = $data;
        $return['per_page'] = count($data);

        return self::createJsonData(true,$return);
    }


}