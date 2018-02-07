<?php namespace App\Http\Controllers\Admin;
/**
 * @author: wanghui
 * @date: 2017/5/15 上午10:56
 * @email: wanghui@yonglibao.com
 */
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class OperateController extends AdminController {

    /**
     * 首页数据
     * @param Request $request
     */
    public function homeData(Request $request){
        $validateRules = [
            'recommend_expert_name' => 'required',
            'recommend_expert_description' => 'required',
            'recommend_expert_uid' => 'required|integer'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            $data = $request->except('_token');
            unset($data['_token']);
            $recommend_expert_uid = $data['recommend_expert_uid'];
            $recommend_expert_user = UserData::find($recommend_expert_uid);
            if($recommend_expert_user && $recommend_expert_user->authentication_status == 1){

            } else {
                return $this->success(route('admin.operate.home_data'),'首页推荐专家用户有误');
            }
            foreach($data as $name=>$value){
                Setting()->set($name,$value);
            }
            Setting()->clearAll();

            return $this->success(route('admin.operate.home_data'),'首页数据设置成功');
        }

        return view('admin.operate.home_data');
    }

    public function refreshExpert(){
        $experts = Authentication::where('status',1)->pluck('user_id')->toArray();
        shuffle($experts);
        $cache_experts = [];
        $expert_uids = array_slice($experts,0,7);
        foreach ($expert_uids as $key=>$expert_uid) {
            $expert_user = User::find($expert_uid);
            $cache_experts[$key]['id'] = $expert_uid;
            $cache_experts[$key]['name'] = $expert_user->name;
            $cache_experts[$key]['title'] = $expert_user->title;
            $cache_experts[$key]['uuid'] = $expert_user->uuid;
            $cache_experts[$key]['work_years'] = $expert_user->getWorkYears();
            $cache_experts[$key]['avatar_url'] = $expert_user->avatar;
            $cache_experts[$key]['is_followed'] = 0;
        }
        Cache::put('home_experts',$cache_experts,60*24);
        return $this->success(route('admin.operate.recommendRead.index'),'首页专家更新成功');

    }

    public function bootGuide(Request $request){
        $validateRules = [
            'show_boot_guide' => 'required|integer'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            $data = $request->except('_token');
            unset($data['_token']);
            foreach($data as $name=>$value){
                Setting()->set($name,$value);
            }
            Setting()->clearAll();

            return $this->success(route('admin.operate.bootGuide'),'设置成功');
        }

        return view('admin.operate.bootGuide');
    }
}