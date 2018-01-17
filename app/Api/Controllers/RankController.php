<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Attention;
use App\Models\Comment;
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
        return self::createJsonData(true,$info);
    }

    //用户贡献榜
    public function userContribution(Request $request)
    {
        $userDatas = UserData::orderBy('coins','desc')->take(20)->get();
        $loginUser = $request->user();
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
        $users = User::selectRaw('count(*) as total,rc_uid')->groupBy('rc_uid')->orderBy('total','desc')->take(20)->get();
        $loginUser = $request->user();
        $data = [];
        $rank = 0;
        foreach ($users as $user) {
            if (empty($user->rc_uid)) continue;
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
                'uuid'    => $rcUser->uuid,
                'user_name' => $rcUser->name,
                'is_expert' => $rcUser->is_expert,
                'invited_users'     => $user->total,
                'is_followed' => $is_followed,
                'user_avatar_url' => $rcUser->avatar
            ];
        }
        return self::createJsonData(true,$data);
    }

    //用户成长榜
    public function userGrowth(Request $request)
    {
        $userDatas = UserData::orderBy('credits','desc')->take(20)->get();
        $loginUser = $request->user();
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
                'credits'     => $userData->credits,
                'is_followed' => $is_followed,
                'user_avatar_url' => $userData->user->avatar
            ];
        }
        return self::createJsonData(true,$data);
    }
}
