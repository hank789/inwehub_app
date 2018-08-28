<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Collection;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午5:29
 * @email: wanghui@yonglibao.com
 */

class HomeController extends Controller {

    /**
     * Returns the submissions for the homepage of Auth user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function feed(Request $request)
    {
        $this->validate($request, [
            'sort' => 'required|in:hot,new,rising',
            'page' => 'required|integer',
        ]);

        $user = $request->user();
        $submissions = Submission::where('public',1);
        $type = $request->input('type',1);
        switch ($type){
            case 2:
                //文章链接
                $submissions->where('type','link');
                break;
            case 3:
                //纯图文
                $submissions->where('type','text');
                break;
        }
        $uuid = $request->input('uuid');
        if ($uuid) {
            $search_user = User::where('uuid',$uuid)->first();
            if (!$search_user) throw new ApiException(ApiException::BAD_REQUEST);
            $submissions->where('user_id',$search_user->id);
        }

        // spicify the filter:
        if ($request->filter == 'all-channels') {
            // guest what? we don't have to do anything :|
        }

        if ($request->sort == 'new') {
            $submissions->orderBy('created_at', 'desc');
        }

        if ($request->sort == 'rising') {
            $submissions->where('created_at', '>=', Carbon::now()->subHour())
                ->orderBy('rate', 'desc');
        }

        if ($request->sort == 'hot') {
            $submissions->orderBy('rate', 'desc');
        }

        $submissions = $submissions->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission['id'])
                ->where('supportable_type',Submission::class)
                ->exists();
            $bookmark = Collection::where('user_id',$user->id)
                ->where('source_id',$submission['id'])
                ->where('source_type',Submission::class)
                ->exists();
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $group = Group::find($submission->group_id);
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            $item['group']= $group->toArray();
            $item['group']['is_joined'] = $is_joined;
            $item['group']['subscribers'] = $group->getHotIndex();
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    public function userArticle(Request $request){
        $uuid = $request->input('uuid');
        $search_user = User::where('uuid',$uuid)->first();
        if (!$search_user) throw new ApiException(ApiException::BAD_REQUEST);
        $submissions = (new Submission())->newQuery();
        $submissions = $submissions->where('author_id',$search_user->id)
            ->whereIn('type',['link','article'])
            ->orderBy('rate', 'desc')
            ->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

}