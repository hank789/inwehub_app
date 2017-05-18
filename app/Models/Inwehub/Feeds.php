<?php namespace App\Models\Inwehub;

use Illuminate\Database\Eloquent\Model;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:02
 * @email: wanghui@yonglibao.com
 */

/**
 * Class Feeds
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class Feeds extends Model {

    protected $table = 'feeds';
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';


    protected $fillable = ['name', 'user_id', 'description','source_type','source_link','status'];

}