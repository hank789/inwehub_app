<?php

namespace App\Api\Controllers\Partner;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Jobs\UpdateProductInfoCache;
use App\Models\PartnerOauth;
use App\Models\Tag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function sendPhoneCode(Request $request) {
        $this->validPartnerOauth($request);
        $validateRules = [
            'mobile' => 'required|cn_phone',
            'type'   => 'required|in:register,login,change,weapp_register,change_phone',
            'params' => 'required'
        ];

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        $type   = $request->input('type');
        if(RateLimiter::instance()->increase('sendPhoneCode:'.$type,$mobile,60,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        dispatch((new SendPhoneMessage($mobile,$request->input('params'),$type)));
        return self::createJsonData(true);
    }

    public function getProductInfo(Request $request) {
        $this->validPartnerOauth($request);
        $app_id = $request->input('auth_key');
        $oauth = PartnerOauth::where('app_id',$app_id)->where('status',1)->first();
        $product = Tag::find($oauth->product_id);
        $data = $product->getProductCacheInfo();
        if (!$data) {
            $data = (new UpdateProductInfoCache($product->id))->handle();
        }
        $oauth->api_url = trim($request->input('api_url'));
        $oauth->save();
        return self::createJsonData(true,$data);
    }
}
