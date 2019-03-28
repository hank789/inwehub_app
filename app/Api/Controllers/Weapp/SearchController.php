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
        $word = $request->input('search_word');
        if ($word == '点评送咖啡') {
            $return = $this->tagProductList($request);
        } else {
            $query = Tag::search(formatElasticSearchTitle($word));
            if (config('app.env') == 'production') {
                $query = $query->where('type',TagCategoryRel::TYPE_REVIEW)
                    ->where('status',1);
            }
            $tags = $query->orderBy('reviews', 'desc')
                ->paginate(Config::get('inwehub.api_data_page_size'));
            $data = [];
            $ids = [];
            foreach ($tags as $key=>$tag) {
                if ($key === 0 && strtolower($tag->name)!=strtolower($word)) {
                    $match = Tag::getTagByName($word);
                    if ($match) {
                        $matchRel = TagCategoryRel::select(['tag_id'])->where('tag_id',$match->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
                        if ($matchRel) {
                            $ids[$match->id] = $match->id;
                            $info = Tag::getReviewInfo($match->id);
                            $data[] = [
                                'id' => $match->id,
                                'name' => $match->name,
                                'logo' => $match->logo,
                                'review_count' => $info['review_count'],
                                'review_average_rate' => $info['review_average_rate']
                            ];
                        }
                    }
                }
                if (isset($ids[$tag->id])) continue;
                $ids[$tag->id] = $tag->id;
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
        }

        $this->searchNotify($oauth,$request->input('search_word'),'在栏目[产品]',',搜索结果'.($return['total']??$return['to']));
        return self::createJsonData(true, $return);
    }

    public function getCommonTagProduct() {
        $p = rand(1,4);
        $product = [];
        switch ($p) {
            case 1:
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
                break;
            case 2:
                $products = TagCategoryRel::select(['tag_id'])->where('type',TagCategoryRel::TYPE_REVIEW)
                    ->where('status',1)->orderBy('updated_at','desc')->distinct()->groupBy('tag_id')->take(8)->get();
                foreach ($products as $t) {
                    $model = Tag::find($t->tag_id);
                    $product[] = $model->name;
                }
                break;
            case 3:
                $products = TagCategoryRel::select(['tag_id'])->where('type',TagCategoryRel::TYPE_REVIEW)
                    ->where('status',1)->orderBy('review_average_rate','desc')->distinct()->groupBy('tag_id')->take(8)->get();
                foreach ($products as $t) {
                    $model = Tag::find($t->tag_id);
                    $product[] = $model->name;
                }
                break;
            case 4:
                $products = TagCategoryRel::select(['tag_id'])->where('type',TagCategoryRel::TYPE_REVIEW)
                    ->where('status',1)->orderBy('reviews','desc')->distinct()->groupBy('tag_id')->take(8)->get();
                foreach ($products as $t) {
                    $model = Tag::find($t->tag_id);
                    $product[] = $model->name;
                }
                break;
        }

        return self::createJsonData(true,['words'=>$product]);
    }

    public function msgCallback(Request $request) {
        $signature = $request->input('signature');
        $timestamp = $request->input('timestamp');
        $nonce = $request->input('nonce');
        $echostr = $request->input('echostr');
        \Log::info('test',$request->all());
        $token = 'dianping2019inwehub';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return $echostr;
        }else{
            return '';
        }
    }

}