<?php

namespace App\Api\Controllers;

use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Attention;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Support;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

class RankController extends Controller
{

    //获取用户简单数据
    public function userInfo(Request $request){
        $info = [];
        $user = $request->user();
        $info['user_name'] = $user->name;
        $info['user_level'] = $user->userData->user_level;
        $info['user_credits'] = $user->userData->credits;
        $info['user_coins'] = $user->userData->coins;
        $info['invited_users'] = User::where('rc_uid',$user->id)->count();
        $beginTime = date('Y-m-01 00:00:00');
        $endTime = date('Y-m-d 23:59:59',strtotime($beginTime.' +1 month -1 day'));
        $info['current_month_invited_users'] = User::where('rc_uid',$user->id)->whereBetween('created_at',[$beginTime,$endTime])->count();
        $info['current_month_user_upvotes'] = Support::where('refer_user_id',$user->id)->whereBetween('created_at',[$beginTime,$endTime])->count();
        $info['show_rank'] = $this->checkTankLimit($user);
        $info['is_expert'] = $user->is_expert;
        $info['user_avatar'] = $user->avatar;
        $info['user_uuid'] = $user->uuid;

        return self::createJsonData(true,$info);
    }

    //用户贡献榜
    public function userContribution(Request $request)
    {
        $loginUser = $request->user();
        if ($this->checkTankLimit($loginUser) == false) throw new ApiException(ApiException::ACTIVITY_RANK_TIME_LIMIT);
        event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']查看了贡献榜',[]));
        $userDatas = UserData::whereNotIn('user_id',getSystemUids())->orderBy('coins','desc')->take(20)->get();
        $data = [];
        foreach ($userDatas as $key=>$userData) {
            $is_followed = 0;
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($userData->user))->where('source_id','=',$userData->user_id)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $data[] = [
                'rank' => $key+1,
                'user_id' => $userData->user_id,
                'uuid'    => $userData->user->uuid,
                'user_name' => $userData->user->name,
                'is_expert' => $userData->user->is_expert,
                'coins'     => $userData->coins,
                'is_followed' => $is_followed,
                'user_avatar_url' => $userData->user->avatar
            ];
        }
        return self::createJsonData(true,$data);
    }

    //用户邀请榜
    public function userInvitation(Request $request)
    {
        $users = User::selectRaw('count(*) as total,rc_uid')->groupBy('rc_uid')->orderBy('total','desc')->take(60)->get();
        $loginUser = $request->user();
        $data = [];
        $rank = 0;
        event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']查看了邀请榜',[]));
        $systemUsers = User::whereIn('id',[329,269])->get();
        foreach ($systemUsers as $systemUser) {
            $rank ++;
            $is_followed = 0;
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($systemUser))->where('source_id','=',$systemUser->id)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $data[] = [
                'rank' => 1,
                'user_id' => $systemUser->id,
                'coins'   => $systemUser->userData->coins,
                'uuid'    => $systemUser->uuid,
                'user_name' => $systemUser->name,
                'is_expert' => $systemUser->is_expert,
                'invited_users'     => $systemUser->id == 329?13:10,
                'is_followed' => $is_followed,
                'user_avatar_url' => $systemUser->avatar
            ];
        }

        foreach ($users as $user) {
            if (empty($user->rc_uid)) continue;
            if (in_array($user->rc_uid,getSystemUids())) continue;
            $rank ++;
            $is_followed = 0;
            $rcUser = User::find($user->rc_uid);
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user))->where('source_id','=',$user->rc_uid)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $data[] = [
                'rank' => $rank,
                'user_id' => $user->rc_uid,
                'coins'   => $rcUser->userData->coins,
                'uuid'    => $rcUser->uuid,
                'user_name' => $rcUser->name,
                'is_expert' => $rcUser->is_expert,
                'invited_users'     => $user->total,
                'is_followed' => $is_followed,
                'user_avatar_url' => $rcUser->avatar
            ];
        }
        usort($data,function ($a,$b) {
            if ($a['invited_users'] == $b['invited_users']) {
                if ($a['coins'] == $b['coins']) return 0;
                return $a['coins'] > $b['coins'] ? -1:1;
            } elseif($a['invited_users'] > $b['invited_users']) {
                return -1;
            }
            return 1;
        });
        foreach ($data as $key=>&$item) {
            $item['rank'] = $key+1;
        }

        return self::createJsonData(true,array_slice($data,0,20));
    }

    //用户成长榜
    public function userGrowth(Request $request)
    {
        $beginTime = date('Y-m-01 00:00:00');
        $endTime = date('Y-m-d 23:59:59',strtotime($beginTime.' +1 month -1 day'));
        $userCredits = Credit::selectRaw('sum(credits) as total_credits,user_id')->whereNotIn('user_id',getSystemUids())->whereBetween('created_at',[$beginTime,$endTime])->groupBy('user_id')->orderBy('total_credits','desc')->take(20)->get();
        $loginUser = $request->user();
        event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']查看了成长榜',[]));
        $data = [];
        foreach ($userCredits as $key=>$userCredit) {
            $is_followed = 0;
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($userCredit->user))->where('source_id','=',$userCredit->user_id)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $data[] = [
                'rank' => $key+1,
                'user_id' => $userCredit->user_id,
                'uuid'    => $userCredit->user->uuid,
                'user_name' => $userCredit->user->name,
                'is_expert' => $userCredit->user->is_expert,
                'credits'     => $userCredit->total_credits,
                'is_followed' => $is_followed,
                'user_avatar_url' => $userCredit->user->avatar
            ];
        }
        return self::createJsonData(true,$data);
    }

    //用户本月获赞榜
    public function userUpvotes(Request $request){
        $beginTime = date('Y-m-01 00:00:00');
        $endTime = date('Y-m-d 23:59:59',strtotime($beginTime.' +1 month -1 day'));
        $loginUser = $request->user();
        $supports = Support::selectRaw('count(*) as total,refer_user_id')->whereNotIn('refer_user_id',getSystemUids())->whereBetween('created_at',[$beginTime,$endTime])->groupBy('refer_user_id')->orderBy('total','desc')->take(20)->get();
        event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']查看了点赞榜',[]));
        $data = [];
        foreach ($supports as $key=>$support) {
            $is_followed = 0;
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($loginUser))->where('source_id','=',$support->refer_user_id)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $refer_user = User::find($support->refer_user_id);
            $data[] = [
                'rank' => $key+1,
                'user_id' => $refer_user->id,
                'uuid'    => $refer_user->uuid,
                'user_name' => $refer_user->name,
                'is_expert' => $refer_user->is_expert,
                'upvotes'     => $support->total,
                'is_followed' => $is_followed,
                'coins'   => $refer_user->userData->coins,
                'user_avatar_url' => $refer_user->avatar
            ];
        }
        usort($data,function ($a,$b) {
            if ($a['upvotes'] == $b['upvotes']) {
                if ($a['coins'] == $b['coins']) return 0;
                return $a['coins'] > $b['coins'] ? -1:1;
            } elseif($a['upvotes'] > $b['upvotes']) {
                return -1;
            }
            return 1;
        });
        foreach ($data as $key=>&$item) {
            $item['rank'] = $key+1;
        }
        return self::createJsonData(true,$data);
    }

    protected function checkTankLimit($user){
        return config('app.env') == 'production' ? ((time()>=strtotime('2018-01-22 00:00:01') || in_array($user->id,getSystemUids()))?true:false):true;
    }
}
