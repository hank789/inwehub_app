<?php
/**
 * @author: wanghui
 * @date: 2018/10/11 下午12:26
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Services;


use App\Events\Frontend\System\ExceptionNotify;
use QL\QueryList;

/**
 * http://47.96.179.217/Api1.0.html
 * 每天免费500次 凌晨更新次数
 * Class WechatGzhService
 * @package App\Services
 */
class WechatGzhService
{
    private $authToken = '89b7d3ecb1396da17c72e59dca2c415f';

    private $apiUrl = 'http://47.96.179.217/';

    private $count = 0;

    protected static $instance = null;

    public function __construct()
    {

    }

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getProfile($biz) {
        $result = $this->postRequest($this->apiUrl.'Home/info/get_profile',['auth'=>$this->authToken,'biz'=>$biz]);
        return $result?$result['list']:false;
    }

    public function foreverUrl($tmpUrl) {
        $result = $this->postRequest($this->apiUrl.'Home/info/get_sougou',['auth'=>$this->authToken,'url'=>$tmpUrl]);
        return $result?$result['forever_url']:false;
    }

    protected function postRequest($url,array $params) {
        $ql = QueryList::getInstance();
        $this->count++;
        try {
            $data = $ql->post($url,$params)->getHtml();
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return false;
        }
        $result = json_decode($data,true);
        if ($result['code'] == 1) {
            //成功
            return $result['data'];
        } else {
            event(new ExceptionNotify('公众号服务接口返回失败:'.$result['message']));
            if ($result['message'] != 'no money' && $this->count <= 3) {
                sleep(5);
                return $this->postRequest($url,$params);
            }
            return false;
        }
    }

}