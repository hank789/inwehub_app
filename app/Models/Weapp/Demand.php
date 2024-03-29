<?php namespace App\Models\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: hank.huiwang@gmail.com
 */
use App\Models\IM\Room;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

/**
 * Class Demand
 *
 * @package App\Models\Weapp
 * @property int $id
 * @property int $user_id
 * @property string $title 标题
 * @property string $address 地点
 * @property float $salary 薪资
 * @property string $industry 行业
 * @property int $project_cycle 项目周期
 * @property string $project_begin_time 项目开始时间
 * @property string $description 需求描述
 * @property string $expired_at 过期时间
 * @property int $views 查看次数
 * @property int $status 状态:0,待发布,1已发布,2被拒绝,3已关闭，4已过期
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereProjectBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereProjectCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereViews($value)
 * @mixin \Eloquent
 * @property int $salary_type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereSalaryType($value)
 */
class Demand extends Model implements HasMedia
{
    use BelongsToUserTrait,HasMediaTrait;
    protected $table = 'demand';
    protected $fillable = ['title', 'user_id', 'salary', 'salary_upper', 'salary_type','industry', 'project_cycle', 'project_begin_time', 'description', 'expired_at', 'views','address', 'status'];

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_REJECT = 2;
    const STATUS_CLOSED = 3;

    protected $casts = [
        'address' => 'json'
    ];

    public static function boot()
    {
        parent::boot();
        static::deleted(function($demand){
            DemandUserRel::where('demand_id',$demand->id)->delete();
            Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->delete();
        });
    }


    public function getIndustryName(){
        $tag = Tag::find($this->industry);
        return $tag?$tag->name:'';
    }

    public function getRoomCount(){
        return Room::where('source_id',$this->id)->where('source_type',Demand::class)->count();
    }

    public function getSubscribeCount() {
        return DemandUserRel::where('demand_id',$this->id)->whereNotNull('subscribes')->count();
    }

}