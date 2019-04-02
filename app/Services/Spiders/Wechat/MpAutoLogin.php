<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\SystemNotify;
use App\Mail\ScanQrcode;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2019/1/14 下午6:47
 * @email:    hank.HuiWang@gmail.com
 */

class MpAutoLogin
{
    //--------------------------------------------------------LOGIN START
    private $_apis = [
        "host" => "https://mp.weixin.qq.com",
        "login" => "https://mp.weixin.qq.com/cgi-bin/bizlogin?action=startlogin",
        "qrcode" => "https://mp.weixin.qq.com/cgi-bin/loginqrcode?action=getqrcode&param=4300",
        "loginqrcode" => "https://mp.weixin.qq.com/cgi-bin/loginqrcode?action=ask&token=&lang=zh_CN&f=json&ajax=1",
        "loginask" => "https://mp.weixin.qq.com/cgi-bin/loginqrcode?action=ask&token=&lang=zh_CN&f=json&ajax=1&random=",
        "loginauth" => "https://mp.weixin.qq.com/cgi-bin/loginauth?action=ask&token=&lang=zh_CN&f=json&ajax=1",
        "bizlogin" => "https://mp.weixin.qq.com/cgi-bin/bizlogin?action=login&lang=zh_CN"
    ];
    private $_redirect_url = "";
    private $_key = "";
    private $_qrcodeUrl = '';
    private $_ql = '';

    private function _getCookieFile()
    {
        return storage_path('app/public/') . "cookie_{$this->_key}.text";
    }

    private function _getSavePath()
    {
        return storage_path('app/public/') . $this->_qrcodeName();
    }

    private function _qrcodeName()
    {
        return "qrcode_{$this->_key}.png";
    }

    private function _log($msg)
    {
        event(new SystemNotify("[微信调度:" . date("Y-m-d H:i:s") . "]  ======: {$msg}"));
    }

    public function getToken()
    {
        return Setting::get('scraper_wechat_gzh_token');
    }

    public function setToken($token)
    {
        return Setting::set('scraper_wechat_gzh_token',$token);
    }

    public function init($options)
    {
        if (!isset($options["key"])) {
            die("Key is Null!");
        }
        $this->_key = $options["key"];
        if ($this->getToken()) {
            echo("HAS Token !");
            return true;
        } else {
            $this->_ql = QueryList::getInstance();
            //尼玛，先要获取首页!!!
            $this->fetch("https://mp.weixin.qq.com/", "", "text");
            $this->_log("start login!!");
            return $this->start_login($options);
        }
    }

    private function start_login($options)
    {
        $_res = $this->_login($options["account"], $options["password"]);
        if ($_res == false) {
            $this->_log('登陆失败');
            return false;
        }
        //保存二维码
        $this->_saveQRcode();
        $_ask_api = $this->_apis["loginask"];
        $_input["refer"] = $this->_redirect_url;
        $_index = 1;
        while (true) {
            if($_index>60){
                break;
            }
            $_res = $this->fetch($_ask_api . $this->getWxRandomNum(), $_input);
            $_status = $_res["status"];
            if ($_status == 1) {
                if ($_res["user_category"] == 1) {
                    $_ask_api = $this->_apis["loginauth"];
                } else {
                    $this->_log("Login success");
                    break;
                }
            } else if ($_status == 4) {
                $this->_log("已经扫码");
            } else if ($_status == 2) {
                $this->_log("管理员拒绝");
                break;
            } else if ($_status == 3) {
                $this->_log("登录超时");
                break;
            } else {
                if ($_ask_api == $this->_apis["loginask"]) {
                    $this->_log("请打开test.jpg，用微信扫码");
                } else {
                    $this->_log("等待确认");
                }
            }
            sleep(5);
            $_index++;
        }
        if($_index>=60){
            $this->_log("U亲，超时了");
            return false;
        }
        $this->_log("开始验证");
        $_input["post"] = ["lang" => "zh_CN", "f" => "json", "ajax" => 1, "random" => $this->getWxRandomNum(), "token" => ""];
        $_input["refer"] = $this->_redirect_url;
        $_res = $this->fetch($this->_apis["bizlogin"], $_input);
        $this->_log(print_r($_res, true));
        if ($_res["base_resp"]["ret"] != 0) {
            $this->_log("error = " . $_res["base_resp"]["err_msg"]);
            return false;
        }
        $redirect_url = $_res["redirect_url"];//跳转路径
        if (preg_match('/token=([\d]+)/i', $redirect_url, $match)) {//获取cookie
            $this->setToken($match[1]);
        }
        $this->_log("验证成功,token: " . $this->getToken());
        return true;
    }

    //下载二维码
    private function _saveQRcode()
    {
        $_input["refer"] = $this->_redirect_url;
        $_res = $this->fetch($this->_apis["qrcode"], $_input, "text");
        $fileName = 'attachments/qrcode.png';
        //Storage::disk('local')->put($fileName,$_res);
        Storage::disk('oss')->put('system/mp_auto_login_qrcode.png',$_res);
        $this->_qrcodeUrl = 'http://inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com/system/mp_auto_login_qrcode.png';
        Mail::to('hank.wang@inwehub.com')->send(new ScanQrcode($this->_qrcodeUrl));
        //$fp = fopen($this->_getSavePath(), "wb+") or die("open fails");
        //fwrite($fp, $_res) or die("fwrite fails");
        //fclose($fp);
    }

    private function _login($_username, $_password)
    {
        $_input["post"] = array(
            'username' => $_username,
            'pwd' => md5($_password),
            'f' => 'json',
            'imgcode' => ""
        );
        $_input["refer"] = "https://mp.weixin.qq.com";
        $_res = $this->fetch($this->_apis["login"], $_input);
        if ($_res["base_resp"]["ret"] !== 0) {
            var_dump($_res["base_resp"]["err_msg"]);
            return false;
        }
        $this->_redirect_url = "https://mp.weixin.qq.com" . $_res["redirect_url"];//跳转路径
        return true;
    }

    function getWxRandomNum()
    {
        return "0." . mt_rand(1000000000000000, 9999999999999999);
    }

    /**
     * @param $url
     * @param null $_input
     * @param string $data_type
     * @return mixed
     * $_input= ["post"=>[],"refer"=>"",cookiefile='']
     */
    function fetch($url, $_input = null, $data_type = 'json')
    {
        $headers = [];
        if (isset($_input['refer'])) {
            $headers['Referer'] = $_input['refer'];
        }
        if (isset($_input['post'])) {
            $result = $this->_ql->post($url,$_input['post'],['headers'=>$headers])->getHtml();
        } else {
            $result = $this->_ql->get($url,null,['headers'=>$headers])->getHtml();
        }
        //var_dump($result);
        if ($data_type == 'json') {
            $result = json_decode($result, true);
        }
        return $result;


        $ch = curl_init();
        $useragent = isset($_input['useragent']) ? $_input['useragent'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';
        //curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->_headers); //设置HTTP头字段的数组
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, isset($_input['post']));
        if (isset($_input['post'])) curl_setopt($ch, CURLOPT_POSTFIELDS, $_input['post']);
        if (isset($_input['refer'])) curl_setopt($ch, CURLOPT_REFERER, $_input['refer']);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($_input['timeout']) ? $_input['timeout'] : 5));
        curl_setopt($ch, CURLOPT_COOKIEJAR, (isset($_input['cookiefile']) ? $_input['cookiefile'] : $this->_getCookieFile()));
        curl_setopt($ch, CURLOPT_COOKIEFILE, (isset($_input['cookiefile']) ? $_input['cookiefile'] : $this->_getCookieFile()));
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump($result);
        if ($data_type == 'json') {
            $result = json_decode($result, true);
        }
        return $result;
    }
    //--------------------------------------------------------LOGIN END

}