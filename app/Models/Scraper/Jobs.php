<?php namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:02
 * @email: wanghui@yonglibao.com
 */

/**
 * Class Feeds
 *
 * @package App\Models\Inwehub
 * @mixin \Eloquent
 */
class Jobs extends Model {

    protected $table = 'scraper_jobs';

    protected $fillable = ['positionName', 'positionId', 'city', 'salary', 'companyId',
        'companyLogo', 'companyName', 'companyFullName'];

}