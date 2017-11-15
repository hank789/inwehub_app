<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Models\Readhub\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午4:39
 * @email: wanghui@yonglibao.com
 */

class BookmarksController extends Controller {

    /**
     * Favorited submissions by Auth user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function bookmarkSubmission(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $submission = Submission::findOrFail($request->id);
        $user = $request->user();
        $type = $submission->bookmark($user->id);

        return self::createJsonData(true,['type'=>$type]);
    }

}