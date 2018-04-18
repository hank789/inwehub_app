<?php namespace App\Http\Controllers\Admin\Weapp;
use App\Http\Controllers\Admin\AdminController;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Third\Weapp\WeApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2018/3/9 下午1:53
 * @email: wanghui@yonglibao.com
 */


class DemandController extends AdminController {

    /**
     * 管理首页
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Demand::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("id","=",$filter['user_id']);
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }

        if (isset($filter['word']) && $filter['word']) {
            $query->where('title','like','%'.$filter['word'].'%');
        }

        $demands = $query->orderBy('created_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.weapp.demand.index')->with('demands',$demands)->with('filter',$filter);
    }

    public function detail(Request $request,WeApp $wxxcx) {
        $id = $request->input('id');
        $demand = Demand::find($id);
        $page = 'pages/detail/detail';
        $scene = 'demand_id='.$demand->id;
        try {
            $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
        } Catch (\Exception $e) {
            $qrcode = '';
        }

        return view('admin.weapp.demand.detail')->with($demand)->with($qrcode);
    }


    public function destroy(Request $request){
        $ids = $request->input('ids');
        Demand::destroy($ids);
        return $this->success(route('admin.weapp.demand.index'),'删除成功');
    }

}