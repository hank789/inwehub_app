<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\QuestionInvitation
 *
 * @property int $id
 * @property int $from_user_id
 * @property int $user_id
 * @property string $send_to
 * @property int $question_id
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereFromUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereSendTo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionInvitation whereUserId($value)
 * @mixin \Eloquent
 */
class QuestionInvitation extends Model
{
    use BelongsToUserTrait;
    protected $table = 'question_invitations';
    protected $fillable = ['question_id','user_id','from_user_id','send_to'];

    public function question(){
        return $this->belongsTo('App\Models\Question');
    }

}
