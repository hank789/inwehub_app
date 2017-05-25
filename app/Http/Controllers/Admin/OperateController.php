<?php namespace App\Http\Controllers\Admin;
/**
 * @author: wanghui
 * @date: 2017/5/15 上午10:56
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Http\Request;

class OperateController extends AdminController {

    /**
     * 首页数据
     * @param Request $request
     */
    public function homeData(Request $request){
        $validateRules = [
            'recommend_expert_name' => 'required',
            'recommend_expert_description' => 'required',
            'recommend_expert_uid' => 'required|integer',
            'recommend_expert_avatar_url' => 'required'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            $data = $request->except('_token');
            unset($data['_token']);
            foreach($data as $name=>$value){
                Setting()->set($name,$value);
            }
            Setting()->clearAll();

            return $this->success(route('admin.operate.home_data'),'首页数据设置成功');
        }

        return view('admin.operate.home_data');
    }

}