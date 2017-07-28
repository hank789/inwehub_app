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
 * @mixin \Eloquent
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
