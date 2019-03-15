<?php namespace App\Models\Scraper;

use App\Models\ContentCollection;
use App\Models\RecommendRead;
use App\Models\Relations\MorphManyTagsTrait;
use App\Models\Submission;
use App\Models\Tag;
use App\Services\RateLimiter;
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
class WechatWenzhangInfo extends Model {

    use MorphManyTagsTrait;

    protected $table = 'scraper_news_info';

    protected $primaryKey = '_id';

    public $timestamps = true;

    protected $fillable = ['title','content_url','source_url','topic_id',
        'qunfa_id','mp_id','author','site_name','mobile_url','date_time',
        'read_count','like_count','comment_count','site_name',
        'author','msg_index','copyright_stat','type',
        'source_type','description','body','cover_url','status'];

    //status状态：1待发布，2已发布，3已删除

    const TYPE_TAG_NEWS = 1;//产品资讯
    const TYPE_TAG_CASE = 2;//产品案例

    public function withAuthor(){
        if ($this->source_type == 1) {
            return WechatMpInfo::find($this->mp_id);
        } else {
            return Feeds::find($this->mp_id);
        }
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

    public function addProductTag() {
        $tag_ids = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('status',1)
            ->where('sort',$this->mp_id)
            ->pluck('source_id')->toArray();
        if ($tag_ids) {
            Tag::multiAddByIds($tag_ids,$this);
            if (empty($this->cover_url)) {
                $info = getUrlInfo($this->content_url,true, 'submissions', false);
                $img_url = $info['img_url'];
            } else {
                $parse_url = parse_url($this->cover_url);
                $img_url = $this->cover_url;
                //非本地地址，存储到本地
                if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                    $img_url = saveImgToCdn($this->cover_url,'submissions', false, false);
                }
            }
            $this->cover_url = $img_url;
            $this->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
            $this->save();
            //更新产品信息缓存
            foreach ($tag_ids as $id) {
                RateLimiter::instance()->hSet('product_pending_update_cache',$id,$id);
            }
        }
    }

}