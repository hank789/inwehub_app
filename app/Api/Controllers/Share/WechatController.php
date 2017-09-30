<?php namespace App\Api\Controllers\Share;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Http\Requests;
use App\Models\Credit;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

class WechatController extends Controller
{


    public function jssdk(Request $request)
    {
        $validateRules = [
            'current_url' => 'required'
        ];

        $this->validate($request,$validateRules);
        $current_url = $request->input('current_url');
        $wechat = app('wechat');
        $js = $wechat->js;
        $js->setUrl($current_url);
        return self::createJsonData(true,['config'=>$js->config(['onMenuShareTimeline','onMenuShareQQ','onMenuShareAppMessage', 'onMenuShareWeibo'],false,false,false)]);
    }

    public function shareSuccess(Request $request){
        $validateRules = [
            'target' => 'required'
        ];

        $this->validate($request,$validateRules);
        $user = $request->user();
        if ($user) {
            if(RateLimiter::instance()->increase('share:success',$user->id,3,1)){
                throw new ApiException(ApiException::VISIT_LIMIT);
            }
            $this->credit($user->id,Credit::KEY_SHARE_SUCCESS,0,$request->input('target'));
        }
        return self::createJsonData(true);
    }

}
