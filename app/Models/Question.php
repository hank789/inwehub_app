<?php

namespace App\Models;

use App\Jobs\Question\ConfirmOvertimeAlertSystem;
use App\Logic\TagsLogic;
use App\Models\Feed\Feed;
use App\Models\Pay\Order;
use App\Models\Relations\BelongsToCategoryTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyDoingsTrait;
use App\Models\Relations\MorphManyOrdersTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BosonNLPService;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * App\Models\Question
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property int $price
 * @property bool $hide
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property int $views
 * @property int $followers
 * @property int $collections
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property bool $device
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionInvitation[] $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCollections($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereComments($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereDevice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereFollowers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereHide($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question wherePrice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereViews($value)
 * @mixin \Eloquent
 * @property int $question_type
 * @property int $is_recommend
 * @property int $is_hot
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Doing[] $doings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pay\Order[] $orders
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereIsHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereIsRecommend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereQuestionType($value)
 * @property array $data
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereData($value)
 * @property float $rate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereRate($value)
 * @property float $hot_rate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereHotRate($value)
 */
class Question extends Model
{
    use BelongsToUserTrait,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToCategoryTrait, MorphManyDoingsTrait, MorphManyOrdersTrait;
    protected $table = 'questions';
    protected $fillable = ['title', 'user_id','category_id','rate','hot_rate','question_type','description','tags','price','hide','status','device','data'];



    //提问设备，1为IOS，2为安卓，3为网页，4为微信小程序

    //question_type，1为定向付费提问，2为悬赏问答


    protected $casts = [
        'data' => 'json'
    ];

    public static function boot()
    {
        parent::boot();

        /*监听问题创建*/
        static::creating(function($question){
            /*开启问题审核状态检查*/
            if(Setting()->get('verify_question')==1){
                $question->status = 0;
            }


        });

        static::saved(function($question){
            if(Setting()->get('xunsearch_open',0) == 1) {
                App::offsetGet('search')->update($question);
            }
        });


        static::deleted(function($question){

            /*删除回答数据*/
            Answer::where("question_id","=",$question->id)->delete();

            UserData::where("user_id","=",$question->user_id)->where("questions",">",0)->decrement('questions');
            /*删除问题评论*/
            Comment::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除动态*/
            Doing::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除问题关注*/
            Attention::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除标签关联*/
            Taggable::where('taggable_type','=',get_class($question))->where('taggable_id','=',$question->id)->delete();

            /*删除问题邀请*/
            QuestionInvitation::where('question_id','=',$question->id)->delete();

            /*删除问题收藏*/
            Collection::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();
            //删除动态
            Feed::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();
            Task::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            if(Setting()->get('xunsearch_open',0) == 1) {
                App::offsetGet('search')->delete($question);
            }

        });
    }

    public function statusFormatDescription($user_id) {
        if ($this->question_type == 1) {
            $question_invitation = QuestionInvitation::where('question_id','=',$this->id)->first();
            if ($this->status < 6) {
                if ($this->user_id == $user_id) {
                    return '正在等待'.($question_invitation?$question_invitation->user->name:'').'回答';
                } else {
                    return '请于'.date('Y-m-d H:i',strtotime($this->created_at.' +48 hours')).'前回答，超时则提问失效。';
                }
            }
            if ($this->status == 6) {
                return '已回答';
            }
            if ($this->status == 9) return '对方未响应，问题已被关闭，'.$this->price.'元已自动退回。';
        }
        if ($this->status == 8) return '已采纳';
        if ($this->status == 7) return '已点评';
        if ($this->status == 9) return '24小时内没有回答者，问题已关闭，'.$this->price.'元将自动退回。';
        $description = '';
        //提问者
        if ($this->user_id == $user_id) {
            //悬赏还未结束
            if (strtotime($this->created_at.' +96 hours') > time()) {
                $description = '请于'.date('Y-m-d H:i',strtotime($this->created_at.' +96 hours')).'前采纳最佳回答，悬赏会支付给该回答者。';
            } else {
                $description = '您的采纳已延期，请尽快采纳最佳回答。';
            }
        } else {
            if (strtotime($this->created_at.' +96 hours') > time()) {
                $description = '最佳回答将于'.date('Y-m-d H:i',strtotime($this->created_at.' +96 hours')).'前采纳，悬赏会支付给该回答者。';
            } else {
                $description = '提问者正在采纳最佳回答，悬赏会支付给该回答者。';
            }
            if ($this->answers <=0 ) $description = '正在等待回答者回答';
        }
        return $description;
    }

    public function statusShortTip($user_id) {
        $description = '';
        switch ($this->status){
            case 0:
                $description = '未发布';
                break;
            case 1:
            case 2:
            case 4:
            case 5:
                $description = '悬赏中';
                if ($this->question_type == 1) $description = '待回答';
                break;
            case 6:
                $description = '悬赏中';
                if ($this->question_type == 1) $description = '待点评';
                break;
            case 3:
                $description = '问题已关闭';
                break;
            case 7:
                $description = '已点评';
                break;
            case 8:
                //已采纳
                $description = '已采纳';
                break;
            case 9:
                //退款并关闭
                $description = '已关闭';
                break;
        }
        return $description;
    }

    public function statusHumanDescription($user_id){
        $description = '';
        switch ($this->status){
            case 0:
                $description = '未发布';
                break;
            case 1:
                $description = '已受理';
                break;
            case 2:
                $description = '已匹配';
                break;
            case 3:
                $description = '问题已关闭';
                break;
            case 4:
                $description = '待回答';
                break;
            case 5:
                $description = '已受理';
                break;
            case 6:
                $description = '已回答';
                break;
            case 7:
                $description = '已点评';
                break;
            case 8:
                //已采纳
                $description = '已采纳';
                break;
            case 9:
                //退款并关闭
                $description = '已关闭';
                break;
        }
        return $description;
    }

    public function formatTimeline(){
        $doings = $this->doings()->orderBy('id','asc')->get();
        $timeline = [];
        $is_find_expert = false;
        foreach($doings as $doing){
            $title = '';
            $description = '';
            $is_finish = 0;
            switch($doing->action){
                case 'question_submit':
                    $title = '提交成功';
                    $description = '叮咚,您的问题提交成功啦!';
                    break;
                case 'question_process':
                    $title = '受理成功';
                    $description = '平台已受理,正快马加鞭为您寻找匹配的专家!';
                    break;
                case 'question_invite_answer_confirming':
                case 'question_answer_confirming':
                    if($is_find_expert) break;
                    $title = '匹配成功';
                    $description = '专家已经找到啦,就等他再确认一下!';
                    $is_find_expert = true;
                    break;
                case 'question_answer_confirmed':
                    $title = '确认成功';
                    $description = '专家'.$doing->user->name.'将义不容辞的为您答疑解惑!';
                    break;
                case 'question_answered':
                    $title = '回答成功';
                    $description = '专家'.$doing->user->name.'干脆利落的回答了您的提问!';
                    $is_finish = 1;
                    break;
                case 'question_answer_rejected':
                    break;
            }
            if($title){
                $timeline[] = [
                    'title' => $title,
                    'description' => $description,
                    'is_finish' => $is_finish,
                    'created_at' => (string)$doing->created_at
                ];
            }
        }
        return $timeline;
    }

    public function formatListItem() {
        $question = $this;
        $item = [
            'id' => $question->id,
            'question_type' => $question->question_type,
            'question_user_avatar' => $question->hide?config('image.user_default_avatar'):$question->user->avatar,
            'question_user_is_expert' => $question->hide?0:$question->user->is_expert,
            'question_user_name' => $question->hide?'匿名':$question->user->name,
            'price'      => $question->price,
            'description'  => $question->title,
            'tags' => $question->tags()->wherePivot('is_display',1)->get()->toArray(),
            'status' => $question->status,
            'created_at' => $question->created_at->diffForHumans(),
        ];
        if($question->question_type == 1){
            $item['comment_number'] = 0;
            $item['average_rate'] = 0;
            $item['support_number'] = 0;
            $item['status_description'] = $question->price.'元';
            $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            if ($bestAnswer) {
                $item['comment_number'] = $bestAnswer->comments;
                $item['average_rate'] = $bestAnswer->getFeedbackRate();
                $item['support_number'] = $bestAnswer->supports;
            }
        } else {
            $item['answer_number'] = $question->answers;
            $item['follow_number'] = $question->followers;
            $item['status_description'] = $question->price.'元悬赏'.($question->status <= 7 ? '中':($question->status==8?',已采纳':',已关闭'));
        }
        return $item;
    }


    //已邀请
    public function invitedAnswer(){
        //只有状态是待分配和已拒绝时才要更改状态
        if($this->status != 4 && $this->status != 6 && $this->status != 7){
            if($this->status != 2) {
                $overtime = Setting()->get('alert_minute_operator_question_unconfirm',10);
                dispatch((new ConfirmOvertimeAlertSystem($this->id,$overtime))->delay(Carbon::now()->addMinutes($overtime)));
            }
            $this->status = 2;
            return $this->save();
        }
    }

    //已确认待回答
    public function confirmedAnswer(){
        if($this->status != 6 && $this->status != 7){
            $this->status = 4;
            return $this->save();
        }
    }

    //已拒绝
    public function rejectAnswer(){
        if($this->status != 4 && $this->status != 6 && $this->status != 7){
            $this->status = 5;
            return $this->save();
        }
    }

    //已回答
    public function answered()
    {
        if($this->status < 6){
            $this->status = 6;
            return $this->save();
        }
    }

    /*获取相关问题*/
    public static function correlations($tagIds,$size=6)
    {
        $questions = self::whereHas('tags', function($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })->orderBy('created_at','DESC')->take($size)->get();
        return $questions;
    }

    /*获取相关问题*/
    public static function correlationsPage($tagIds,$pageSize=10,$questionType='',array $ignoreUsers=[], array $ignoreQuestions = [])
    {
        $query = self::whereHas('tags', function($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds)->whereNotIn('status',[2,3,4,5,9]);
        })->orderBy('answers','ASC')->orderBy('created_at','DESC');
        if ($questionType) {
            $query = $query->where('question_type',$questionType);
        }
        if ($ignoreUsers) {
            $query = $query->whereNotIn('user_id',$ignoreUsers);
        }
        if ($ignoreQuestions) {
            $query = $query->whereNotIn('id',$ignoreQuestions);
        }
        return $query->simplePaginate($pageSize);
    }




    /*热门问题*/
    public static function hottest($categoryId = 0,$pageSize=20)
    {
        $query = self::with('user');
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('views','DESC')->orderBy('answers','DESC')->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;

    }

    /*最新问题*/
    public static function newest($categoryId=0 , $pageSize=20)
    {
        $query = self::with('user');
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*未回答的*/
    public static function unAnswered($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->where('answers','=',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*悬赏问题*/
    public static function reward($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->where('price','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*最近热门问题*/
    public static function recent($pageSize=10,$questionType='',array $ignoreUsers=[])
    {
        $query = self::whereNotIn('status',[2,3,4,5,9])->orderBy('answers','ASC')->orderBy('created_at','DESC');
        if ($questionType) {
            $query = $query->where('question_type',$questionType);
        }
        if ($ignoreUsers) {
            $query = $query->whereNotIn('user_id',$ignoreUsers);
        }
        return $query->simplePaginate($pageSize);
    }

    /*是否已经邀请用户回答了*/
    public function isInvited($toUserId,$fromUserId){
        return $this->invitations()->where("user_id","=",$toUserId)->where("from_user_id","=",$fromUserId)->count();
    }

    /*问题搜索*/
    public static function search($word)
    {
        $list = self::where('title','like',"%$word%");
        return $list;
    }


    /*问题所有回答*/
    public function answers()
    {
        return $this->hasMany('App\Models\Answer','question_id');
    }


    /*问题所有邀请*/
    public function invitations()
    {
        return $this->hasMany('App\Models\QuestionInvitation','question_id');
    }

    public function getFormatTitle() {
        switch ($this->question_type) {
            case 1:
                //专业回答
                return '付费咨询 | '.$this->title;
                break;
            case 2:
                return '悬赏问答 | '.$this->title;
                break;
        }
        return $this->title;
    }

    //计算排名积分
    public function calculationRate(){
        $views = $this->answers()->sum('views') + $this->views;
        $answers = $this->answers;
        $supports = $this->answers()->sum('supports');
        $downvotes = $this->answers()->sum('downvotes');
        $bestAnswer = $this->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();
        $stars = 0;
        if ($bestAnswer) {
            $stars = $bestAnswer->feedbacks()->sum('star');
        }
        $commentSupports = $this->comments()->sum('supports');
        $y = $this->answers()->sum('pay_for_views') + 1;

        $rate =  hotRate($views,$answers,$supports-$downvotes,$commentSupports + $stars+$this->followers+$this->collections+$y,$this->created_at,$this->updated_at);
        $this->rate = $rate;
        //计算热门排名
        if ($this->question_type == 1) {
            //专业问答
            $this->hot_rate = $y + 3 * $supports;
        } else {
            //互动问答
            $this->hot_rate = $this->followers + $answers + $supports + 1;
        }
        $this->save();
        $recommendRead = RecommendRead::where('source_id',$this->id)->where('source_type',Question::class)->first();
        if ($recommendRead) {
            $recommendRead->rate = $this->rate + $recommendRead->getRateWeight();
            $recommendRead->save();
        }
    }

    //设置关键词标签
    public function setKeywordTags() {
        try {
            $content = strip_tags($this->title);
            $keywords = array_column(BosonNLPService::instance()->keywords($content),1);
            $tags = TagsLogic::getContentTags($content);
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
            Tag::multiAddByName(array_slice($tags,0,10),$this,1);
        } catch (\Exception $e) {
            \Log::info('setKeywordTagsError',$this->toArray());
            app('sentry')->captureException($e,$this->toArray());
        }
    }

    public function getRelatedProducts() {
        $related_tags = Cache::get('question_related_products_'.$this->id);
        if ($related_tags === null && isset($this->data['keywords'])) {
            $ignoreKeywords = Config::get('inwehub.ignore_product_keywords');
            $keywords = explode(',', $this->data['keywords']);
            $tagNames = $this->tags->pluck('name')->toArray();
            $tagNames = array_unique(array_merge($tagNames,$keywords));
            $related_tags = [];
            $used = [];
            foreach ($tagNames as $keyword) {
                if (in_array($keyword,$ignoreKeywords)) continue;
                $rels = Tag::where('name', $keyword)->get();
                foreach ($rels as $rel) {
                    if (isset($used[$rel->id])) continue;
                    $tagRel = TagCategoryRel::where('tag_id', $rel->id)->where('type', TagCategoryRel::TYPE_REVIEW)->where('status', 1)->first();
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
                    $ignoreKeywords = ['科技','信息','公司','有限','科技公司','有限公司','信息科技'];
                    if (in_array($keyword,$ignoreKeywords)) continue;
                    $rels = Tag::where('name', 'like', '%' . $keyword . '%')->orderBy('reviews', 'desc')->take(10)->get();
                    foreach ($rels as $rel) {
                        if (!in_array($rel->id, $used)) {
                            $tagRel = TagCategoryRel::where('tag_id', $rel->id)->where('type', TagCategoryRel::TYPE_REVIEW)->where('status', 1)->first();
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
            Cache::forever('question_related_products_' . $this->id, $related_tags);
        }
        return $related_tags;
    }


}
