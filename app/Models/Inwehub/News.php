<?php

namespace App\Models\Inwehub;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class News
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class News extends Model
{
    protected $table = 'news';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub';

    protected $fillable = ['title', 'user_id', 'url','mobile_url','topic_id','site_name','author_name','status'];

    public static function boot()
    {
        parent::boot();

        /*监听创建*/
        static::creating(function($comment){

        });

        /*监听删除事件*/
        static::deleting(function($comment){

        });
    }

    public function toTopic(){
        return $this->belongsTo('App\Models\Inwehub\Topic','to_topic_id');
    }


}
