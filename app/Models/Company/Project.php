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

    const STATUS_DRAFT = 0;
    const STATUS_PENDING = 1;
    const STATUS_PUBLISH = 2;
    const STATUS_REJECT = 3;

}
