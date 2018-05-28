<?php namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:02
 * @email: wanghui@yonglibao.com
 */

/**
 * Class Feeds
 *
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class WechatMpInfo extends Model {

    protected $table = 'scraper_wechat_mp_info';
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $primaryKey = '_id';

    public $timestamps = false;

    protected $fillable = ['status','logo_url','qr_url','group_id'];


    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}