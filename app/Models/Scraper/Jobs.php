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
class Jobs extends Model {

    protected $table = 'scraper_jobs';

    protected $fillable = ['title', 'guid', 'city', 'source_url',
        'company', 'topic_id','status','summary','group_id','tags'];

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}