<?php namespace App\Http\Controllers\Admin\Finance;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Pay\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午7:59
 * @email: hank.huiwang@gmail.com
 */

class OrderController extends AdminController {

    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Order::query();

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
        }

        $orders = $query->orderBy('id','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.finance.order.index')->with('orders',$orders)->with('filter',$filter);
    }

}