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
class Feeds extends Model {

    protected $table = 'scraper_feeds';

    protected $fillable = ['name', 'source_type', 'source_link', 'status', 'group_id'];

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}