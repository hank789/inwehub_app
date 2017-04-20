<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class Task extends Model
{
    use BelongsToUserTrait;
    protected $table = 'task';
    protected $fillable = ['user_id', 'source_type','source_id','subject','status'];
}
