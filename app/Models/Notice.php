<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notice
 *
 * @property int $id
 * @property string $subject
 * @property string $url
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereUrl($value)
 * @mixin \Eloquent
 * @property string|null $img_url 图片地址
 * @property int $sort 排序
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Notice whereImgUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Notice whereSort($value)
 */
class Notice extends Model
{
    protected $table = 'notices';
    protected $fillable = ['subject', 'url','status','img_url','sort'];

    public function getUrlAttribute($value) {
        return explode(',',$value);
    }

}
