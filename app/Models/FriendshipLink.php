<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FriendshipLink
 *
 * @property int $id
 * @property string $name
 * @property string $slogan
 * @property string $url
 * @property int $sort
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereSlogan($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendshipLink whereUrl($value)
 * @mixin \Eloquent
 */
class FriendshipLink extends Model
{
    protected $table = 'friendship_links';
    protected $fillable = ['name', 'slogan','url','sort','status'];

}
