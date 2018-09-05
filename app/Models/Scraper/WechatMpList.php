<?php namespace App\Models\Scraper;

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
class WechatMpList extends Model {

    protected $table = 'scraper_wechat_add_mp_list';

    protected $primaryKey = '_id';

    protected $fillable = ['name', 'wx_hao'];

}