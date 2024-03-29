<?php

namespace App\Models;

use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BosonNLPService;
use App\Services\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use QL\QueryList;

/**
 * App\Models\Recommendation
 *
 * @property int $id
 * @mixin \Eloquent
 * @property string $subject
 * @property string $user_name
 * @property string $user_avatar_url
 * @property int $price
 * @property int $type
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUserAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUserName($value)
 * @property int $read_type 分类
 * @property int $source_id
 * @property string $source_type
 * @property array $data
 * @property int|null $audit_status 审核状态 0-未审核 1-已审核 2-未通过
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendRead whereAuditStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendRead whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendRead whereReadType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendRead whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendRead whereSourceType($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 */
class RecommendRead extends Model
{
    use MorphManyTagsTrait;
    protected $table = 'recommend_read';
    protected $fillable = ['read_type','audit_status','data','source_type','source_id','sort', 'rate', 'tips','created_at','updated_at'];

    protected $casts = [
        'data' => 'json'
    ];

    const READ_TYPE_SUBMISSION = 1;
    const READ_TYPE_PAY_QUESTION = 2;
    const READ_TYPE_FREE_QUESTION = 3;
    const READ_TYPE_ACTIVITY = 4;//活动
    const READ_TYPE_PROJECT_OPPORTUNITY = 5;//项目机遇
    const READ_TYPE_FREE_QUESTION_ANSWER = 6;//互动问答回复



    public function getReadTypeName() {
        switch ($this->read_type) {
            case self::READ_TYPE_SUBMISSION:
                return '发现分享';
            case self::READ_TYPE_PAY_QUESTION:
                return '问答';
            case self::READ_TYPE_FREE_QUESTION:
                return '问答';
            case self::READ_TYPE_ACTIVITY:
                return '活动';
            case self::READ_TYPE_PROJECT_OPPORTUNITY:
                return '项目机遇';
            case self::READ_TYPE_FREE_QUESTION_ANSWER:
                return '问答回复';
        }
        return '';
    }

    public function getWebUrl() {
        switch ($this->read_type) {
            case self::READ_TYPE_SUBMISSION:
                return config('app.mobile_url').'#/c/'.$this->data['category_id'].'/'.$this->data['slug'];
            case self::READ_TYPE_PAY_QUESTION:
            case self::READ_TYPE_FREE_QUESTION:
            case self::READ_TYPE_FREE_QUESTION_ANSWER:
                return route('ask.question.detail',['id'=>$this->source_id]);
            case self::READ_TYPE_ACTIVITY:
            case self::READ_TYPE_PROJECT_OPPORTUNITY:
                return route('blog.article.detail',['id'=>$this->source_id]);
        }
        return '';
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function getRateWeight() {
        $weight = RateLimiter::instance()->hGet('recommend-rate-weight',$this->id);
        return $weight?:0;
    }

    public function setRateWeight($value) {
        return RateLimiter::instance()->hSet('recommend-rate-weight',$this->id,$value);
    }

    //设置关键词标签
    public function setKeywordTags() {
        $source = $this->source;
        $tags = $source->tags()->pluck('tag_id')->toArray();
        Tag::multiAddByIds($tags,$this);
    }

    public function checkSourceLink() {
        if ($this->data['type'] == 'link') {
            if ($this->data['domain'] == 'mp.weixin.qq.com') {
                $source = $this->source;
                if (str_contains($source->data['url'],'wechat_redirect') || str_contains($source->data['url'],'__biz=') || str_contains($source->data['url'],'/s/')) {
                    return true;
                }
                return $source->data['url'];
            }
        }
        return true;
    }

}
