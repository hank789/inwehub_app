<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class Feedback extends Model
{
    use BelongsToUserTrait;
    protected $table = 'feedback';
    protected $fillable = ['user_id', 'source_type','source_id','content','to_user_id','star','created_at'];
    public $timestamps = false;

}
