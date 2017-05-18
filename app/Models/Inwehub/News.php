<?php

namespace App\Models\Inwehub;

use Illuminate\Database\Eloquent\Model;

/**
 * Class News
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class News extends Model
{
    protected $table = 'news_info';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $primaryKey = '_id';

    protected $fillable = ['title', 'description','date_time' ,'content_url','mobile_url','topic_id','site_name','author','status','source_type'];

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
