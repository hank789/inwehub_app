<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TagCategoryRel
 *
 * @property int $id
 * @property int $tag_id
 * @property int category_id
 * @mixin \Eloquent
 */
class UserToOauthUser extends Model
{
    protected $table = 'user_to_oauth_user';

    protected $fillable = ['user_id', 'to_oauth_user_id','data'];

    public $timestamps = false;

    protected $casts = [
        'data' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
