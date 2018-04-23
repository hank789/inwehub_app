<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 2017/3/4
 * Time: 上午12:01
 */

namespace App\Http\Controllers\Admin;


use App\Models\Credit;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\Frontend\System\Credit as CreditEvent;
use App\Notifications\MoneyLog as MoneyLogNotify;

class CreditController extends AdminController
{

    public function index(Request $request){
        $query = Credit::query();
        $filter =  $request->all();
        $query->where(function($query){
            $query->where('credits','<>',0)
                  ->orWhere('coins','<>',0);
        });
        /*充值人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }
        /*行为过滤*/
        if( isset($filter['action']) &&  $filter['action'] ){
            $query->where('action','=',$filter['action']);
        }
        /*时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }

        $credits = $query->orderBy('created_at','desc')->paginate(20);

        return view('admin.credit.index')->with(compact('credits','filter'));
    }

    public function create(){
        return view('admin.credit.create');
    }

    public function store(Request $request){
        $validateRule = [
            'user_id' => 'required|integer',
            'action' => 'required|in:reward_user,punish_user',
            'coins' => 'required|integer|min:0',
            'credits' => 'required|integer|min:0',
            'money' => 'required|integer|min:0',
            'source_subject' => 'required'
        ];
        $request->flash();
        $this->validate($request,$validateRule);

        $userId = $request->input('user_id',0);
        $user = User::find($userId);
        if(!$user){
            return $this->error(route('admin.credit.create'),'用户不存在，请核实');
        }
        $action = $request->input('action');
        $coins = $request->input('coins');
        $credits = $request->input('credits');
        $money = $request->input('money');
        if( $action == 'punish_user'){
            $credits = intval(-$credits);
            $coins   = intval(-$coins);
            $money   = intval(-$money);
        }
        event(new CreditEvent($userId,$action,$coins,$credits,0,$request->input('source_subject')));
        if ($money) {
            $userMoney = UserMoney::find($userId);
            $before_money = $userMoney->total_money;
            $moneyLog = MoneyLog::create([
                'user_id' => $userId,
                'change_money' => $request->input('money'),
                'source_id'    => $user->id,
                'source_type'  => get_class($user),
                'io'           => $money>0?1:-1,
                'money_type'   => MoneyLog::MONEY_TYPE_SYSTEM_ADD,
                'before_money' => $before_money
            ]);
            $userMoney->total_money = bcadd($userMoney->total_money, $money,2);
            $user->notify(new MoneyLogNotify($userId,$moneyLog,null,$request->input('source_subject')));
        }

        return $this->success(route('admin.credit.index'),'充值成功');
    }

}