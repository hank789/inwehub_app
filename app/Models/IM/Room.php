<?php

namespace App\Models\IM;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUserId($value)
 * @mixin \Eloquent
 * @property int $contact_id
 * @property int $message_id
 * @property-read \App\Models\User $contact
 * @property-read \App\Models\IM\Message $last_message
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Conversation whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Conversation whereMessageId($value)
 * @property int $r_type 房间类型，1为私聊，2为群聊
 * @property string|null $r_name 房间名字
 * @property string|null $r_description 房间描述
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Room whereRDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Room whereRName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Room whereRType($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 */
class Room extends Model
{
    use BelongsToUserTrait;
    protected $table = 'im_room';

    protected $fillable = [
        'r_type', 'user_id','source_id', 'source_type', 'r_name', 'r_description'
    ];

    /**
     *   The attributes that should be hidden for arrays.
     *
     *   @var array
     */
    protected $hidden = [
        'updated_at',
    ];

    const ROOM_TYPE_WHISPER = 1;//私聊
    const ROOM_TYPE_GROUP = 2;//群聊

    public function source()
    {
        return $this->morphTo();
    }
}
