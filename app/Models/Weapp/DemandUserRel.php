<?php namespace App\Models\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;


class DemandUserRel extends Model
{
    use BelongsToUserTrait;
    protected $table = 'demand_user_rel';
    protected $fillable = ['demand_id', 'user_id'];

}