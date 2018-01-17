<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Models\Collection;
use App\Models\Submission;
use App\Models\Support;
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
        $submissions = (new Submission())->newQuery();

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
            $item['tags'] = $submission->tags()->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

}