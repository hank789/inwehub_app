<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Message
 *
 * @property int $id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property string $content
 * @property bool $is_read
 * @property bool $from_deleted
 * @property bool $to_deleted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereFromDeleted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereFromUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereIsRead($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereToDeleted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereToUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Message whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = ['from_user_id', 'to_user_id','content','is_read'];


}
