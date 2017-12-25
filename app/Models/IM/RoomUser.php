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
 */
class RoomUser extends Model
{
    use BelongsToUserTrait;
    protected $table = 'im_room_user';

    protected $fillable = [
        'room_id', 'user_id'
    ];

    /**
     *   The attributes that should be hidden for arrays.
     *
     *   @var array
     */
    protected $hidden = [
        'updated_at',
    ];

    public function last_message()
    {
        $last_c = Message::where('room_id',$this->room_id)->orderBy('id','desc')->first();
        return Message::find($last_c->id);
    }

}
