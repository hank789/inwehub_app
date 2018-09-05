<?php namespace App\Http\Controllers\Admin\Finance;
use App\Events\Frontend\Withdraw\WithdrawOffline;
use App\Events\Frontend\Withdraw\WithdrawProcess;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Pay\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午7:59
 * @email: hank.huiwang@gmail.com
 */

class WithdrawController extends AdminController {

    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Withdraw::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("user_id","=",$filter['user_id']);
        }

        /*时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }elseif(isset($filter['status']) && $filter['status'] != -9){
            $query->where('status','=',Withdraw::WITHDRAW_STATUS_PENDING);
            $filter['status'] = Withdraw::WITHDRAW_STATUS_PENDING;
        }

        $withdraws = $query->orderBy('created_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.finance.withdraw.index')->with('withdraws',$withdraws)->with('filter',$filter);
    }


    public function verify(Request $request)
    {
        $ids = $request->input('id');
        foreach($ids as $id){
            $withdraw = Withdraw::find($id);
            if($withdraw->status == Withdraw::WITHDRAW_STATUS_PENDING ||
                $withdraw->status == Withdraw::WITHDRAW_STATUS_FAIL)
            {
                $withdraw->status = Withdraw::WITHDRAW_STATUS_PROCESS;
                $withdraw->save();
                event(new WithdrawProcess($id));
            }
        }
        return $this->success(route('admin.finance.withdraw.index').'?status=0','开始处理提现');
    }

    public function verifyOffline(Request $request)
    {
        $ids = $request->input('id');
        foreach($ids as $id){
            $withdraw = Withdraw::find($id);
            if($withdraw->status == Withdraw::WITHDRAW_STATUS_PENDING ||
                $withdraw->status == Withdraw::WITHDRAW_STATUS_FAIL)
            {
                $withdraw->status = Withdraw::WITHDRAW_STATUS_PROCESS;
                $withdraw->save();
                event(new WithdrawOffline($id));
            }
        }
        return $this->success(route('admin.finance.withdraw.index').'?status=0','开始处理提现');
    }

}