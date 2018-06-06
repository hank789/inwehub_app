<?php

namespace App\Models\Feed;

use App\Models\Answer;
use App\Models\Attention;
use App\Models\Comment;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Question;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

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
 * @property int $top
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereTop($value)
 * @property string $tags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feed\Feed whereTags($value)
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
        'user_id', 'user_id', 'group_id', 'public', 'top','tags', 'feed_type','source_id','source_type','data','audit_status', 'is_anonymous'
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
    const FEED_TYPE_ADOPT_ANSWER = 14;//采纳了回答






    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $data = [];
        $columns = [
            'feed_content',
            'question_title',
            'submission_title',
            'comment_content',
            'submission_username',
            'current_address_name',
            'answer_content',
            'answer_user_name'
        ];
        foreach ($this->data as $key=>$val) {
            if (in_array($key,$columns)) {
                $data[] = $val;
            }
        }
        return [
            'title' => strip_tags(implode(',',$data)),
        ];

    }

    public function getSourceFeedData($search_type=0) {
        $url = '';
        $data = [];
        switch ($this->feed_type) {
            case self::FEED_TYPE_ANSWER_PAY_QUESTION:
                //回答专业问题
                $answer = Answer::find($this->source_id);
                if (empty($answer)) return false;
                $url = '/askCommunity/major/'.$answer->question_id;
                $question = $answer->question;
                $is_pay_for_view = false;

                if (Auth::user()->id == $question->user_id) {
                    $is_pay_for_view = true;
                }
                if (Auth::user()->id == $answer->user_id) {
                    $is_pay_for_view = true;
                }
                $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
                $data = [
                    'question_title' => $question->title,
                    'answer_content' => str_limit($answer->getContentText(),$is_pay_for_view?120:20),
                    'comment_number' => $answer->comments,
                    'average_rate'   => $answer->getFeedbackRate(),
                    'support_number' => $answer->supports,
                    'is_pay_for_view' => $is_pay_for_view,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元',
                    'answer_id' => $answer->id,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray()
                ];
                break;
            case self::FEED_TYPE_ANSWER_FREE_QUESTION:
                //回答互动问题
                $url = '/askCommunity/interaction/'.$this->source_id;
                $answer = Answer::find($this->source_id);
                if (empty($answer)) return false;
                $question = Question::find($answer->question_id);
                $is_pay_for_view = true;
                if ($answer->adopted_at) {
                    $is_pay_for_view = false;
                    if (Auth::user()->id == $question->user_id) {
                        $is_pay_for_view = true;
                    }
                    if (Auth::user()->id == $answer->user_id) {
                        $is_pay_for_view = true;
                    }
                    $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                    if ($payOrder) {
                        $is_pay_for_view = true;
                    }
                }
                $data = [
                    'question_title'     => $question->title,
                    'answer_content'   => str_limit($answer->getContentText(),$is_pay_for_view?120:20),
                    'comment_number' => $answer->comments,
                    'support_number' => $answer->supports,
                    'is_pay_for_view' => $is_pay_for_view,
                    'price'      => $question->price,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元悬赏'.($question->status != 8 ? '中':''),
                    'answer_id' => $answer->id,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray(),
                ];
                break;
            case self::FEED_TYPE_CREATE_FREE_QUESTION:
                //发布互动问题
                $url = '/askCommunity/interaction/answers/'.$this->source_id;
                $question = Question::find($this->source_id);
                switch ($search_type) {
                    case 1:
                    case 5:
                        if ($question->hide) {
                            return null;
                        }
                        break;
                }
                $data = [
                    'title' => $question->title,
                    'answer_number' => $question->answers,
                    'follow_number' => $question->followers,
                    'question_id' => $question->id,
                    'price'      => $question->price,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元悬赏'.($question->status != 8 ? '中':''),
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray()
                ];
                break;
            case self::FEED_TYPE_SUBMIT_READHUB_ARTICLE:
                //发布文章
                $submission = Submission::find($this->source_id);
                $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                $url = $submission->data['url']??$comment_url;
                if (!$submission) return null;
                $upvote = Support::where('user_id',Auth::user()->id)
                    ->where('supportable_id',$submission->id)
                    ->where('supportable_type',Submission::class)
                    ->exists();
                $group = Group::find($submission->group_id);
                $data = [
                    'title'     => $submission->partHtmlTitle(),
                    'img'       => $submission->data['img'],
                    'files'       => $submission->data['files']??'',
                    'domain'    => $submission->data['domain']??'',
                    'tags'      => $submission->tags()->get()->toArray(),
                    'submission_id' => $this->source_id,
                    'current_address_name' => $submission->data['current_address_name']??'',
                    'current_address_longitude' => $submission->data['current_address_longitude']??'',
                    'current_address_latitude'  => $submission->data['current_address_latitude']??'',
                    'comment_url' => $comment_url,
                    'comment_number' => $submission->comments_number,
                    'support_number' => $submission->upvotes,
                    'is_upvoted'     => $upvote ? 1 : 0,
                    'submission_type' => $submission->type,
                    //'comments' => $submission->comments()->with('owner','children')->where('parent_id', 0)->orderBy('id','desc')->take(8)->get(),
                    'group'    => $group?$group->toArray():[]
                ];
                $data['group']['subscribers'] = $group->getHotIndex();
                break;
            case self::FEED_TYPE_FOLLOW_FREE_QUESTION:
                //关注了互动问答
                $url = '/askCommunity/interaction/answers/'.$this->source_id;
                $question = Question::find($this->source_id);
                $data = [
                    'title' => $question->title,
                    'answer_number' => $question->answers,
                    'price'      => $question->price,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元悬赏'.($question->status != 8 ? '中':''),
                    'follow_number' => $question->followers,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray(),
                ];
                break;
            case self::FEED_TYPE_UPVOTE_PAY_QUESTION:
                //赞了专业问答
                $answer = Answer::find($this->source_id);
                if (empty($answer)) return false;
                $question = $answer->question;
                $url = '/askCommunity/major/'.$answer->question_id;
                $is_pay_for_view = false;

                if (Auth::user()->id == $question->user_id) {
                    $is_pay_for_view = true;
                }
                if (Auth::user()->id == $answer->user_id) {
                    $is_pay_for_view = true;
                }
                $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
                $data = [
                    'question_title' => $question->title,
                    'answer_content' => str_limit($answer->getContentText(),$is_pay_for_view?120:20),
                    'comment_number' => $answer->comments,
                    'average_rate'   => $answer->getFeedbackRate(),
                    'support_number' => $answer->supports,
                    'is_pay_for_view' => $is_pay_for_view,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元',
                    'answer_id' => $answer->id,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray()
                ];
                break;
            case self::FEED_TYPE_UPVOTE_FREE_QUESTION:
                //赞了互动问答
                $answer = Answer::find($this->source_id);
                if (empty($answer)) return false;
                $question = Question::find($answer->question_id);
                $url = '/askCommunity/interaction/'.$answer->id;
                $is_pay_for_view = true;
                if ($answer->adopted_at) {
                    $is_pay_for_view = false;
                    if (Auth::user()->id == $question->user_id) {
                        $is_pay_for_view = true;
                    }
                    if (Auth::user()->id == $answer->user_id) {
                        $is_pay_for_view = true;
                    }
                    $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                    if ($payOrder) {
                        $is_pay_for_view = true;
                    }
                }
                $data = [
                    'question_title'     => $question->title,
                    'answer_content'   => str_limit($answer->getContentText(),$is_pay_for_view?120:20),
                    'comment_number' => $answer->comments,
                    'support_number' => $answer->supports,
                    'is_pay_for_view' => $is_pay_for_view,
                    'price'      => $question->price,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元悬赏'.($question->status != 8 ? '中':''),
                    'answer_id' => $answer->id,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray(),
                ];
                break;
            case self::FEED_TYPE_ADOPT_ANSWER:
                //采纳了互动回答
                $url = '/askCommunity/interaction/'.$this->source_id;
                $answer = Answer::find($this->source_id);
                if (empty($answer)) return false;
                $question = Question::find($answer->question_id);
                $is_pay_for_view = true;
                if ($answer->adopted_at) {
                    $is_pay_for_view = false;
                    if (Auth::user()->id == $question->user_id) {
                        $is_pay_for_view = true;
                    }
                    if (Auth::user()->id == $answer->user_id) {
                        $is_pay_for_view = true;
                    }
                    $payOrder = $answer->orders()->where('user_id',Auth::user()->id)->where('return_param','view_answer')->first();
                    if ($payOrder) {
                        $is_pay_for_view = true;
                    }
                }
                $data = [
                    'question_title'     => $question->title,
                    'answer_content'   => str_limit($answer->getContentText(),$is_pay_for_view?120:20),
                    'comment_number' => $answer->comments,
                    'support_number' => $answer->supports,
                    'is_pay_for_view' => $is_pay_for_view,
                    'price'      => $question->price,
                    'status'     => $question->status,
                    'status_description' => $question->price.'元悬赏'.($question->status != 8 ? '中':''),
                    'answer_id' => $answer->id,
                    'question_id' => $question->id,
                    'tags'      => $question->tags()->select('tag_id','name')->get()->toArray(),
                ];
                break;
        }
        return ['url'=>$url,'feed'=>$data];
    }


}
