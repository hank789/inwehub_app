<?php namespace App\Models\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Category;
use App\Models\ContentCollection;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\UserOauth;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Tongji
 * @package App\Models\Weapp
 * @mixin \Eloquent
 */
class Tongji extends Model
{

    protected $table = 'weapp_tongji';
    protected $fillable = ['user_oauth_id', 'page', 'start_time', 'end_time', 'stay_time','event_id','scene','parent_refer','from_user_id','product_id'];

    public static $pageType = [
        'pages/index/index' => ['name'=>'首页'],
        'pages/specialDetail/specialDetail' => ['name'=>'专题集'],
        'pages/majorProduct/majorProduct' => ['name'=>'产品详情'],
        'pages/productDetail/productDetail' => ['name'=>'产品详情'],
        'pages/commentDetail/commentDetail' => ['name'=>'点评详情'],
        'pages/allDianping/allDianping' => ['name'=>'点评列表'],
        'pages/search/search' => ['name'=>'搜索页'],
        'pages/url/url' => ['name'=>'文章详情'],
        'pages/pdf/pdf' => ['name'=>'pdf案例'],
        'pages/video/video' => ['name'=>'视频案例'],
        'pages/moreInfo/moreInfo' => ['name'=>'资讯列表'],
        'pages/totalComment/totalComment' => ['name'=>'评论列表']
    ];

    public function getUserName() {
        if ($this->user_oauth_id) {
            $oauth = UserOauth::find($this->user_oauth_id);
            return $oauth->nickname;
        }
        return '游客';
    }

    public function getPageName() {
        return isset(self::$pageType[$this->page])?self::$pageType[$this->page]['name']:'';
    }

    public function getPageObject() {
        if (empty($this->event_id)) return '';
        switch ($this->page) {
            case 'pages/totalComment/totalComment':
            case 'pages/specialDetail/specialDetail':
                $c = Category::find($this->event_id);
                return $c->name;
                break;
            case 'pages/productDetail/productDetail':
            case 'pages/allDianping/allDianping':
            case 'pages/majorProduct/majorProduct':
                $tag = Tag::find($this->event_id);
                if (empty($this->product_id)) {
                    $this->product_id = $this->event_id;
                    $this->save();
                }
                return $tag->name;
                break;
            case 'pages/url/url':
                $article = WechatWenzhangInfo::find($this->event_id);
                if ($this->parent_refer && empty($this->product_id)) {
                    $parent_refer_arr = explode('_',$this->parent_refer);
                    if (isset($parent_refer_arr[0]) && $parent_refer_arr[0] == 'product') {
                        $this->product_id = $parent_refer_arr[1];
                        $this->save();
                    }
                }
                return $article->title;
                break;
            case 'pages/moreInfo/moreInfo':
                if ($this->parent_refer == 'album') {
                    $c = Category::find($this->event_id);
                    return $c->name;
                } else {
                    $tag = Tag::find($this->event_id);
                    if (empty($this->product_id)) {
                        $this->product_id = $this->event_id;
                        $this->save();
                    }
                    return $tag->name;
                }
                break;
            case 'pages/commentDetail/commentDetail':
                $review = Submission::find($this->event_id);
                if (empty($this->product_id)) {
                    $this->product_id = $review->category_id;
                    $this->save();
                }
                return str_limit($review->title);
                break;
            case 'pages/video/video':
            case 'pages/pdf/pdf':
                $case = ContentCollection::find($this->event_id);
                if (empty($this->product_id)) {
                    $this->product_id = $case->source_id;
                    $this->save();
                }
                return $case->content['title'];
                break;
        }
        return '';
    }

}