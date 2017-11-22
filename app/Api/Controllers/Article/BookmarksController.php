<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Collection;
use App\Models\Submission;
use Illuminate\Http\Request;

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

        $submission = Submission::find($request->id);
        if (!$submission) {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $user = $request->user();
        /*不能多次收藏*/
        $userCollect = $user->isCollected(get_class($submission),$submission->id);
        if($userCollect){
            $userCollect->delete();
            $submission->decrement('collections');
            return self::createJsonData(true,['tip'=>'取消收藏成功','type'=>'unbookmarked'],ApiException::SUCCESS,'取消收藏成功');
        }

        $data = [
            'user_id'     => $user->id,
            'source_id'   => $submission->id,
            'source_type' => get_class($submission),
            'subject'  => '',
        ];

        $collect = Collection::create($data);

        if($collect){
            $submission->increment('collections');
        }

        return self::createJsonData(true,['tip'=>'收藏成功', 'type'=>'bookmarked'],ApiException::SUCCESS,'收藏成功');
    }

}