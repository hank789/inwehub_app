<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 21:32
 */

namespace App\Third\Pay\ali;


use common\library\helper\LogHelper;
use App\Third\Pay\ali\request\AlipayDataDataserviceBillDownloadurlQueryRequest;
use App\Third\Pay\ali\request\AlipayTradeCloseRequest;
use App\Third\Pay\ali\request\AlipayTradeFastpayRefundQueryRequest;
use App\Third\Pay\ali\request\AlipayTradePagePayRequest;
use App\Third\Pay\ali\request\AlipayTradePrecreateRequest;
use App\Third\Pay\ali\request\AlipayTradeQueryRequest;
use App\Third\Pay\ali\request\AlipayTradeRefundRequest;
use yii\helpers\Json;

class AlipayTradeService
{
    //支付宝网关地址
    public $gateway_url = "https://openapi.alipay.com/gateway.do";

    //支付宝公钥
    public $alipay_public_key;

    //商户私钥
    public $private_key;

    //应用id
    public $appid;

    //编码格式
    public $charset = "UTF-8";

    public $token = NULL;

    //返回数据格式
    public $format = "json";

    //签名方式
    public $signtype = "RSA2";

    public function __construct($alipay_config){

        $this->gateway_url = $alipay_config['gatewayUrl'];
        $this->appid = $alipay_config['app_id'];
        $this->private_key = $alipay_config['merchant_private_key'];
        $this->alipay_public_key = $alipay_config['alipay_public_key'];
        $this->charset = $alipay_config['charset'];
        $this->signtype=$alipay_config['sign_type'];

        if(empty($this->appid)||trim($this->appid)==""){
            throw new \Exception("appid should not be NULL!");
        }
        if(empty($this->private_key)||trim($this->private_key)==""){
            throw new \Exception("private_key should not be NULL!");
        }
        if(empty($this->alipay_public_key)||trim($this->alipay_public_key)==""){
            throw new \Exception("alipay_public_key should not be NULL!");
        }
        if(empty($this->charset)||trim($this->charset)==""){
            throw new \Exception("charset should not be NULL!");
        }
        if(empty($this->gateway_url)||trim($this->gateway_url)==""){
            throw new \Exception("gateway_url should not be NULL!");
        }

    }

    /**
     * alipay.trade.page.pay
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @param $return_url 同步跳转地址，公网可以访问
     * @param $notify_url 异步通知地址，公网可以访问
     * @return $response 支付宝返回的信息
     */
    public function pagePay($builder,$return_url,$notify_url) {

        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);

        $request = new AlipayTradePagePayRequest();

        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent ( $biz_content );

        // 首先调用支付api
        $response = $this->aopclientRequestExecute ($request,true);
        // $response = $response->alipay_trade_wap_pay_response;
        return $response;
    }

    public function qrcodePay($builder,$return_url,$notify_url)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradePrecreateRequest();

        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request, false);
        $json01 = Json::encode($response);
        $ret = Json::decode($json01);
        if(!empty($ret) && $ret['alipay_trade_precreate_response']['code'] == 10000){

            return [
                    'qr_code' => $ret['alipay_trade_precreate_response']['qr_code'],
                    'out_trade_no' => $ret['alipay_trade_precreate_response']['out_trade_no']
                ];

        } else {

            return [];
        }
    }

    /**
     * sdkClient
     * @param $request 接口请求参数对象。
     * @param $ispage  是否是页面接口，电脑网站支付是页面表单接口。
     * @return $response 支付宝返回的信息
     */
    public function aopclientRequestExecute($request,$ispage=false) {

        $aop = new AopClient ();
        $aop->gatewayUrl = $this->gateway_url;
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey =  $this->private_key;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $aop->apiVersion ="1.0";
        $aop->postCharset = $this->charset;
        $aop->format= $this->format;
        $aop->signType=$this->signtype;
        // 开启页面信息输出
        $aop->debugInfo=true;
        if($ispage)
        {
            $result = $aop->pageExecute($request,"post");
            echo $result;
        }
        else
        {
            $result = $aop->Execute($request);
        }

        //打开后，将报文写入log文件
        $this->writeLog("response: ".var_export($result,true));
        return $result;
    }

    /**
     * alipay.trade.query (统一收单线下交易查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Query($builder){
        $biz_content=$builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeQueryRequest();
        $request->setBizContent ( $biz_content );

        $response = $this->aopclientRequestExecute ($request);
        $response = $response->alipay_trade_query_response;
        return $response;
    }

    /**
     * alipay.trade.refund (统一收单交易退款接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Refund($builder){
        $biz_content=$builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeRefundRequest();
        $request->setBizContent ( $biz_content );

        $response = $this->aopclientRequestExecute ($request);
        $response = $response->alipay_trade_refund_response;
        return $response;
    }

    /**
     * alipay.trade.close (统一收单交易关闭接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Close($builder){
        $biz_content=$builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeCloseRequest();
        $request->setBizContent ( $biz_content );

        $response = $this->aopclientRequestExecute ($request);
        $response = $response->alipay_trade_close_response;
        return $response;
    }

    /**
     * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function refundQuery($builder){
        $biz_content=$builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent ( $biz_content );

        $response = $this->aopclientRequestExecute ($request);
        return $response;
    }
    /**
     * alipay.data.dataservice.bill.downloadurl.query (查询对账单下载地址)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function downloadurlQuery($builder){
        $biz_content=$builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayDataDataserviceBillDownloadurlQueryRequest();
        $request->setBizContent ( $biz_content );

        $response = $this->aopclientRequestExecute ($request);
        $response = $response->alipay_data_dataservice_bill_downloadurl_query_response;
        return $response;
    }

    /**
     * 验签方法
     * @param $arr 验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    public function check($arr){
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result = $aop->rsaCheckV1($arr, $this->alipay_public_key, $this->signtype);

        return $result;
    }

    /**
     * 请确保项目文件有可写权限，不然打印不了日志。
     */
    public function writeLog($text) {
        \Log::info('supplier_pay_ali_request_data',['body'=>$text]);
    }

}