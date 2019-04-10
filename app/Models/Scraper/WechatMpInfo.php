<?php namespace App\Models\Scraper;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:02
 * @email: hank.huiwang@gmail.com
 */

/**
 * Class Feeds
 *
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class WechatMpInfo extends Model {
    use BelongsToUserTrait, MorphManyTagsTrait;

    protected $table = 'scraper_wechat_mp_info';
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $primaryKey = '_id';

    public $timestamps = false;

    protected $fillable = ['name','wx_hao','newrank_id','is_auto_publish','company','description','wz_url','last_qunfa_id','create_time','status','logo_url','qr_url','group_id','user_id','rank_article_release_count'];


    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

    public function countTodayArticle() {
        return WechatWenzhangInfo::where('mp_id',$this->_id)->where('source_type',1)->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])->count();
    }

    public function countTotalArticle() {
        return WechatWenzhangInfo::where('mp_id',$this->_id)->where('source_type',1)->count();
    }

}