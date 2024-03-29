<?php namespace App\Http\Controllers\Admin\Finance;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午7:57
 * @email: hank.huiwang@gmail.com
 */

class SettingController extends AdminController {


    public function index(Request $request){
        $validateRules = [
            'withdraw_suspend' => 'required|integer',
            'need_pay_actual' => 'required|integer',
            'withdraw_auto' => 'required|integer',
            'withdraw_day_limit' => 'required|integer',
            'withdraw_per_min_money' => 'required|integer',
            'withdraw_per_max_money' => 'required|integer',
            'pay_answer_normal_fee_rate' => 'numeric|min:0|max:1',
            'pay_answer_iap_fee_rate'    => 'numeric|min:0|max:1'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            $data = $request->except('_token');
            unset($data['_token']);
            foreach($data as $name=>$value){
                Setting()->set($name,$value);
            }
            Setting()->clearAll();

            return $this->success(route('admin.finance.setting.index'),'设置成功');
        }

        return view('admin.finance.setting.index');
    }

}