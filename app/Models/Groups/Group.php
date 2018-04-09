<?php

namespace App\Models\Groups;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Groups\Group
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
class Group extends Model
{
    use SoftDeletes,BelongsToUserTrait;

    protected $table = 'groups';
    protected $fillable = ['user_id','name','description','logo','public','audit_status','subscribers', 'articles','failed_reason'];


    const AUDIT_STATUS_DRAFT = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_REJECT = 2;

    public function members() {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public static function search($word)
    {
        $list = self::where('name','like',"%$word%");
        return $list;
    }

}
