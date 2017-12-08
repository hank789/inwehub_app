<?php

namespace App\Models\Feed;

use App\Models\Answer;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Question;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUserId($value)
 * @mixin \Eloquent
 * @property int $feed_type 分类
 * @property array $data
 * @property int|null $audit_status 审核状态 0-未审核 1-已审核 2-未通过
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feed\Feed onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereAuditStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereFeedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feed\Feed withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Feed\Feed withoutTrashed()
 * @property int $is_anonymous 是否匿名
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereIsAnonymous($value)
 */
class Feed extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'feeds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'user_id', 'feed_type','source_id','source_type','data','audit_status', 'is_anonymous'
    ];

    protected $casts = [
        'data' => 'json',
    ];

    const AUDIT_STATUS_PENDING = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_REJECT = 2;

    const FEED_TYPE_ANSWER_PAY_QUESTION = 1;//回答专业问题
    const FEED_TYPE_ANSWER_FREE_QUESTION = 2;//回答互动问题
    const FEED_TYPE_CREATE_FREE_QUESTION = 3;//发布互动问题
    const FEED_TYPE_CREATE_PAY_QUESTION = 4;//发布专业问题
    const FEED_TYPE_SUBMIT_READHUB_ARTICLE = 5;//发布阅读文章
    const FEED_TYPE_FOLLOW_FREE_QUESTION = 6;//关注了互动问答
    const FEED_TYPE_FOLLOW_USER = 7;//关注了用户
    const FEED_TYPE_COMMENT_PAY_QUESTION = 8;//评论了专业问答
    const FEED_TYPE_COMMENT_FREE_QUESTION = 9;//评论了互动问答
    const FEED_TYPE_COMMENT_READHUB_ARTICLE = 10;//评论了阅读文章
    const FEED_TYPE_UPVOTE_PAY_QUESTION = 11;//赞了专业问答
    const FEED_TYPE_UPVOTE_FREE_QUESTION = 12;//赞了互动问答
    const FEED_TYPE_UPVOTE_READHUB_ARTICLE = 13;//赞了阅读文章






    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSourceFeedData() {
        $url = '';
        $data = [];
        switch ($this->feed_type) {
            case self::FEED_TYPE_ANSWER_PAY_QUESTION:
                //回答专业问题
                $url = '/askCommunity/major/'.$this->data['question_id'];
                $answer = Answer::find($this->data['answer_id']);
                $supporters = [];
                $support_uids = Support::where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->take(20)->pluck('user_id');
                if ($support_uids) {
                    $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
                }
                $is_pay_for_view = false;
                $is_self = Auth::user()->id == $answer->question->user_id;
                $is_answer_author = Auth::user()->id == $answer->user_id;
                $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
                $data = [
                    'title' => $this->data['question_title'],
                    'comment_number' => $answer->comments,
                    'average_rate'   => $answer->getFeedbackRate(),
                    'support_number' => $answer->supports,
                    'supporter_list' => $supporters,
                    'is_pay_for_view' => ($is_self || $is_answer_author || $is_pay_for_view),
                    'answer_id' => $answer->id
                ];
                break;
            case self::FEED_TYPE_ANSWER_FREE_QUESTION:
                //回答互动问题
                $url = '/askCommunity/interaction/'.$this->source_id;
                $answer = Answer::find($this->source_id);
                $question = Question::find($answer->question_id);
                $is_followed_question = 0;
                $attention_question = Attention::where("user_id",'=',Auth::user()->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
                if ($attention_question) {
                    $is_followed_question = 1;
                }
                $data = [
                    'title'     => $this->data['question_title'],
                    'content'   => $this->data['answer_content'],
                    'comment_num' => $answer->comments,
                    'support_number' => $answer->supports,
                    'follow_question_num'  => $question->followers,
                    'is_followed_question' => $is_followed_question,
                    'answer_id' => $answer->id
                ];
                break;
            case self::FEED_TYPE_CREATE_FREE_QUESTION:
                //发布互动问题
                $url = '/askCommunity/interaction/answers/'.$this->data['question_id'];
                $question = Question::find($this->data['question_id']);
                $answer_uids = Answer::where('question_id',$question->id)->select('user_id')->distinct()->take(5)->pluck('user_id')->toArray();
                $answer_users = [];
                if ($answer_uids) {
                    $answer_users = User::whereIn('id',$answer_uids)->select('uuid','name')->get()->toArray();
                }
                $is_followed_question = 0;
                $attention_question = Attention::where("user_id",'=',Auth::user()->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
                if ($attention_question) {
                    $is_followed_question = 1;
                }
                $data = [
                    'title' => $this->data['question_title'],
                    'answer_num' => $question->answers,
                    'follow_num' => $question->followers,
                    'answer_user_list' => $answer_users,
                    'is_followed_question' => $is_followed_question
                ];
                break;
            case self::FEED_TYPE_CREATE_PAY_QUESTION:
                //发布专业问题
                $url = '/answer/'.$this->data['question_id'];
                $data = [
                    'title' => $this->data['question_title']
                ];
                break;
            case self::FEED_TYPE_SUBMIT_READHUB_ARTICLE:
                //发布文章
                $comment_url = '/c/'.$this->data['category_id'].'/'.$this->data['slug'];
                $url = $this->data['view_url']?:$comment_url;
                $submission = Submission::find($this->source_id);
                if (!$submission) return null;
                $support_uids = Support::where('supportable_id',$submission->id)
                    ->where('supportable_type',Submission::class)->take(20)->pluck('user_id');
                $supporters = [];
                if ($support_uids) {
                    $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
                }
                $upvote = Support::where('user_id',Auth::user()->id)
                    ->where('supportable_id',$submission->id)
                    ->where('supportable_type',Submission::class)
                    ->exists();
                $data = [
                    'title'     => $this->data['submission_title'],
                    'img'       => $this->data['img'],
                    'domain'    => $this->data['domain'],
                    'tags'      => $submission->tags()->get()->toArray(),
                    'submission_id' => $this->source_id,
                    'current_address_name' => $this->data['current_address_name']??'',
                    'current_address_longitude' => $this->data['current_address_longitude']??'',
                    'current_address_latitude'  => $this->data['current_address_latitude']??'',
                    'comment_url' => $comment_url,
                    'comment_number' => $submission->comments_number,
                    'support_number' => $submission->upvotes,
                    'supporter_list' => $supporters,
                    'is_upvoted'     => $upvote ? 1 : 0,
                    'submission_type' => $submission->type,
                    'comments' => $submission->comments()->with('owner','children')->where('parent_id', 0)->orderBy('id','desc')->take(8)->get()
                ];
                break;
            case self::FEED_TYPE_FOLLOW_FREE_QUESTION:
                //关注了互动问答
                $url = '/askCommunity/interaction/answers/'.$this->data['question_id'];
                $question = Question::find($this->data['question_id']);
                $answer_uids = Answer::where('question_id',$question->id)->select('user_id')->distinct()->take(5)->pluck('user_id')->toArray();
                $answer_users = [];
                if ($answer_uids) {
                    $answer_users = User::whereIn('id',$answer_uids)->select('uuid','name')->get()->toArray();
                }
                $data = [
                    'title' => $this->data['question_title'],
                    'answer_num' => $question->answers,
                    'follow_num' => $question->followers,
                    'answer_user_list' => $answer_users,
                    'is_followed_question' => 1
                ];
                break;
            case self::FEED_TYPE_FOLLOW_USER:
                //关注了用户
                $follower_user = User::find($this->data['follow_user_id']);
                $url = '/share/resume/'.$follower_user->uuid;
                $data = [
                    'follow_user_id'    =>    $follower_user->id,
                    'follow_user_name'  =>    $follower_user->name,
                    'follow_user_uuid'  =>    $follower_user->uuid,
                    'follow_user_avatar'  =>    $follower_user->avatar,
                    'follow_user_is_expert' => $follower_user->userData->authentication_status == 1 ? 1 : 0
                ];
                break;
            case self::FEED_TYPE_COMMENT_PAY_QUESTION:
                //评论了专业问答
                $url = $this->data['feed_url'];
                $data = $this->data;
                break;
            case self::FEED_TYPE_COMMENT_FREE_QUESTION:
                //评论了互动问答
                $url = $this->data['feed_url'];
                $data = $this->data;
                break;
            case self::FEED_TYPE_COMMENT_READHUB_ARTICLE:
                //评论了文章
                $url = '/c/'.$this->data['category_id'].'/'.$this->data['slug'].'?comment='.$this->data['comment_id'];
                $data = [
                    'title'     => $this->data['submission_title'],
                    'img'       => $this->data['img'],
                    'domain'    => $this->data['domain'],
                    'submission_type' => $this->data['type']??'link',
                    'comment_content' => $this->data['comment_content'],
                    'submission_username' => $this->data['submission_username']
                ];
                break;
            case self::FEED_TYPE_UPVOTE_PAY_QUESTION:
                //赞了专业问答
                $url = $this->data['feed_url'];
                $data = $this->data;
                $answer = Answer::find($this->source_id);
                $supporters = [];
                $support_uids = Support::where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->take(20)->pluck('user_id');
                if ($support_uids) {
                    $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
                }
                $is_pay_for_view = false;
                $is_self = Auth::user()->id == $answer->question->user_id;
                $is_answer_author = Auth::user()->id == $answer->user_id;
                $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
                $data['comment_number'] = $answer->comments;
                $data['average_rate']   = $answer->getFeedbackRate();
                $data['support_number'] = $answer->supports;
                $data['supporter_list'] = $supporters;
                $data['answer_id'] = $answer->id;
                $data['is_pay_for_view'] = ($is_self || $is_answer_author || $is_pay_for_view);
                break;
            case self::FEED_TYPE_UPVOTE_FREE_QUESTION:
                //赞了互动问答
                $url = $this->data['feed_url'];
                $data = $this->data;
                $answer = Answer::find($this->source_id);
                $question = Question::find($answer->question_id);
                $is_followed_question = 0;
                $attention_question = Attention::where("user_id",'=',Auth::user()->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
                if ($attention_question) {
                    $is_followed_question = 1;
                }
                $data['comment_num'] = $answer->comments;
                $data['support_number'] = $answer->supports;
                $data['follow_question_num'] = $question->followers;
                $data['answer_id'] = $answer->id;
                $data['is_followed_question'] = $is_followed_question;
                break;
            case self::FEED_TYPE_UPVOTE_READHUB_ARTICLE:
                //赞了文章
                $comment_url = '/c/'.$this->data['category_id'].'/'.$this->data['slug'];
                $url = $this->data['view_url']?:$comment_url;
                $data = [
                    'submission_username' => $this->data['submission_username'],
                    'title'     => $this->data['submission_title'],
                    'img'       => $this->data['img'],
                    'domain'    => $this->data['domain'],
                    'submission_type' => $this->data['type']??'link',
                    'submission_id' => $this->source_id,
                    'comment_url' => $comment_url
                ];
                break;
        }
        return ['url'=>$url,'feed'=>$data];
    }


}
