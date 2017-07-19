<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 2017/3/4
 * Time: 上午12:01
 */

namespace App\Http\Controllers\Admin;


use App\Models\LoginRecord;
use Illuminate\Http\Request;

class LoggerController extends AdminController
{

    public function loginLog(Request $request){
        $query = LoginRecord::query();
        $filter =  $request->all();

        /*充值人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }
        /*时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }
        $records = $query->orderBy('created_at','desc')->paginate(20);
        /*$credits->map(function($credit){
            $credit->actionText = config('inwehub.user_actions.'.$credit->action);
        });*/
        return view('admin.logger.login')->with(compact('records','filter'));
    }

}