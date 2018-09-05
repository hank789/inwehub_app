<?php namespace App\Http\Controllers\Admin\Weapp;
use App\Http\Controllers\Admin\AdminController;
use App\Models\UserOauth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2018/3/9 下午1:53
 * @email: hank.huiwang@gmail.com
 */


class UserController extends AdminController {

    /**
     * 用户管理首页
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP);

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("id","=",$filter['user_id']);
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }

        if (isset($filter['wechat_nickname']) && $filter['wechat_nickname']) {
            $query->where('nickname','like','%'.$filter['wechat_nickname'].'%');
        }

        $users = $query->orderBy('created_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.weapp.user.index')->with('users',$users)->with('filter',$filter);
    }

    /*用户审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        UserOauth::whereIn('id',$ids)->update(['status'=>1]);
        return $this->success(route('admin.weapp.user.index'),'用户审核成功');

    }

    /**
     * 将用户设置为不可用
     */
    public function cancelVerify(Request $request)
    {
        $ids = $request->input('id');
        UserOauth::whereIn('id',$ids)->update(['status'=>0]);
        return $this->success(route('admin.user.index'),'取消用户审核成功');

    }

}