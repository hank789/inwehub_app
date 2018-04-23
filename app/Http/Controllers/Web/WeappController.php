<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: wanghui@yonglibao.com
 */

class WeappController extends Controller
{
    public function getDemandShareLongInfo($id,WeApp $wxxcx)
    {
        $demand = Demand::find($id);
        $cacheKey = 'demand-qrcode';
        $url = RateLimiter::instance()->hGet($cacheKey,$demand->id);
        $page = 'pages/detail/detail';
        $scene = 'demand_id='.$demand->id;
        try {
            if (!$url) {
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                $file_name = 'demand/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
                Storage::disk('oss')->put($file_name,$qrcode);
                $url = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet($cacheKey,$demand->id,$url);
            }
        } catch (\Exception $e) {

        }

        $demand_oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
        $data = [
            'publisher_user_id'=>$demand_oauth->user_id,
            'publisher_name'=>$demand->user->name,
            'publisher_avatar'=>$demand_oauth->avatar,
            'publisher_title'=>$demand->user->title,
            'publisher_company'=>$demand->user->company,
            'publisher_email'=>$demand->user->email,
            'publisher_phone' => $demand->user->mobile,
            'title' => $demand->title,
            'address' => $demand->address,
            'salary' => $demand->salary,
            'salary_upper' => $demand->salary_upper?:$demand->salary,
            'salary_type' => $demand->salary_type,
            'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
            'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
            'project_begin_time' => $demand->project_begin_time,
            'description' => $demand->description,
            'expired_at'  => $demand->expired_at,
            'views' => $demand->views,
            'status' => $demand->status,
            'qrcodeUrl' => $url
        ];
        return view('h5::weapp.demandShareLong')->with('demand',$data);
    }

    public function getDemandShareShortInfo($id){
        return view('h5::weapp.demandShareShort');
    }

}