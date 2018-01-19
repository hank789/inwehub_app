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

        $info['show_rank'] = $this->checkTankLimit($user);
        return self::createJsonData(true,$info);
    }

    //用户贡献榜
    public function userContribution(Request $request)
    {
        $loginUser = $request->user();
        if ($this->checkTankLimit($loginUser) == false) throw new ApiException(ApiException::ACTIVITY_RANK_TIME_LIMIT);
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
        $users = User::selectRaw('count(*) as total,rc_uid')->groupBy('rc_uid')->orderBy('total','desc')->take(40)->get();
        $loginUser = $request->user();
        $data = [];
        $rank = 0;
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
            if ($rank >= 20) break;
        }
        usort($data,function ($a,$b) {
            if ($a['invited_users'] == $b['invited_users']) {
                if ($a['coins'] == $b['coins']) return 0;
                return $a['coins'] > $b['coins'] ? -1:1;
            }
        });
        foreach ($data as $key=>&$item) {
            $item['rank'] = $key+1;
        }
        return self::createJsonData(true,$data);
    }

    //用户成长榜
    public function userGrowth(Request $request)
    {
        $userDatas = UserData::whereNotIn('user_id',getSystemUids())->orderBy('credits','desc')->take(20)->get();
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

    protected function checkTankLimit($user){
        return config('app.env') == 'production' ? ((time()>=strtotime('2018-01-22 00:00:01') || in_array($user->id,getSystemUids()))?true:false):true;
    }
}
