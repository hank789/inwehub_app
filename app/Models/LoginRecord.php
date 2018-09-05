<?php namespace App\Models;

/**
 * App\Models\LoginRecord
 *
 * @author : wanghui
 * @date : 2017/4/7 下午6:49
 * @email : hank.huiwang@gmail.com
 * @property int $id
 * @property int $user_id 用户id
 * @property string|null $ip ip地址，可为空
 * @property string|null $address 登录设备大致地理位置，可为空
 * @property string|null $device_system 登录设备操作系统，可为空
 * @property string|null $device_name 登录设备名称，可为空
 * @property string|null $device_model 登录设备型号，可为空
 * @property string|null $device_code 设备码，可为空
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereDeviceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereDeviceModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereDeviceSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $address_detail 登录设备详细地理位置
 * @property string|null $latitude 纬度
 * @property string|null $longitude 经度
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereAddressDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LoginRecord whereLongitude($value)
 */

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

class LoginRecord extends Model
{
    use BelongsToUserTrait;

    protected $table = 'login_records';
    protected $fillable = ['user_id', 'ip','address','device_system','device_name','device_model','device_code','longitude','latitude','address_detail'];

}