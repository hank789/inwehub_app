<?php

namespace App\Models\Groups;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Groups\GroupMember
 *
 * @property int $id
 * @property int $parent_id
 * @property int $grade
 * @property string $name
 * @property string $icon
 * @property string $slug
 * @property string $type
 * @property int $sort
 * @property string $role_id
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Authentication[] $experts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereGrade($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereIcon($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereRoleId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GroupMember extends Model
{
    use BelongsToUserTrait;

    protected $table = 'group_members';
    protected $fillable = ['user_id','group_id','audit_status'];

    const AUDIT_STATUS_DRAFT = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_REJECT = 2;

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }
}
