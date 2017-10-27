<?php namespace App\Models;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserDevice
 *
 * @author : wanghui
 * @date : 2017/5/9 下午6:00
 * @email : wanghui@yonglibao.com
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $client_id 推送设备唯一标示
 * @property string $device_token Android - 2.2+ (支持): 设备的唯一标识号，通常与clientid值一致。iOS - 4.5+ (支持): 设备的DeviceToken值，向APNS服务器发送推送消息时使用
 * @property string|null $appid 第三方推送服务的应用标识
 * @property string|null $appkey 第三方推送服务器的应用键值
 * @property int $device_type 设备类型,1安卓,2苹果
 * @property int $status 状态:1登陆,0未登录
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereAppid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereAppkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserDevice whereUserId($value)
 */
class UserDevice extends Model {
    use BelongsToUserTrait;

    protected $table = 'user_device';
    protected $fillable = ['client_id', 'user_id','device_token','appid','appkey','device_type'];

    const DEVICE_TYPE_IOS = 2;
    const DEVICE_TYPE_ANDROID = 1;

}