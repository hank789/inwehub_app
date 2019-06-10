<?php namespace App\Models;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: hank.huiwang@gmail.com
 */

use App\Jobs\UpdateSubmissionKeywords;
use App\Logic\QuillLogic;
use App\Logic\TagsLogic;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BosonNLPService;
use App\Services\RateLimiter;
use Carbon\Carbon;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;
use QL\QueryList;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCommentsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereDownvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereNsfw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereRecommendSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereRecommendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereResubmitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereUpvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Bookmark[] $bookmarks
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission bookmarkedBy($user_id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Submission onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Submission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Submission withoutTrashed()
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
class Submission extends Model implements HasMedia {

    use SoftDeletes,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToUserTrait, Searchable, HasMediaTrait;

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
        'data', 'title', 'slug','author_id', 'type', 'category_id', 'hide', 'rate','group_id','rate_star',
        'upvotes', 'downvotes', 'user_id', 'views', 'data', 'approved_at','public','is_recommend', 'support_type',
        'deleted_at', 'comments_number', 'share_number', 'status','	created_at', 'updated_at'
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
            if ($submission->group_id) {
                $group = Group::find($submission->group_id);
                if ($group->subscribers >= 1) {
                    $group->decrement('subscribers');
                }
            }
            //删除推荐
            RecommendRead::where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->delete();
            /*删除标签关联*/
            Taggable::where('taggable_type','=',get_class($submission))->where('taggable_id','=',$submission->id)->delete();
            /*删除动态*/
            Doing::where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->delete();
            if ($submission->type == 'review') {
                foreach ($submission->data['category_ids'] as $category_id) {
                    $tagC = TagCategoryRel::where('tag_id',$submission->category_id)->where('category_id',$category_id)->first();
                    $tagC->reviews -= 1;
                    $tagC->review_rate_sum -= $submission->rate_star;
                    if ($tagC->review_rate_sum < 0) {
                        $tagC->review_rate_sum = 0;
                    }
                    $tagC->review_average_rate = $tagC->reviews?bcdiv($tagC->review_rate_sum,$tagC->reviews,1):0;
                    $tagC->save();
                }
                $tag = Tag::find($submission->category_id);
                $tag->decrement('reviews');
                TagsLogic::delProductCache();
            }
            Cache::delete('submission_related_products_'.$submission->id);
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
                    $data[] = strip_tags(QuillLogic::parseText($val));
                }
            }
        }
        $title = strip_tags(implode(';',$data));
        $str_length = strlen($title);
        if ($str_length >= 32760) {
            //elasticsearch的索引长度为32766
            $title = str_limit($title,$str_length/1.6,'');
        }

        $fields = [
            'title' => strtolower($title),
            'product_type'  => $this->type=='review'?2:1,
            'status' => $this->status,
            'public' => $this->public,
            'group_id' => $this->group_id,
            'rate' => $this->rate
        ];
        if (config('app.env') != 'production') {
            unset($fields['product_type']);
        }
        return $fields;
    }

    public function formatTitle(){
        return formatHtml(strip_tags(trim($this->title)));
    }

    public function partHtmlTitle(){
        return strip_tags($this->title,'<a><span>');
    }

    public function formatListItem($user, $withGroup = true, $inwehub_user_device = 'web') {
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
        $downvote = DownVote::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();
        $isBookmark = $user->id?$user->isCollected(get_class($submission),$submission->id):0;
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$submission->group_id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();

        $img = $submission->data['img']??'';
        $sourceData = [
            'title'     => formatHtml(strip_tags($submission->title)),
            'article_title' => $submission->data['title']??'',
            'rate_star' => $submission->rate_star,
            'slug'      => $submission->slug,
            'img'       => $img,
            'files'       => $submission->data['files']??'',
            'domain'    => $submission->data['domain']??'',
            'tags'      => $submission->tags()->wherePivot('is_display',1)->get()->toArray(),
            'submission_id' => $submission->id,
            'current_address_name' => $submission->data['current_address_name']??'',
            'current_address_longitude' => $submission->data['current_address_longitude']??'',
            'current_address_latitude'  => $submission->data['current_address_latitude']??'',
            'comment_url' => $comment_url,
            'link_url' => '',
            'comment_number' => $submission->comments_number,
            'support_number' => $submission->upvotes,
            'downvote_number' => $submission->downvotes,
            'supporter_list' => $supporters,
            'is_upvoted'     => $upvote ? 1 : 0,
            'is_downvoted'   => $downvote ? 1 : 0,
            'is_bookmark'    => $isBookmark ? 1 : 0,
            'is_recommend'   => $submission->is_recommend,
            'is_joined_group'=> $groupMember?1:0,
            'is_group_owner' => $submission->group_id?($submission->group->user_id==$user->id?1:0):0,
            'submission_type' => $submission->type,
            'group'    => $withGroup&&$submission->group_id?$submission->group->toArray():''
        ];
        if ($sourceData['group']) {
            $sourceData['group']['name'] = str_limit($sourceData['group']['name'], 20);
        }

        $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE;
        $title = $submission->user->name.'发布了'.($submission->type == 'article' ? '文章':'分享');
        $top = $submission->top;
        if ($submission->type == 'text') $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_SHARE;
        if ($submission->type == 'link') {
            $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_LINK;
            $sourceData['link_url'] = $submission->data['url'];
            if (!in_array($inwehub_user_device,['web','wechat']) && $sourceData['domain'] == 'mp.weixin.qq.com') {
                if (!(str_contains($sourceData['link_url'], 'wechat_redirect') || str_contains($sourceData['link_url'], '__biz=') || str_contains($sourceData['link_url'], '/s/'))) {
                    $sourceData['link_url'] = config('app.url').'/articleInfo/'.$submission->id.'?inwehub_user_device='.$inwehub_user_device;
                }
            }
        }
        if ($submission->type == 'review') {
            $url = '/dianping/comment/'.$submission->slug;
            $sourceData['comment_url'] = $url;
            $feed_type = Feed::FEED_TYPE_SUBMIT_READHUB_REVIEW;
            $title = $submission->hide?'匿名':$submission->user->name;
            foreach ($sourceData['tags'] as $key=>$tag) {
                $sourceData['tags'][$key]['review_average_rate'] = 0;
                if (isset($submission->data['category_ids'])) {
                    $reviewInfo = Tag::getReviewInfo($tag['id']);
                    $sourceData['tags'][$key]['reviews'] = $reviewInfo['review_count'];
                    $sourceData['tags'][$key]['review_average_rate'] = $reviewInfo['review_average_rate'];
                }
            }
            $comment = Comment::where('source_id',$submission->id)->where('source_type',get_class($submission))
                ->where('comment_type',Comment::COMMENT_TYPE_OFFICIAL)->where('status',1)->first();
            $sourceData['official_reply'] = '';
            if ($comment) {
                $sourceData['official_reply'] = [
                    'author' => '官方回复',
                    'content'=>$comment->content,
                    'created_at' => $comment->created_at->diffForHumans()
                ];
            }
        }

        $item = [
            'id' => $submission->id,
            'title' => $title,
            'top' => $top,
            'user'  => [
                'id'    => $submission->hide?'':$submission->user->id ,
                'uuid'  => $submission->hide?'':$submission->user->uuid,
                'name'  => $submission->hide?'匿名':$submission->user->name,
                'is_expert' => $submission->hide?0:$submission->user->is_expert,
                'avatar'=> $submission->hide?config('image.user_default_avatar'):$submission->user->avatar
            ],
            'feed'  => $sourceData,
            'url'   => $url,
            'feed_type'  => $feed_type,
            'created_at' => $submission->created_at->diffForHumans()
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
        $shareNumber = $this->share_number;
        $commentSupports = $this->comments()->sum('supports');
        $views = $this->views;
        //如果是原创文章，权重高一点，默认给100阅读
        /*if ($this->type == 'article') {
            $views += 100;
        }
        $Qscore = $this->upvotes-$this->downvotes;
        $Ascores = $commentSupports + $this->collections + $shareNumber;
        $rate =  hotRate($views,$this->comments_number?:1, $Qscore, $Ascores,$this->created_at,$this->updated_at);*/
        $rate = $views/10 + $commentSupports + $this->comments_number * 2 + $this->collections + $shareNumber * 3 + $this->upvotes-$this->downvotes;
        $rate = (int) $rate;
        $this->rate = date('Ymd',strtotime($this->created_at)).(sprintf('%08s',$rate));
        $this->save();
        $recommendRead = RecommendRead::where('source_id',$this->id)->where('source_type',Submission::class)->first();
        if ($recommendRead) {
            $recommendRead->rate = $this->rate;
            $recommendRead->save();
        }
    }

    public function updateLinkImage($imgUrl) {
        if ($this->type == 'link') {
            $data = $this->data;
            $data['img'] = $imgUrl;
            $this->data = $data;
            $this->save();
        }
    }

    //设置关键词标签
    public function setKeywordTags() {
        try {
            $content = '';
            if (isset($this->data['domain']) && $this->data['domain'] == 'mp.weixin.qq.com') {
                $content = getWechatUrlBodyText($this->data['url']);
                $content = $this->title.';'.$content;
                $keywords = array_column(BosonNLPService::instance()->keywords($content),1);
            } elseif ($this->type == 'article') {
                $content = strip_tags($this->title).';'.QuillLogic::parseText($this->data['description']);
                $keywords = array_column(BosonNLPService::instance()->keywords($content),1);
            } elseif ($this->type == 'text') {
                $content = strip_tags($this->title);
                $keywords = array_column(BosonNLPService::instance()->keywords($content),1);
            } elseif ($this->type == 'review') {
                $content = '';
                $keywords = $this->tags->pluck('name')->toArray();
            } else {
                $parse_url = parse_url($this->data['url']);
                $gfw_urls = RateLimiter::instance()->sMembers('gfw_urls');
                if (isset($parse_url['host']) && in_array($parse_url['host'],$gfw_urls)) {
                    $html = curlShadowsocks($this->data['url']);
                    $ql = QueryList::getInstance();
                    $ql->setHtml($html);
                } else {
                    $ql = QueryList::get($this->data['url'],null,['timeout'=>15]);
                }
                $metas = $ql->find('meta[name=keywords]')->content;
                if ($metas) {
                    $content = $metas;
                    $metas = trim($metas);
                    $metas = str_replace('，',',',$metas);
                    $metas = str_replace('、',',',$metas);
                    $metas = str_replace(' ',',',$metas);
                    $keywords = explode(',',$metas);
                    if (count($keywords) == 1 && $keywords[0] == $metas) {
                        $keywords = explode(' ',$metas);
                    }
                }
                $description = strip_tags($this->title).';'.$ql->find('meta[name=description]')->content;
                if ($description) {
                    $content .= $description;
                    $keywords_description = array_column(BosonNLPService::instance()->keywords($description),1);
                    if (isset($keywords)) {
                        $keywords = array_merge($keywords,$keywords_description);
                    } else {
                        $keywords = $keywords_description;
                    }
                }
            }
            //和我们的产品进行一次匹配
            $tags = TagsLogic::getContentTags($content);
            //尝试自动分领域
            //$tags = array_merge($tags,TagsLogic::getRegionTags($content));
            $keywords = array_unique($keywords);
            foreach ($keywords as $keyword) {
                $keyword = formatHtml(formatKeyword($keyword));
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
            $tags = array_unique($tags);
            $data['keywords'] = implode(',',$tags);
            $this->data = $data;
            $this->save();
            Tag::multiAddByName(array_slice($tags,0,15),$this,1);
        } catch (ConnectException $e) {
            var_dump($this->data['url']);
            if (isset($parse_url)) {
                RateLimiter::instance()->sAdd('gfw_urls',$parse_url['host'],0);
            }
            dispatch((new UpdateSubmissionKeywords($this->id))->delay(Carbon::now()->addSeconds(300)));
        } catch (TooManyRedirectsException $e) {
            app('sentry')->captureException($e,$this->toArray());
        } catch (RequestException $e) {
            app('sentry')->captureException($e,$this->toArray());
        } catch (\Exception $e) {
            app('sentry')->captureException($e,$this->toArray());
            dispatch((new UpdateSubmissionKeywords($this->id))->delay(Carbon::now()->addSeconds(300)));
        }
    }

    public function updateRelatedProducts() {
        Cache::delete('submission_related_products_'.$this->id);
        $this->getRelatedProducts();
    }

    public function getRelatedProducts() {
        if ($this->type == 'review') {
            $tag = Tag::find($this->category_id);
            $related_tags = $tag->relationReviews(4);
        } else {
            $ignoreKeywords = Config::get('inwehub.ignore_product_keywords');
            $related_tags = Cache::get('submission_related_products_'.$this->id);
            if ($related_tags === null && isset($this->data['keywords'])) {
                $used = [];
                $keywords = explode(',',$this->data['keywords']);
                $tagNames = $this->tags->pluck('name')->toArray();
                $tagNames = array_unique(array_merge($tagNames,$keywords));
                $related_tags = [];
                foreach ($tagNames as $keyword) {
                    if (in_array($keyword,$ignoreKeywords)) continue;
                    $rels = Tag::where('name',$keyword)->get();
                    foreach ($rels as $rel) {
                        if (isset($used[$rel->id])) continue;
                        $tagRel = TagCategoryRel::where('tag_id',$rel->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
                        if ($tagRel) {
                            $info = Tag::getReviewInfo($rel->id);
                            $related_tags[] = [
                                'id' => $rel->id,
                                'name' => $rel->name,
                                'logo' => $rel->logo,
                                'review_count' => $info['review_count'],
                                'review_average_rate' => $info['review_average_rate']
                            ];
                            $used[$rel->id] = $rel->id;
                            if (count($related_tags) >= 4) break;
                        }
                    }
                    if (count($related_tags) >= 4) break;
                }
                if (count($related_tags) < 4) {
                    foreach ($tagNames as $keyword) {
                        if (in_array($keyword,$ignoreKeywords)) continue;
                        $rels = Tag::where('name','like','%'.$keyword.'%')->orderBy('reviews','desc')->take(10)->get();
                        foreach ($rels as $rel) {
                            if (!in_array($rel->id,$used)) {
                                $tagRel = TagCategoryRel::where('tag_id',$rel->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
                                if ($tagRel) {
                                    $info = Tag::getReviewInfo($rel->id);
                                    $related_tags[] = [
                                        'id' => $rel->id,
                                        'name' => $rel->name,
                                        'logo' => $rel->logo,
                                        'review_count' => $info['review_count'],
                                        'review_average_rate' => $info['review_average_rate']
                                    ];
                                    $used[$rel->id] = $rel->id;
                                    if (count($related_tags) >= 4) break;
                                }
                            }
                        }
                        if (count($related_tags) >= 4) break;
                    }
                }
                Cache::forever('submission_related_products_'.$this->id,$related_tags);
            }
        }
        return $related_tags?:[];
    }

}