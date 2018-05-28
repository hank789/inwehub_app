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
class WechatWenzhangInfo extends Model {

    protected $table = 'scraper_news_info';

    protected $primaryKey = '_id';

    public $timestamps = false;

    protected $fillable = ['topic_id'];

    public function withAuthor(){
        return WechatMpInfo::find($this->mp_id);
    }

}