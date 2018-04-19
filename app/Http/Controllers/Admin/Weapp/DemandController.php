<?php namespace App\Http\Controllers\Admin\Weapp;
use App\Http\Controllers\Admin\AdminController;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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

        try {
            $url = RateLimiter::instance()->hGet('demand-qrcode',$demand->id);
            if (!$url) {
                $page = 'pages/detail/detail';
                $scene = 'demand_id='.$demand->id;
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                $file_name = 'demand/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
                Storage::disk('oss')->put($file_name,$qrcode);
                $url = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet('demand-qrcode',$demand->id,$url);
            }
        } Catch (\Exception $e) {
            $url = '';
        }
        return view('admin.weapp.demand.detail')->with('demand',$demand)->with('qrcode',$url);
    }


    public function destroy(Request $request){
        $ids = $request->input('ids');
        Demand::destroy($ids);
        return $this->success(route('admin.weapp.demand.index'),'删除成功');
    }

}