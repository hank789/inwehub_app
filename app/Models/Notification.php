<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property int $user_id
 * @property int $to_user_id
 * @property string $type
 * @property int $source_id
 * @property string $subject
 * @property string $content
 * @property int $refer_id
 * @property string $refer_type
 * @property bool $is_read
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $toUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereIsRead($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereReferId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereReferType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereToUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends DatabaseNotification
{
    const NOTIFICATION_TYPE_NOTICE = 1;//普通通知
    const NOTIFICATION_TYPE_MONEY = 2;//资金类型的通知
    const NOTIFICATION_TYPE_TASK = 3;//任务类型的通知
    const NOTIFICATION_TYPE_READ = 4;//发现类型的通知
    const NOTIFICATION_TYPE_INTEGRAL = 5;//积分类型的通知
}
