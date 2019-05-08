<?php

namespace App\Api\Controllers\Partner;

use App\Api\Controllers\Controller;
use App\Events\Frontend\System\ImportantNotify;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Jobs\UpdateProductInfoCache;
use App\Models\ContentCollection;
use App\Models\PartnerOauth;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Tag;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use App\Services\Spiders\Wechat\WechatSogouSpider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    public function fetWechatUrlInfo(Request $request) {
        $this->validPartnerOauth($request);
        $validateRules = [
            'url'   => 'required|url',
        ];
        $this->validate($request,$validateRules);
        $link_url = $request->input('url');
        $parse_url = parse_url($link_url);
        if ($parse_url['host'] != 'mp.weixin.qq.com') {
            throw new ApiException(ApiException::PRODUCT_CASE_URL_INVALID);
        }
        $aid = Cache::get($link_url);
        if ($aid) {
            $article = WechatWenzhangInfo::find($aid);
            $mpInfo = WechatMpInfo::find($article->mp_id);
            return self::createJsonData(true,[
                'body' => $article->body,
                'title' => $article->title,
                'author' => $article->author,
                'wxHao' => $mpInfo->wx_hao,
                'date' => $article->date_time,
                'cover_img' => $article->cover_url
            ]);
        }
        $linkInfo = getWechatUrlInfo($link_url,false,true);
        $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
        if (!$mpInfo) {
            $mpInfo = WechatMpInfo::create([
                'name' => $linkInfo['author'],
                'wx_hao' => $linkInfo['wxHao'],
                'company' => $linkInfo['author'],
                'description' => '',
                'logo_url' => '',
                'qr_url' => '',
                'wz_url' => '',
                'last_qunfa_id' => 0,
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
        $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
        if ($aid) {
            $article = WechatWenzhangInfo::find($aid);
        } else {
            $article = WechatWenzhangInfo::create([
                'title' => $linkInfo['title'],
                'source_url' => '',
                'content_url' => $link_url,
                'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                'description' => '',
                'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                'mp_id' => $mpInfo->_id,
                'author' => $linkInfo['author'],
                'msg_index' => 0,
                'body' => $linkInfo['body'],
                'copyright_stat' => 0,
                'qunfa_id' => 0,
                'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                'like_count' => 0,
                'status' => 2,
                'read_count' => 0,
                'comment_count' => 0
            ]);
            RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
        }
        Cache::put($link_url,$article->_id,120);
        return self::createJsonData(true,[
            'body' => $article->body,
            'title' => $article->title,
            'author' => $article->author,
            'wxHao' => $mpInfo->wx_hao,
            'date' => $article->date_time,
            'cover_img' => $article->cover_url
        ]);
    }

    //获取源信息
    public function fetchSourceInfo(Request $request) {
        $this->validPartnerOauth($request);
        $validateRules = [
            'product_id' => 'required',
            'source'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $wx_hao = trim($request->input('source'));
        $product_id = $request->input('product_id');
        if (count(parse_url($wx_hao))>=2) {
            throw new ApiException(ApiException::PRODUCT_SOURCE_URL_INVALID);
        }
        $mpInfo = WechatMpInfo::where('wx_hao',$wx_hao)->first();
        if ($mpInfo) {
            return self::createJsonData(true,$mpInfo->toArray());
        }
        $mpInfo = WechatMpInfo::where('name',$wx_hao)->first();
        if ($mpInfo) {
            return self::createJsonData(true,$mpInfo->toArray());
        }
        $spider = new MpSpider();
        if (config('app.env') == 'production') {
            $data = $spider->getGzhInfo($wx_hao,false);
        } else {
            $data = null;
        }

        if ($data) {
            $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
            if (!$info) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $data['name'],
                    'wx_hao' => $data['wechatid'],
                    'company' => $data['company'],
                    'description' => $data['description'],
                    'logo_url' => $data['img'],
                    'qr_url' => $data['qrcode'],
                    'wz_url' => $data['url'],
                    'last_qunfa_id' => $data['last_qunfa_id'],
                    'is_auto_publish' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            $spider2 = new WechatSogouSpider();
            $data = $spider2->getGzhInfo($wx_hao,true);
            if ($data['name']) {
                $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
                if (!$info) {
                    $mpInfo = WechatMpInfo::create([
                        'name' => $data['name'],
                        'wx_hao' => $data['wechatid'],
                        'company' => $data['company'],
                        'description' => $data['description'],
                        'logo_url' => $data['img'],
                        'qr_url' => $data['qrcode'],
                        'wz_url' => $data['url'],
                        'is_auto_publish' => 0,
                        'status' => 0,
                        'last_qunfa_id' => $data['last_qunfa_id'],
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                throw new ApiException(ApiException::REQUEST_FAIL);
            }
        }
        if ($mpInfo) {
            return self::createJsonData(true,$mpInfo->toArray());
        } else {
            throw new ApiException(ApiException::REQUEST_FAIL);
        }
    }

    public function addSource(Request $request) {
        $this->validPartnerOauth($request);
        $validateRules = [
            'source'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $app_id = $request->input('auth_key');
        $oauth = PartnerOauth::where('app_id',$app_id)->where('status',1)->first();

        $source = trim($request->input('source'));
        $source_id = trim($request->input('source_id'));
        $product_id = $oauth->product_id;
        if (count(parse_url($source))>=2) {
            throw new ApiException(ApiException::PRODUCT_SOURCE_URL_INVALID);
        }
        if ($source_id) {
            $mpInfo = WechatMpInfo::find($source_id);
            if (!$mpInfo) {
                throw new ApiException(ApiException::REQUEST_FAIL);
            }
        } else {
            $mpInfo = WechatMpInfo::where('wx_hao',$source)->first();
            if (!$mpInfo) {
                $mpInfo = WechatMpInfo::where('name',$source)->first();
            }
            if (!$mpInfo) {
                $spider = new MpSpider();
                if (config('app.env') == 'production') {
                    $data = $spider->getGzhInfo($source,false);
                } else {
                    $data = null;
                }

                if ($data) {
                    $info = WechatMpInfo::where('wx_hao',$source)->first();
                    if (!$info) {
                        $mpInfo = WechatMpInfo::create([
                            'name' => $data['name'],
                            'wx_hao' => $data['wechatid'],
                            'company' => $data['company'],
                            'description' => $data['description'],
                            'logo_url' => $data['img'],
                            'qr_url' => $data['qrcode'],
                            'wz_url' => $data['url'],
                            'last_qunfa_id' => $data['last_qunfa_id'],
                            'is_auto_publish' => 0,
                            'status' => 0,
                            'create_time' => date('Y-m-d H:i:s')
                        ]);
                    }
                } else {
                    $spider2 = new WechatSogouSpider();
                    $data = $spider2->getGzhInfo($source,true);
                    if ($data['name']) {
                        $info = WechatMpInfo::where('wx_hao',$source)->first();
                        if (!$info) {
                            $mpInfo = WechatMpInfo::create([
                                'name' => $data['name'],
                                'wx_hao' => $data['wechatid'],
                                'company' => $data['company'],
                                'description' => $data['description'],
                                'logo_url' => $data['img'],
                                'qr_url' => $data['qrcode'],
                                'wz_url' => $data['url'],
                                'is_auto_publish' => 0,
                                'status' => 0,
                                'last_qunfa_id' => $data['last_qunfa_id'],
                                'create_time' => date('Y-m-d H:i:s')
                            ]);
                        }
                    } else {
                        throw new ApiException(ApiException::REQUEST_FAIL);
                    }
                }
            }
        }
        $exist = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$product_id)
            ->where('sort',$mpInfo->_id)
            ->first();
        if (!$exist) {
            $exist = ContentCollection::create([
                'content_type' => ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH,
                'sort' => $mpInfo->_id,
                'source_id' => $product_id,
                'status' => 1,
                'content' => [
                    'wx_hao' => $mpInfo->wx_hao,
                    'mp_id' => $mpInfo->_id
                ]
            ]);
        }
        if ($mpInfo->status != 1) {
            $mpInfo->status = 1;
            $mpInfo->save();
        }
        return self::createJsonData(true,$mpInfo->toArray());
    }

    public function removeSource(Request $request) {
        $this->validPartnerOauth($request);
        $validateRules = [
            'source_id'   => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $app_id = $request->input('auth_key');
        $oauth = PartnerOauth::where('app_id',$app_id)->where('status',1)->first();
        $id = $request->input('source_id');
        $mpInfo = WechatMpInfo::find($id);
        ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$oauth->product_id)
            ->where('sort',$id)->delete();
        $exist = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('sort',$id)
            ->first();
        if (!$exist) {
            $mpInfo = WechatMpInfo::find($id);
            $mpInfo->status = 0;
            $mpInfo->save();
        }
        return self::createJsonData(true);
    }
}
