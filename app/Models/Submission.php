<?php namespace App\Models;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: wanghui@yonglibao.com
 */

use App\Logic\QuillLogic;
use App\Models\Feed\Feed;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BosonNLPService;
use App\Services\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use QL\QueryList;

/**
 * App\Models\Submission
 *
 * @package App\Models
 * @mixin \Eloquent
 * @property int $id
 * @property int $recommend_status
 * @property int $recommend_sort
 * @property string $slug
 * @property string $title
 * @property string $type
 * @property array $data
 * @property string $category_name
 * @property float $rate
 * @property int|null $resubmit_id
 * @property int $user_id
 * @property int $nsfw
 * @property int $category_id
 * @property int $upvotes
 * @property int $downvotes
 * @property int $comments_number
 * @property string|null $approved_at
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $url
 * @property string|null $domain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCommentsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDownvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereNsfw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRecommendSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRecommendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereResubmitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUpvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Readhub\Bookmark[] $bookmarks
 * @property-read \App\Models\Readhub\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Readhub\Comment[] $comments
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission bookmarkedBy($user_id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withoutTrashed()
 * @property int $collections
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCollections($value)
 * @property int $author_id
 * @property-read \App\Models\User $author
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereAuthorId($value)
 * @property int $views
 * @property int $group_id
 * @property int $is_recommend
 * @property int $public
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereIsRecommend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereViews($value)
 */
class Submission extends Model {

    use SoftDeletes,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToUserTrait;

    protected $table = 'submissions';

    protected $casts = [
        'data' => 'json'
    ];

    protected $with = ['owner'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'title', 'slug','author_id', 'type', 'category_id', 'category_name', 'rate','group_id',
        'upvotes', 'downvotes', 'user_id', 'views', 'data', 'approved_at','public','is_recommend', 'support_type',
        'deleted_at', 'comments_number', 'status'
    ];

    const RECOMMEND_STATUS_NOTHING = 0;
    const RECOMMEND_STATUS_PENDING = 1;
    const RECOMMEND_STATUS_PUBLISH = 2;


    public static function boot()
    {
        parent::boot();
        static::deleted(function($submission){
            Feed::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Collection::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Comment::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Support::where('supportable_id',$submission->id)
                ->where('supportable_type','App\Models\Submission')
                ->delete();
            //删除推荐
            RecommendRead::where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->delete();
            /*删除标签关联*/
            Taggable::where('taggable_type','=',get_class($submission))->where('taggable_id','=',$submission->id)->delete();
            /*删除动态*/
            Doing::where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->delete();
        });
    }

    public static function search($word)
    {
        $list = self::where('title','like',"%$word%");
        return $list;
    }

    public function formatTitle(){
        return strip_tags($this->title);
    }

    public function partHtmlTitle(){
        return strip_tags($this->title,'<a><span>');
    }

    public function isRecommendRead() {
        $recommendRead = RecommendRead::where('source_id',$this->id)->where('source_type',Submission::class)->first();
        if ($recommendRead) {
            return true;
        }
        return false;
    }

    /**
     * A submission is owned by a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select(['id', 'name', 'avatar', 'uuid','is_expert']);
    }

    public function author(){
        return $this->belongsTo(User::class, 'author_id')
            ->select(['id', 'name', 'avatar', 'uuid','is_expert']);
    }

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

    public function getSupportRateDesc($isSupported = true) {
        if ($this->upvotes <= 0 && $this->downvotes <=0) return '暂无，快来表个态';
        $rate = (bcdiv($this->upvotes,$this->upvotes + $this->downvotes,2) * 100).'%';
        switch ($this->support_type) {
            case 1:
                return '有'.$rate.'的人'.($isSupported?'与您一样':'').'点赞';
                break;
            case 2:
                return '有'.$rate.'的人'.($isSupported?'与您一样':'').'看好';
                break;
            case 3:
                return '有'.$rate.'的人'.($isSupported?'与您一样':'').'支持';
                break;
            case 4:
                return '有'.$rate.'的人'.($isSupported?'与您一样':'').'意外';
                break;
        }
    }

    public function getDownvoteRateDesc() {
        if ($this->upvotes <= 0 && $this->downvotes <=0) return '暂无，快来表个态';
        $rate = (bcdiv($this->downvotes,$this->upvotes + $this->downvotes,2) * 100).'%';
        switch ($this->support_type) {
            case 1:
                return '有'.$rate.'的人与您一样点踩';
                break;
            case 2:
                return '有'.$rate.'的人与您一样不看好';
                break;
            case 3:
                return '有'.$rate.'的人与您一样反对';
                break;
            case 4:
                return '有'.$rate.'的人与您一样不意外';
                break;
        }
    }

    public function getSupportPercent() {
        if ($this->upvotes <= 0) return '0';
        return (bcdiv($this->upvotes,$this->upvotes + $this->downvotes,2) * 100);
    }

    public function getSupportRate() {
        if ($this->upvotes <= 0) return '0%';
        return (bcdiv($this->upvotes,$this->upvotes + $this->downvotes,2) * 100).'%';
    }

    public function getSupportTypeTip() {
        switch ($this->support_type) {
            case 1:
                return [
                    'support_tip' => '赞',
                    'downvote_tip' => '踩'
                ];
                break;
            case 2:
                return [
                    'support_tip' => '我看好',
                    'downvote_tip' => '我不看好'
                ];
                break;
            case 3:
                return [
                    'support_tip' => '我支持',
                    'downvote_tip' => '我反对'
                ];
                break;
            case 4:
                return [
                    'support_tip' => '意外',
                    'downvote_tip' => '不意外'
                ];
                break;
        }
    }

    //计算排名积分
    public function calculationRate(){
        $shareNumber = Doing::where('action',Doing::ACTION_SHARE_SUBMISSION_SUCCESS)
            ->where('source_id',$this->id)
            ->where('source_type',Submission::class)
            ->count();
        $commentSupports = $this->comments()->sum('supports');
        $rate =  hotRate($this->views,$this->comments_number, $this->upvotes-$this->downvotes,$commentSupports + $this->collections + $shareNumber,$this->created_at,$this->updated_at);
        $this->rate = $rate;
        $this->save();
        $recommendRead = RecommendRead::where('source_id',$this->id)->where('source_type',Submission::class)->first();
        if ($recommendRead) {
            $recommendRead->rate = $this->rate + $recommendRead->getRateWeight();
            $recommendRead->save();
        }
    }

    //设置关键词标签
    public function setKeywordTags() {
        try {
            if (config('app.env') != 'production') {
                return;
            }
            if (isset($this->data['domain']) && $this->data['domain'] == 'mp.weixin.qq.com') {
                $content = getWechatUrlBodyText($this->data['url']);
                $keywords = array_column(BosonNLPService::instance()->keywords($content,15),1);
            } elseif ($this->type == 'article') {
                $keywords = array_column(BosonNLPService::instance()->keywords(strip_tags($this->title).';'.QuillLogic::parseText($this->data['description']),15),1);
            } elseif ($this->type == 'text') {
                $keywords = array_column(BosonNLPService::instance()->keywords(strip_tags($this->title),15),1);
            } else {
                $ql = QueryList::get($this->data['url']);
                $metas = $ql->find('meta[name=keywords]')->content;
                if ($metas) {
                    $metas = str_replace('，',',',$metas);
                    $metas = str_replace('、',',',$metas);
                    $keywords = explode(',',$metas);
                } else {
                    $description = $ql->find('meta[name=description]')->content;
                    $keywords = array_column(BosonNLPService::instance()->keywords(strip_tags($this->title).'。'.$description,15),1);
                }
            }
            $tags = [];
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                $keyword = str_replace('，','',$keyword);
                $keyword = str_replace('、','',$keyword);
                $keyword = str_replace('"','',$keyword);
                $keyword = str_replace('。','',$keyword);
                if (RateLimiter::instance()->hGet('ignore_tags',$keyword)) {
                    continue;
                }
                //如果含有中文，则至少2个中文字符
                if (preg_match("/[\x7f-\xff]/", $keyword) && strlen($keyword) >= 6) {
                    $tags[] = $keyword;
                } elseif (!preg_match("/[\x7f-\xff]/", $keyword) && strlen($keyword) >= 2) {
                    //如果不含有中文，则至少2个字符
                    $tags[] = $keyword;
                }
            }
            Tag::multiAddByName($tags,$this,1);
        } catch (\Exception $e) {
            \Log::info('setKeywordTagsError',$this->toArray());
            app('sentry')->captureException($e,$this->toArray());
        }
    }

}