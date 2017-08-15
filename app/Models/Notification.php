<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
class Notification extends Model
{
    protected $table = 'notifications';

}
