<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Logic\QuestionLogic;
use App\Logic\TaskLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Pay\Order;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class QuestionController extends Controller {


    protected function searchNotify($user,$searchWord,$typeName='',$searchResult=''){
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.$typeName.'搜索['.$searchWord.']'.$searchResult));
        RateLimiter::instance()->hIncrBy('search-word-count',$searchWord,1);
        RateLimiter::instance()->hIncrBy('search-user-count-'.$user->id,$searchWord,1);
    }
    public function store(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'title' => 'required|max:500',
            'hide'=> 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $data = [
            'user_id' => $user->id,
            'category_id' => 20,
            'title' => $request->input('title'),
            'question_type' => $request->input('question_type',2),
            'price' => abs($request->input('price')),
            'hide' => $request->input('hide')?1:0,
            'status'    => 1,
            'device'       => intval($request->input('device')),
            'rate'          => firstRate(),
            'data'          => []
        ];
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'questions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $data['data']['img'] = [$img_url];
            }
        }
        $question = Question::create($data);

        /*添加标签*/
        $tagString = $request->input('tags');
        Tag::multiSaveByIds($tagString,$question);
        //记录动态
        $this->doing($question->user_id,'free_question_submit',get_class($question),$question->id,$question->title,'');
        $user->userData()->increment('questions');
        UserTag::multiIncrement($user->id,$question->tags()->get(),'questions');
        //匿名互动提问的不加分
        //首次提问
        if($user->userData->questions == 1){
            if ($question->question_type == 1) {
                $credit_key = Credit::KEY_FIRST_ASK;
            } else {
                $credit_key = Credit::KEY_FIRST_COMMUNITY_ASK;
            }
            TaskLogic::finishTask('newbie_ask',0,'newbie_ask',[$user->id]);
        } else {
            if ($question->question_type == 1) {
                $credit_key = Credit::KEY_ASK;
            } else {
                $credit_key = Credit::KEY_COMMUNITY_ASK;
            }
        }
        if ($question->question_type == 1 || ($question->question_type == 2 && $question->hide==0)) {
            $this->credit($user->id,$credit_key,$question->id,$question->title);
        }
        return self::createJsonData(true,['id'=>$question->id]);
    }

    /**
     * 问题详情查看
     */
    public function info(Request $request,JWTAuth $JWTAuth)
    {

        $id = $request->input('id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }

        $is_self = $user->id == $question->user_id;
        $is_answer_author = false;
        $is_pay_for_view = false;

        /*已解决问题*/
        $bestAnswer = [];
        if($question->status >= 6 ){
            $bestAnswer = $question->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();
        }

        //已经回答的问题其他人都能看,没回答的问题只有邀请者才能看(付费专业问答)
        if ($question->question_type == 1 && $question->status < 6) {
            //问题作者或邀请者才能看
            $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$user->id)->first();
            if(empty($question_invitation) && !$is_self){
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            //已经拒绝了
            if($question_invitation && $question_invitation->status == QuestionInvitation::STATUS_REJECTED){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_REJECTED);
            }
            //虽然邀请他回答了,但是已被其他人回答了
            if($user->id != $question->user->id){
                $question_invitation_confirmed = QuestionInvitation::where('question_id','=',$question->id)->whereIn('status',[QuestionInvitation::STATUS_ANSWERED,QuestionInvitation::STATUS_CONFIRMED])->first();
                if($question_invitation_confirmed && $question_invitation_confirmed->user_id != $user->id) {
                    throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
                }
            }
        }


        $answers_data = [];
        $promise_answer_time = '';

        if($bestAnswer){
            //是否回答者
            if ($bestAnswer->user_id == $user->id) {
                $is_answer_author = true;
            }
            //是否已经付过围观费
            $payOrder = $bestAnswer->orders()->where('user_id',$user->id)->where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->first();
            if ($payOrder) {
                $is_pay_for_view = true;
            }
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($bestAnswer->user))->where('source_id','=',$bestAnswer->user_id)->first();

            $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->first();
            //回答查看数增加
            //if ($is_self || $is_answer_author || $is_pay_for_view) $bestAnswer->increment('views');
            $bestAnswer->increment('views');
            $support_uids = Support::where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->take(20)->pluck('user_id');
            $supporters = [];
            if ($support_uids) {
                foreach ($support_uids as $support_uid) {
                    $supporter = User::find($support_uid);
                    $supporters[] = [
                        'name' => $supporter->name,
                        'uuid' => $supporter->uuid
                    ];
                }
            }
            $answers_data[] = [
                'id' => $bestAnswer->id,
                'user_id' => $bestAnswer->user_id,
                'uuid' => $bestAnswer->user->uuid,
                'user_name' => $bestAnswer->user->name,
                'user_avatar_url' => $bestAnswer->user->avatar,
                'title' => $bestAnswer->user->title,
                'company' => $bestAnswer->user->company,
                'is_expert' => $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'content' => ($is_self || $is_answer_author || $is_pay_for_view) ? $bestAnswer->content : '',
                'promise_time' => $bestAnswer->promise_time,
                'is_followed' => $attention?1:0,
                'is_supported' => $support?1:0,
                'support_number' => $bestAnswer->supports,
                'view_number'    => $bestAnswer->views,
                'comment_number' => $bestAnswer->comments,
                'average_rate'   => $bestAnswer->getFeedbackRate(),
                'created_at' => (string)$bestAnswer->created_at,
                'supporter_list' => $supporters
            ];
            $promise_answer_time = $bestAnswer->promise_time;
        }else {
            $promise_answer = $question->answers()->where('status',Answer::ANSWER_STATUS_PROMISE)->orderBy('id','desc')->get()->last();
            if ($promise_answer){
                $promise_answer_time = $promise_answer->promise_time;
            }
        }
        $question->increment('views');


        $attention_question_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->first();

        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'uuid'    => $question->hide ? '':$question->user->uuid,
            'question_type' => $question->question_type,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
            'title' => $question->hide ? '保密' : $question->user->title,
            'company' => $question->hide ? '保密' : $question->user->company,
            'is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
            'is_followed' => $question->hide ? 0 : ($attention_question_user?1:0),
            'user_description' => $question->hide ? '':$question->user->description,
            'data' => $question->data,
            'description'  => $question->title,
            'tags' => $question->tags()->where('category_id','!=',1)->get()->toArray(),
            'hide' => $question->hide,
            'price' => $question->price,
            'status' => $question->status,
            'status_description' => $question->statusHumanDescription($user->id),
            'promise_answer_time' => $promise_answer_time,
            'question_answer_num' => $question->answers,
            'question_follow_num' => $question->followers,
            'views' => $question->views,
            'created_at' => (string)$question->created_at
        ];


        $timeline = $is_self && $question->question_type == 1 ? $question->formatTimeline() : [];

        //feedback
        $feedback_data = [];
        if($answers_data){
            $feedback = $bestAnswer->feedbacks()->where('user_id',$user->id)->orderBy('id','desc')->first();
            if(!empty($feedback)){
                $feedback_data = [
                    'answer_id' => $feedback->source_id,
                    'rate_star' => $feedback->star,
                    'description' => $feedback->content,
                    'create_time' => (string)$feedback->created_at
                ];
            }
        }
        $is_followed_question = 0;
        $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
        if ($attention_question) {
            $is_followed_question = 1;
        }

        $my_answer_id = 0;
        if ($question->question_type != 1) {
            $myAnswer = Answer::where('question_id',$id)->where('user_id',$user->id)->first();
            if ($myAnswer) $my_answer_id = $myAnswer->id;
        }
        $this->doing($user->id,$question->question_type == 1 ? Doing::ACTION_VIEW_PAY_QUESTION:Doing::ACTION_VIEW_FREE_QUESTION,get_class($question),$question->id,'查看问题');

        return self::createJsonData(true,[
            'is_followed_question'=>$is_followed_question,
            'my_answer_id' => $my_answer_id,
            'question'=>$question_data,
            'answers'=>$answers_data,
            'timeline'=>$timeline,
            'feedback'=>$feedback_data]);

    }

    public function addImage(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id' => 'required|integer',
            'image_file'=> 'required|image'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $data = $request->all();
        $question = Question::find($data['id']);
        if ($question->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = $question->data;
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'questions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $data['img'][] = $img_url;
            }
        }
        $question->data = $data;
        $question->save();
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function allList(Request $request,JWTAuth $JWTAuth){
        $orderBy = $request->input('order_by',2);//1最新，2最热，3综合，
        $query = Question::Where('question_type',2);
        $filter = $request->input('filter',0);
        $oauth = $JWTAuth->parseToken()->toUser();
        switch ($filter){
            case 1:
                //我的提问
                if ($oauth->user_id) {
                    $user = $oauth->user;
                } else {
                    $user = new \stdClass();
                    $user->id = 0;
                }
                $query = $query->where('user_id',$user->id);
                break;
        }
        $queryOrderBy = 'questions.rate';
        switch ($orderBy) {
            case 1:
                //最新
                $queryOrderBy = 'questions.updated_at';
                break;
            case 2:
                //最热
                $queryOrderBy = 'questions.hot_rate';
                break;
            case 3:
                //综合
                $queryOrderBy = 'questions.rate';
                break;
        }
        $questions = $query->orderBy($queryOrderBy,'desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $questions->toArray();
        $list = [];
        foreach($questions as $question){
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'tags' => $question->tags()->where('category_id','!=',1)->get()->toArray(),
                'question_user_name' => $question->hide ? '匿名' : $question->user->name,
                'question_user_avatar' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'question_user_is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0)
            ];
            if($question->question_type == 1){
                $item['comment_number'] = 0;
                $item['average_rate'] = 0;
                $item['support_number'] = 0;
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                if ($bestAnswer) {
                    $item['comment_number'] = $bestAnswer->comments;
                    $item['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['support_number'] = $bestAnswer->supports;
                }
            } else {
                $item['answer_number'] = $question->answers;
                $item['follow_number'] = $question->followers;
            }
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    public function loadAnswer(Request $request){
        $validateRules = [
            'question_id' => 'required|integer'
        ];
        $this->validate($request,$validateRules);

        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $question_id = $request->input('question_id',0);
        $question = Question::find($question_id);
        $user = $request->user();

        $query = $question->comments()->where('status',1);
        if (!$question->is_public && $user->id != $question->user_id){
            $query = $query->where('user_id',$user->id);
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $comments = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach($comments as $comment){
            $list[] = [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_avatar_url' => $comment->user->getAvatarUrl(),
                'user_id'   => $comment->user_id,
                'user_name' => $comment->user->name,
                'created_at' => (string)$comment->created_at
            ];
        }
        return self::createJsonData(true,$list);
    }

    public function follow(Request $request,JWTAuth $JWTAuth) {
        $validateRules = [
            'question_id' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        $question_id = $request->input('question_id');
        if ($oauth->user_id) {
            $loginUser = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $source  = Question::findOrFail($question_id);
        $subject = $source->title;
        if (RateLimiter::instance()->increase('follow:question',$loginUser->id,10,5)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        /*再次关注相当于是取消关注*/
        $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($source))->where('source_id','=',$question_id)->first();
        if($attention){
            $attention->delete();
            $source->decrement('followers');
            $fields = [];
            $fields[] = [
                'title' => '标题',
                'value' => $source->title
            ];
            $fields[] = [
                'title' => '地址',
                'value' => route('ask.question.detail',['id'=>$source->id])
            ];
            event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']取消关注了问题',$fields));
            return self::createJsonData(true,['tip'=>'取消关注成功','type'=>'unfollow']);
        }

        $data = [
            'user_id'     => $loginUser->id,
            'source_id'   => $question_id,
            'source_type' => get_class($source),
        ];

        $attention = Attention::create($data);

        if($attention){
            $source->increment('followers');
            $fields = [];
            $fields[] = [
                'title' => '标题',
                'value' => $source->title
            ];
            $fields[] = [
                'title' => '地址',
                'value' => route('ask.question.detail',['id'=>$source->id])
            ];
            event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']关注了问题',$fields));
            //产生一条feed流
            if ($source->question_type == 2) {
                $feed_event = 'question_followed';
                $feed_target = $source->id.'_'.$loginUser->id;
                if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                    feed()
                        ->causedBy($loginUser)
                        ->performedOn($source)
                        ->tags($source->tags()->pluck('tag_id')->toArray())
                        ->log($loginUser->name.'关注了问答', Feed::FEED_TYPE_FOLLOW_FREE_QUESTION);
                    $this->credit($loginUser->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source));
                    if ($source->hide == 0) {
                        $this->credit($source->user_id,Credit::KEY_COMMUNITY_ASK_FOLLOWED,$attention->id,get_class($source));
                    }
                    QuestionLogic::calculationQuestionRate($source->id);
                }
            }
        }

        return self::createJsonData(true,['tip'=>'关注成功','type'=>'follow']);
    }

    public function search(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $questions = Question::search($request->input('search_word'))->where('question_type',2)->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($questions as $question) {
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'tags' => $question->tags()->where('category_id','!=',1)->get()->toArray()
            ];
            if($question->question_type == 1){
                $item['comment_number'] = 0;
                $item['average_rate'] = 0;
                $item['support_number'] = 0;
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                if ($bestAnswer) {
                    $item['comment_number'] = $bestAnswer->comments;
                    $item['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['support_number'] = $bestAnswer->supports;
                }
            } else {
                $item['answer_number'] = $question->answers;
                $item['follow_number'] = $question->followers;
            }
            $data[] = $item;
        }
        $return = $questions->toArray();
        $return['data'] = $data;
        $this->searchNotify($user,$request->input('search_word'),'在栏目[问答]',',搜索结果'.$questions->total());
        return self::createJsonData(true, $return);
    }

    //问题回答列表
    public function answerList(Request $request,JWTAuth $JWTAuth){
        $id = $request->input('question_id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $answers = $question->answers()->whereNull('adopted_at')->orderBy('supports','DESC')->orderBy('updated_at','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $answers->toArray();
        $return['data'] = [];
        foreach ($answers as $answer) {
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($answer->user))->where('source_id','=',$answer->user_id)->first();

            $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->first();

            $return['data'][] = [
                'id' => $answer->id,
                'user_id' => $answer->user_id,
                'uuid' => $answer->user->uuid,
                'user_name' => $answer->user->name,
                'user_avatar_url' => $answer->user->avatar,
                'title' => $answer->user->title,
                'company' => $answer->user->company,
                'is_expert' => $answer->user->userData->authentication_status == 1 ? 1 : 0,
                'content' => $answer->getContentHtml(),
                'promise_time' => $answer->promise_time,
                'is_followed' => $attention?1:0,
                'is_supported' => $support?1:0,
                'support_number' => $answer->supports,
                'view_number'    => $answer->views,
                'comment_number' => $answer->comments,
                'created_at' => (string)$answer->created_at
            ];
        }
        return self::createJsonData(true,$return);
    }
}