<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/31 下午7:57
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadHubUser
 * @package App\Models\Readhub
 * @mixin \Eloquent
 */
class Category extends Model {

    protected $table = 'categories';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';


}