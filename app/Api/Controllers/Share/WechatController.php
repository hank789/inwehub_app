<?php namespace App\Api\Controllers\Share;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Http\Requests;
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
        \Log::info('test',[$current_url]);
        $wechat = app('wechat');
        $js = $wechat->js;
        $js->setUrl($current_url);
        return self::createJsonData(true,['config'=>$js->config(['onMenuShareTimeline','onMenuShareQQ','onMenuShareAppMessage', 'onMenuShareWeibo'],false,false,false)]);
    }

}
