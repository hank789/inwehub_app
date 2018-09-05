<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/31 下午7:57
 * @email: hank.huiwang@gmail.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadHubUser
 *
 * @package App\Models\Readhub
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $language
 * @property string $description
 * @property int $nsfw
 * @property string $color
 * @property string $avatar
 * @property int $public
 * @property int $active
 * @property int $subscribers
 * @property mixed $settings
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereNsfw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereSubscribers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Category whereUpdatedAt($value)
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