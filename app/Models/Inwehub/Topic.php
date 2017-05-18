<?php

namespace App\Models\Inwehub;

use App\Models\Relations\BelongsToCategoryTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * Class Topic
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class Topic extends Model
{
    protected $table = 'topic';
    protected $fillable = ['title', 'user_id','summary','status'];
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    public static function boot()
    {
        parent::boot();

        /*监听创建*/
        static::creating(function($article){

        });

        static::saved(function($article){

        });
        /*监听删除事件*/
        static::deleting(function($article){


        });

        static::deleted(function($article){

        });
    }


    /*搜索*/
    public static function search($word,$size=16)
    {
        $list = self::where('title','like',"$word%")->paginate($size);
        return $list;
    }

    /*最新问题*/
    public static function newest($pageSize=20)
    {
        $query = self::query();
        $list = $query->where('status','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    public function withUser(){
        return User::find($this->user_id);
    }

    public function newsCount(){
        return News::where('topic_id',$this->id)->count();
    }




}
