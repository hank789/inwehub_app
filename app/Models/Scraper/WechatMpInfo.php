<?php namespace App\Models\Scraper;

use App\Models\Relations\BelongsToUserTrait;
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
    use BelongsToUserTrait;

    protected $table = 'scraper_wechat_mp_info';
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $primaryKey = '_id';

    public $timestamps = false;

    protected $fillable = ['name','wx_hao','company','description','wz_url','last_qunfa_id','create_time','status','logo_url','qr_url','group_id','user_id'];


    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}