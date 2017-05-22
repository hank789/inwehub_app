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

        $settlements = $query->orderBy('settlement_date','desc')->paginate(Config::get('tipask.admin.page_size'));
        return view('admin.finance.settlement.index')->with('settlements',$settlements)->with('filter',$filter);
    }

}