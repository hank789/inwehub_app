<?php namespace App\Models\Scraper;

use App\Models\Groups\Group;
use App\Models\RecommendRead;
use App\Models\Submission;
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
class BidInfo extends Model {

    protected $table = 'scraper_bid_info';

    public $timestamps = true;

    protected $fillable = ['guid','source_url','topic_id','title','projectname',
        'projectcode','buyer','toptype','subtype','area','budget','bidamount', 'source_domain',
        'bidopentime','industry','s_subscopeclass','winner','detail','publishtime','topic_id','status'];

    //status状态：1待发布，2已发布，3已删除

    protected $casts = [
        'detail' => 'json'
    ];

    public function submission() {
        if ($this->topic_id > 0) {
            return Submission::find($this->topic_id);
        }
        return null;
    }

    public function isRecommendRead() {
        if ($this->topic_id > 0) {
            $recommendRead = RecommendRead::where('source_id',$this->topic_id)->where('source_type',Submission::class)->first();
            if ($recommendRead) {
                return true;
            }
        }
        return false;
    }

    public function getGroup() {
        $groupId = $this->detail['group_ids']??'';
        if ($groupId) {
            return Group::find($groupId[0]);
        }
        return null;
    }

}