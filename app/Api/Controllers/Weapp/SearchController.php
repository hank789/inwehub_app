<?php
/**
 * @author: wanghui
 * @date: 2018/11/26 下午2:08
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\ImportantNotify;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

class SearchController extends controller
{
    protected function searchNotify($oauth,$searchWord,$typeName='',$searchResult=''){
        event(new ImportantNotify('小程序用户'.$oauth->user_id.'['.$oauth->nickname.']'.$typeName.'搜索['.$searchWord.']'.$searchResult));
        RateLimiter::instance()->hIncrBy('search-word-count',$searchWord,1);
        RateLimiter::instance()->hIncrBy('search-user-count-'.$oauth->user_id,$searchWord,1);
    }

    public function tagProduct(Request $request,JWTAuth $JWTAuth) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        $query = Tag::search(formatElasticSearchTitle($request->input('search_word')));
        if (config('app.env') == 'production') {
            $query = $query->where('type',TagCategoryRel::TYPE_REVIEW)
                ->where('status',1);
        }
        $tags = $query->orderBy('reviews', 'desc')
            ->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($tags as $tag) {
            $info = Tag::getReviewInfo($tag->id);
            $data[] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'logo' => $tag->logo,
                'review_count' => $info['review_count'],
                'review_average_rate' => $info['review_average_rate']
            ];
        }
        $return = $tags->toArray();
        $return['data'] = $data;
        $this->searchNotify($oauth,$request->input('search_word'),'在栏目[产品]',',搜索结果'.$tags->total());
        return self::createJsonData(true, $return);
    }

    public function getCommonTagProduct() {
        $product = [
            'CRM',
            '钉钉',
            '阿里云',
            'SAP',
            '金蝶',
            '北森云',
            'GitHub',
            'OutLook'
        ];
        return self::createJsonData(true,['words'=>$product]);
    }


}