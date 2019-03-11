<?php namespace App\Models\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: hank.huiwang@gmail.com
 */

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
    protected $fillable = ['user_oauth_id', 'page', 'start_time', 'end_time', 'stay_time','event_id','scene'];

    public static $pageType = [
        'pages/index/index' => ['name'=>'首页'],
        'pages/specialDetail/specialDetail' => ['name'=>'专题集'],
        'pages/majorProduct/majorProduct' => ['name'=>'产品详情'],
        'pages/commentDetail/commentDetail' => ['name'=>'点评详情'],
        'pages/allDianping/allDianping' => ['name'=>'点评列表'],
        'pages/search/search' => ['name'=>'搜索页']
    ];

    public function getUserName() {
        if ($this->user_oauth_id) {
            $oauth = UserOauth::find($this->user_oauth_id);
            return $oauth->nickname;
        }
        return '游客';
    }

    public function getPageName() {
        return self::$pageType[$this->page]??'';
    }

    public function getPageObject() {
        return '';
    }

}