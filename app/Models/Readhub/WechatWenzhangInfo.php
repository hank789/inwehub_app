<?php namespace App\Models\Readhub;

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
class WechatWenzhangInfo extends Model {

    protected $table = 'news_info';
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $primaryKey = '_id';

    public $timestamps = false;

    public function withAuthor(){
        return WechatMpInfo::find($this->mp_id);
    }

}