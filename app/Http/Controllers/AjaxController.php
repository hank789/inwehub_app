<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use App\Services\City\CityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{

    /**
     * 加载城市下拉项
     * @param $province_id 省份ID
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function loadCities($province_id)
    {

        $cities = CityData::getCityByProvince($province_id);
        $city_options = '';
        foreach($cities as $key => $city){
            $city_options .= '<option value="'.$key.'">'.$city.'</option>';
        }

        return response($city_options);

    }



    /*未读通知数目*/
    public function unreadNotifications()
    {
        $total = Notification::where('to_user_id','=',Auth()->user()->id)->where('is_read','=',0)->count();
        $response = '<span class="fa fa-bell-o fa-lg"></span>';
        if( $total > 0 ){
            if($total > 99){
                $total = '99+' ;
            }
            $response =  '<span class="fa fa-bell-o fa-lg"></span><span class="label label-danger">'.$total.'</span>';
        }

        return response($response);
    }


    public function unreadMessages()
    {
        $total = Message::where('to_user_id','=',Auth()->user()->id)->where('is_read','=',0)->where("to_deleted","<>",1)->where("from_deleted","<>",1)->count();
        $response = '<span class="fa fa-envelope-o fa-lg"></span>';
        if( $total > 0 ){
            if($total > 99){
                $total = '99+' ;
            }
            $response =  '<span class="fa fa-envelope-o fa-lg"></span><span class="label label-success">'.$total.'</span>';
        }

        return response($response);
    }


    public function loadTags(Request $request)
    {
        $word = $request->input('word');
        $tags = [];
        if( strlen($word) > 10 ){
            return response()->json($tags);
        }
        $tag_type = $request->input('type','all');

        $category_ids = '';
        $category_name = '';
        switch($tag_type){
            case 1:
                //问题分类
                $category_name = 'question';
                break;
            case 2:
                //拒绝分类
                $category_name = 'answer_reject';
                break;
            case 3:
                //行业领域
                $category_name = 'industry';
                break;
            case 4:
                //产品类型
                $category_name = 'product_type';
                break;
        }

        $query = Tag::where('name','like','%'.$word.'%');

        if($category_name){
            $category_ids = Category::where('slug','like',$category_name.'%')->pluck('id')->toArray();
            $query->whereIn('category_id',$category_ids);
        }

        $tags = $query->select('id',DB::raw('name as text'))->take(20)->get();

        return response()->json($tags->toArray());
    }



    public function loadUsers(Request $request)
    {
        $word = $request->input('word');

        $users = User::where('id','<>',$request->user()->id)->where('name','like',"%$word%")->take(10)->get();
        $users->map(function($user){
            $user->avatar = $user->getAvatarUrl();
            $user->coins = $user->userData->coins;
            $user->answers = $user->userData->answers;
            $user->followers = $user->userData->followers;
        });
        return response()->json($users->toArray());
    }


    public function loadInviteUsers(Request $request)
    {
        $questionId = $request->input('question_id',0);
        $question = Question::find($questionId);
        if(!$question){
            return $this->ajaxError(10004,'notFund');
        }
        $tags = $question->tags()->get();

        $tagIds = array_pluck($tags,"id");

        if(!$tagIds){
            return $this->ajaxError(10004,'noData');
        }

        $word = $request->input('word','');

        $is_inviter_must_expert = Setting()->get('is_inviter_must_expert',1);
        if(trim($word)){
            $users = User::where('id','<>',$request->user()->id)->where('name','like',"%$word%")->take(10)->get();
            $users->map(function($user) use($tagIds,$question) {
                $user->tag_name = '';
                $user->tag_answers = 0;
                $userTag = UserTag::where("user_id","=",$user->id)->whereIn("tag_id",$tagIds)->orderBy("answers","desc")->orderBy("created_at","desc")->first();
                if($userTag){
                    $tag = Tag::find($userTag->tag_id);
                    if($tag){
                        $user->tag_name = $tag->name;
                    }
                    $user->tag_answers = $userTag->answers;
                }
                $user->avatar = $user->getAvatarUrl();
                $user->url = route('auth.space.index',['user_id'=>$user->user_id]);
                $user->isInvited = 0;
                $user->isExpert = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
            });
        }else{

            $invitations = $question->invitations()->get();
            $invitedUserIds = array_pluck($invitations,'user_id');
            $userTags = UserTag::whereIn("tag_id",$tagIds)->whereNotIn("user_id",$invitedUserIds)->orderBy("answers","desc")->orderBy("supports","desc")->select("user_id","tag_id","answers","supports")->take(16)->groupBy("user_id")->get();
            $users = [];
            foreach($userTags as $userTag){
                $user = User::find($userTag->user_id);
                if(!$user){
                    continue;
                }
                $user->isExpert = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
                if($is_inviter_must_expert && $user->isExpert == 0) continue;

                $user->tag_name = '';
                $user->tag_answers = 0;
                $tag = Tag::find($userTag->tag_id);
                if($tag){
                    $user->tag_name = $tag->name;
                }
                $user->tag_answers = $userTag->answers;
                $user->avatar = $userTag->user->getAvatarUrl();
                $user->url = route('auth.space.index',['user_id'=>$userTag->user_id]);
                $user->isInvited = 0;

                $users[] = $user;
            }
        }

        return $this->ajaxSuccess($users);
    }





}
