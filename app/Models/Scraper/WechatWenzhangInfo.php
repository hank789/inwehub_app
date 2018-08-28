<?php namespace App\Models\Scraper;

use App\Models\RecommendRead;
use App\Models\Submission;
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

    public $timestamps = true;

    protected $fillable = ['title','content_url','topic_id','mp_id','author','site_name','mobile_url','date_time','source_type','description','body','cover_url','status'];

    public function withAuthor(){
        return WechatMpInfo::find($this->mp_id);
    }

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

}