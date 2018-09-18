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
class Feeds extends Model {
    use BelongsToUserTrait;

    protected $table = 'scraper_feeds';

    protected $fillable = ['name', 'source_type', 'is_auto_publish','keywords', 'source_link', 'status', 'group_id', 'user_id'];

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}