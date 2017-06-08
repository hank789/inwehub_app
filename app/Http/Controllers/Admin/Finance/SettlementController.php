<?php namespace App\Http\Controllers\Admin\Finance;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Pay\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午7:59
 * @email: wanghui@yonglibao.com
 */

class SettlementController extends AdminController {

    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Settlement::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("user_id","=",$filter['user_id']);
        }

        /*时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('settlement_date',explode(" - ",$filter['date_range']));
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }elseif(isset($filter['status']) && $filter['status'] != -9){
            $query->where('status','=',Settlement::SETTLEMENT_STATUS_PENDING);
            $filter['status'] = Settlement::SETTLEMENT_STATUS_PENDING;
        }

        $settlements = $query->orderBy('settlement_date','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.finance.settlement.index')->with('settlements',$settlements)->with('filter',$filter);
    }

    /**
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');

        Settlement::where('status',Settlement::SETTLEMENT_STATUS_PENDING)->whereIn('id',$ids)->update(['status'=>Settlement::SETTLEMENT_STATUS_SUSPEND]);
        return $this->success(route('admin.finance.settlement.index'),'暂停成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        Settlement::where('status',Settlement::SETTLEMENT_STATUS_SUSPEND)->whereIn('id',$ids)->update(['status'=>Settlement::SETTLEMENT_STATUS_PENDING]);

        return $this->success(route('admin.finance.settlement.index'),'恢复成功');
    }

    public function doitnow(Request $request){
        $ids = $request->input('id');
        Settlement::where('status',Settlement::SETTLEMENT_STATUS_PENDING)->whereIn('id',$ids)->update(['settlement_date'=>date('Y-m-d 00:00:00')]);
        Artisan::queue('pay:settlement');
        return $this->success(route('admin.finance.settlement.index'),'执行成功,稍等片刻');
    }

}