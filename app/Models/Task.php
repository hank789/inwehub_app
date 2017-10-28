<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $action
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property int $status
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereUserId($value)
 */
class Task extends Model
{
    use BelongsToUserTrait;
    protected $table = 'task';
    protected $fillable = ['user_id', 'source_type','source_id','subject','status','action'];

    const ACTION_TYPE_ANSWER = 'answer';
    const ACTION_TYPE_ANSWER_FEEDBACK = 'answer_feedback';
    const ACTION_TYPE_INVITE_ANSWER = 'invite_answer';


}
