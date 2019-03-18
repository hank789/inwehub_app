<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 */
class ContentCollection extends Model
{
    protected $table = 'content_collection';
    protected $fillable = ['content_type','sort','source_id','content','status'];

    protected $casts = [
        'content' => 'json',
    ];

    const CONTENT_TYPE_TAG_EXPERT_IDEA = 1;//专辑产品专家观点
    const CONTENT_TYPE_TAG_SHOW_CASE = 2;//专辑产品成功案例
    const CONTENT_TYPE_TAG_WECHAT_GZH = 3;//专辑产品和公众号关联
    const CONTENT_TYPE_HOT_ALBUM = 4;//热门专题


}
