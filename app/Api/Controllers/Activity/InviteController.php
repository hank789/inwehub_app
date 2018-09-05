<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Activity\Coupon;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\UserMoney;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/7/13 上午11:30
 * @email: hank.huiwang@gmail.com
 */

class InviteController extends Controller {

    //邀请注册活动介绍页
    public function registerIntroduce(Request $request){
        $user = $request->user();
        $invited_users = User::where('rc_uid',$user->id)->count();
        $user_money = UserMoney::find($user->id);
        $reward_money = $user_money->reward_money;
        $beginTime = date('Y-m-01 00:00:00');
        $endTime = date('Y-m-d 23:59:59',strtotime($beginTime.' +1 month -1 day'));
        $current_month_invited_users = User::where('rc_uid',$user->id)->whereBetween('created_at',[$beginTime,$endTime])->count();
        return self::createJsonData(true,[
            'user_name'=>$user->name,
            'user_mobile'=>$user->mobile,
            'user_avatar'=>$user->avatar,
            'user_is_expert' => $user->is_expert,
            'invited_users'=>$invited_users,
            'reward_money'=>$reward_money,
            'current_month_invited_users'=>$current_month_invited_users]);
    }

    //我邀请注册的好友列表
    public function myRegisterList(Request $request){
        $user = $request->user();
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = User::where('rc_uid', $user->id);

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $users = $query->orderBy('id','desc')
            ->simplePaginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach ($users as $user) {
            $paid_money = Order::where('user_id',$user->id)->where('status',Order::PAY_STATUS_SUCCESS)->sum('amount');
            $reward_money = MoneyLog::where('user_id',$user->id)
                ->where('source_type','App\Models\Answer')
                ->where('io',1)
                ->where('status',1)
                ->sum('change_money');

            $reward_money_fee = MoneyLog::where('user_id',$user->id)
                ->where('source_type','App\Models\Answer')
                ->where('io',-1)
                ->where('status',1)
                ->sum('change_money');
            $list[] = [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'user_name' => $user->name,
                'is_expert' => ($user->authentication && $user->authentication->status === 1) ? 1 : 0,
                'user_avatar_url' => $user->avatar,
                'register_at' => (string) $user->created_at,
                'paid_money'  => number_format($paid_money,2),
                'reward_money' => number_format($reward_money - $reward_money_fee,2)
            ];
        }

        return self::createJsonData(true,$list);
    }

    //获取邀请者信息
    public function getInviterInfo(Request $request){
        $validateRules = [
            'rc_code' => 'required',
        ];
        $this->validate($request,$validateRules);
        $rc_code = $request->input('rc_code');
        $user = User::where('rc_code',$rc_code)->first();
        if (!$user) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        return self::createJsonData(true,['inviter_name'=>$user->name,'inviter_avatar'=>$user->avatar]);

    }


    //邀请规则
    public function inviteRules(){
        $data = [
            'html' => Setting()->get('setting_invite_rules','')
        ];

        return self::createJsonData(true,$data);
    }
}