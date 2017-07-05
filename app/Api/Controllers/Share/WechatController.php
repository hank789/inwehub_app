<?php namespace App\Api\Controllers\Share;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Http\Requests;
use Illuminate\Http\Request;

class WechatController extends Controller
{


    public function jssdk(Request $request)
    {
        $wechat = app('wechat');
        $js = $wechat->js;
        return self::createJsonData(true,['config'=>$js->config(['onMenuShareTimeline','onMenuShareQQ','onMenuShareAppMessage', 'onMenuShareWeibo'],false,false,false)]);
    }

}
