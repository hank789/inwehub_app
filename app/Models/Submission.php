<?php namespace App\Models;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: hank.huiwang@gmail.com
 */

use App\Jobs\UpdateSubmissionKeywords;
use App\Logic\QuillLogic;
use App\Models\Feed\Feed;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BosonNLPService;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
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

    use SoftDeletes,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToUserTrait, Searchable;

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
        'deleted_at', 'comments_number', 'status','	created_at', 'updated_at'
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

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $data = [$this->title];
        $columns = [
            'description',
            'title',
            'keywords'
        ];
        if ($this->data) {
            foreach ($this->data as $key=>$val) {
                if (in_array($key,$columns) && $val) {
                    $data[] = QuillLogic::parseText($val);
                }
            }
        }
        $title = strip_tags(implode(';',$data));
        $str_length = strlen($title);
        if ($str_length >= 32760) {
            //elasticsearch的索引长度为32766
            $title = str_limit($title,$str_length/1.6,'');
        }
        return [
            'title' => $title,
            'status' => $this->status,
            'public' => $this->public,
            'group_id' => $this->group_id,
            'rate' => $this->rate
        ];

    }

    public function formatTitle(){
        return strip_tags($this->title);
    }

    public function partHtmlTitle(){
        return strip_tags($this->title,'<a><span>');
    }

    public function formatListItem($user, $withGroup = true) {
        $submission = $this;
        //发布文章
        $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
        $url = $comment_url;
        $support_uids = Support::where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)->take(20)->pluck('user_id');
        $supporters = [];
        if ($support_uids) {
            $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
        }
        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $img = $submission->data['img']??'';
        $sourceData = [
            'title'     => strip_tags($submission->title),
            'article_title' => $submission->data['title']??'',
            'img'       => $img,
            'files'       => $submission->data['files']??'',
            'domain'    => $submission->data['domain']??'',
            'tags'      => $submission->tags()->wherePivot('is_display',1)->get()->toArray(),
            'submission_id' => $submission->id,
            'current_address_name' => $submission->data['current_address_name']??'',
            'current_address_longitude' => $submission->data['current_address_longitude']??'',
            'current_address_latitude'  => $submission->data['current_address_latitude']??'',
            'comment_url' => $comment_url,
            'comment_number' => $submission->comments_number,
            'support_number' => $submission->upvotes,
            'supporter_list' => $supporters,
            'is_upvoted'     => $upvote ? 1 : 0,
            'is_recommend'   => $submission->is_recommend,
            'submission_type' => $submission->type,
            'group'    => $withGroup?$submission->group->toArray():null
        ];
        if ($sourceData['group']) {
            $sourceData['group']['name'] = str_limit($sourceData['group']['name'], 20);
        }
        $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE;
        if ($submission->type == 'text') $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_SHARE;
        if ($submission->type == 'link') {
            $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_LINK;
        }

        $item = [
            'id' => $submission->id,
            'title' => $submission->user->name.'发布了'.($submission->type == 'article' ? '文章':'分享'),
            'top' => $submission->top,
            'user'  => [
                'id'    => $submission->user->id ,
                'uuid'  => $submission->user->uuid,
                'name'  => $submission->user->name,
                'is_expert' => $submission->user->is_expert,
                'avatar'=> $submission->user->avatar
            ],
            'feed'  => $sourceData,
            'url'   => $url,
            'feed_type'  => $feed_type,
            'created_at' => (string)$submission->created_at
        ];
        return $item;
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
        $views = $this->views;
        //如果是原创文章，权重高一点，默认给100阅读
        if ($this->type == 'article') {
            $views += 100;
        }
        $rate =  hotRate($views,$this->comments_number, $this->upvotes-$this->downvotes,$commentSupports + $this->collections + $shareNumber,$this->created_at,$this->updated_at);
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
                $keywords = array_column(BosonNLPService::instance()->keywords($this->title.';'.$content),1);
            } elseif ($this->type == 'article') {
                $keywords = array_column(BosonNLPService::instance()->keywords(strip_tags($this->title).';'.QuillLogic::parseText($this->data['description'])),1);
            } elseif ($this->type == 'text') {
                $keywords = array_column(BosonNLPService::instance()->keywords(strip_tags($this->title)),1);
            } else {
                $parse_url = parse_url($this->data['url']);
                if (in_array($parse_url['host'],[
                    'www.enterprisetimes.co.uk',
                    'www.independent.co.uk',
                    'www.businessinsider.com',
                    'www.reuters.com',
                    'www.fool.com',
                    'www.bloomberg.com',
                    'www.wsj.com',
                    'www.investors.com',
                    'thestarphoenix.com',
                    'www.nytimes.com',
                    'www.voanews.com',
                    'www.refinery29.com',
                    'www.bizjournals.com',
                    'www.youtube.com',
                    'uk.businessinsider.com'
                ])) {
                    $html = curlShadowsocks($this->data['url']);
                    $ql = QueryList::getInstance();
                    $ql->setHtml($html);
                } else {
                    $ql = QueryList::get($this->data['url']);
                }
                $metas = $ql->find('meta[name=keywords]')->content;
                if ($metas) {
                    $metas = trim($metas);
                    $metas = str_replace('，',',',$metas);
                    $metas = str_replace('、',',',$metas);
                    $metas = str_replace(' ',',',$metas);
                    $keywords = explode(',',$metas);
                }
                $description = strip_tags($this->title).';'.$ql->find('meta[name=description]')->content;
                if ($description) {
                    $keywords_description = array_column(BosonNLPService::instance()->keywords($description),1);
                    if (isset($keywords)) {
                        $keywords = array_merge($keywords,$keywords_description);
                    } else {
                        $keywords = $keywords_description;
                    }
                }
            }
            $tags = [];
            foreach ($keywords as $keyword) {
                $keyword = formatKeyword($keyword);
                if (RateLimiter::instance()->hGet('ignore_tags',$keyword)) {
                    continue;
                }
                if (!checkInvalidTagString($keyword)) {
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
            $data = $this->data;
            $data['keywords'] = implode(',',$tags);
            $this->data = $data;
            $this->save();
            Tag::multiAddByName(array_slice($tags,0,15),$this,1);
        } catch (\Exception $e) {
            var_dump($this->data['url']);
            app('sentry')->captureException($e,$this->toArray());
            dispatch((new UpdateSubmissionKeywords($this->id))->delay(Carbon::now()->addSeconds(300)));
        }
    }

}