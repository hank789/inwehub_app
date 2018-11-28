<?php
/**
 * Created by PhpStorm.
 * User: Jiawei
 * Date: 2017/7/29
 * Time: 10:04
 */

namespace App\Third\Weapp;


use App\Third\Weapp\Api\CustomMsg;
use App\Third\Weapp\Api\QRCode;
use App\Third\Weapp\Api\SessionKey;
use App\Third\Weapp\Api\Statistic;
use App\Third\Weapp\Api\TemplateMsg;
use App\Third\Weapp\Api\WeAppException;

class WeApp
{
	private $appid;
	private $secret;
	private $instance;
	private $sessionKey;

	public function __construct(){
		$this->appid = config('weapp.appid', '');
		$this->secret = config('weapp.secret', '');
		$this->instance = [];
		SimpleCache::init(storage_path('app/weapp/'));
	}

	public function setConfig($appid,$secret) {
        $this->appid = $appid;
        $this->secret = $secret;
    }

	/**
	 * @param $code
	 * @return array sessionkey相关数组
	 */
	public function getLoginInfo($code){
		if(!isset($this->instance['sessionkey'])){
			$this->instance['sessionkey'] = new SessionKey($this->appid,$this->secret);
		}
        $userInfo =  $this->instance['sessionkey']->get($code);
        if(!isset($userInfo['session_key'])){
            throw new WeAppException('获取 session_key 失败');
        }
        $this->sessionKey = $userInfo['session_key'];
        return $userInfo;
	}

	public function setSessionKey($sessionKey) {
	    $this->sessionKey = $sessionKey;
    }

    /**
     * Created by vicleos
     * @param $encryptedData
     * @param $iv
     * @return string
     */
    public function getUserInfo($encryptedData, $iv){
        $pc = new WXBizDataCrypt($this->appid, $this->sessionKey);
        $decodeData = "";
        $errCode = $pc->decryptData($encryptedData, $iv, $decodeData);
        if ($errCode !=0 ) {
            return [];
            //throw new WeAppException('encryptedData 解密失败');
        }
        return json_decode($decodeData,true);
    }

	/**
	 * @return TemplateMsg 模板消息对象
	 */
	public function getTemplateMsg(){
		if(!isset($this->instance['template'])){
			$this->instance['template'] = new TemplateMsg($this->appid,$this->secret);
		}
		return $this->instance['template'];
	}

	/**
	 * @return QRCode 二维码对象
	 */
	public function getQRCode(){
		if(!isset($this->instance['qrcode'])){
			$this->instance['qrcode'] = new QRCode($this->appid,$this->secret);
		}
		return $this->instance['qrcode'];
	}

	/**
	 * @return Statistic 数据统计对象
	 */
	public function getStatistic(){
		if(!isset($this->instance['statistic'])){
			$this->instance['statistic'] = new Statistic($this->appid,$this->secret);
		}
		return $this->instance['statistic'];
	}

	/**
	 * @return CustomMsg 客户消息对象
	 */
	public function getCustomMsg(){
		if(!isset($this->instance['custommsg'])){
			$this->instance['custommsg'] = new CustomMsg($this->appid,$this->secret);
		}
		return $this->instance['custommsg'];
	}

}