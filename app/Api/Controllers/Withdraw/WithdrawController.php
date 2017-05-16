<?php namespace App\Api\Controllers\Withdraw;
use App\Api\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:11
 * @email: wanghui@yonglibao.com
 */

class WithdrawController extends Controller {
    public function request(Request $request)
    {

        $validateRules = [
            'app_id' => 'required',
            'amount' => 'required|integer',
            'pay_channel' => 'required|in:alipay,wxpay',
            'pay_object_type' => 'required|in:ask'
        ];
        $this->validate($request, $validateRules);
        $data = $request->all();
        $pay_channel = $data['pay_channel'];
    }
}