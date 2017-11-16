<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 */
class RecommendRead extends Model
{
    protected $table = 'recommend_read';
    protected $fillable = ['read_type','audit_status','data','source_type','source_id','sort'];

    protected $casts = [
        'data' => 'json'
    ];

    const READ_TYPE_SUBMISSION = 1;
    const READ_TYPE_PAY_QUESTION = 2;
    const READ_TYPE_FREE_QUESTION = 3;
    const READ_TYPE_ACTIVITY = 4;//活动
    const READ_TYPE_PROJECT_OPPORTUNITY = 5;//项目机遇



    public function getReadTypeName() {
        switch ($this->read_type) {
            case self::READ_TYPE_SUBMISSION:
                return '发现分享';
            case self::READ_TYPE_PAY_QUESTION:
                return '专业问答';
            case self::READ_TYPE_FREE_QUESTION:
                return '互动问答';
            case self::READ_TYPE_ACTIVITY:
                return '活动';
            case self::READ_TYPE_PROJECT_OPPORTUNITY:
                return '项目机遇';
        }
        return '';
    }

    public function getWebUrl() {
        switch ($this->read_type) {
            case self::READ_TYPE_SUBMISSION:
                return '发现分享';
            case self::READ_TYPE_PAY_QUESTION:
            case self::READ_TYPE_FREE_QUESTION:
                return route('ask.question.detail',['id'=>$this->source_id]);
            case self::READ_TYPE_ACTIVITY:
            case self::READ_TYPE_PROJECT_OPPORTUNITY:
                return route('blog.article.detail',['id'=>$this->source_id]);
        }
        return '';
    }

}
