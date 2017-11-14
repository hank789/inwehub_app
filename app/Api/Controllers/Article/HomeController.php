<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Models\Readhub\Submission;
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

        $return = $submissions->simplePaginate(Config::get('api_data_page_size'));
        return self::createJsonData(true, $return->toArray());
    }

}