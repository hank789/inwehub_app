<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $project_name 项目名称
 * @property int $project_type 项目类型
 * @property int $project_stage 项目阶段
 * @property string $project_description 项目简介
 * @property int $status 状态:0,待发布,1已发布,2被拒绝
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Company\ProjectDetail $detailInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Project onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereProjectDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereProjectStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereProjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Project whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Project withoutTrashed()
 */
class Project extends Model implements HasMedia
{
    use BelongsToUserTrait,SoftDeletes,HasMediaTrait;
    protected $table = 'projects';
    protected $fillable = ['user_id', 'project_name','project_type','project_stage','project_description','status'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    //项目状态
    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_PUBLISH = 2;
    const STATUS_REJECT = 3;

    //项目类型
    const PROJECT_TYPE_ONCE = 1;//一次性
    const PROJECT_TYPE_CONTINUED = 2;//持续性

    //项目阶段
    const PROJECT_STAGE_1 = 1;//只有个想法
    const PROJECT_STAGE_2 = 2;//已立项
    const PROJECT_STAGE_3 = 3;//进行中



    public static function boot()
    {
        parent::boot();
        static::deleted(function($project){
            $project->detailInfo()->delete();
        });
    }

    public function detailInfo()
    {
        return $this->hasOne('App\Models\Company\ProjectDetail');
    }
}
