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
 * @property int $priority
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task wherePriority($value)
 */
class Task extends Model
{
    use BelongsToUserTrait;
    protected $table = 'task';
    protected $fillable = ['user_id', 'source_type','source_id','subject','status','priority','action'];

    const ACTION_TYPE_ANSWER = 'answer';
    const ACTION_TYPE_ANSWER_FEEDBACK = 'answer_feedback';
    const ACTION_TYPE_INVITE_ANSWER = 'invite_answer';
    const ACTION_TYPE_NEWBIE_ASK = 'newbie_ask';
    const ACTION_TYPE_NEWBIE_READHUB_COMMENT = 'newbie_readhub_comment';
    const ACTION_TYPE_NEWBIE_COMPLETE_USERINFO = 'newbie_complete_userinfo';
    const ACTION_TYPE_ADOPTED_ANSWER = 'adopted_answer';

    public static $actionPriority = [
        self::ACTION_TYPE_ANSWER => ['name'=>'回答','priority'=>500],
        self::ACTION_TYPE_ANSWER_FEEDBACK => ['name'=>'回答点评','priority'=>400],
        self::ACTION_TYPE_INVITE_ANSWER => ['name'=>'邀请回答','priority'=>450],
        self::ACTION_TYPE_NEWBIE_ASK => ['name'=>'新手任务-提问','priority'=>590],
        self::ACTION_TYPE_NEWBIE_READHUB_COMMENT => ['name'=>'新手任务-回复','priority'=>580],
        self::ACTION_TYPE_NEWBIE_COMPLETE_USERINFO => ['name'=>'新手任务-完善个人信息','priority'=>600],
        self::ACTION_TYPE_ADOPTED_ANSWER => ['name'=>'采纳最佳答案','priority'=>401]
    ];

}
