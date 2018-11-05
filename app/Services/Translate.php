<?php
/**
 * @author: wanghui
 * @date: 2018/11/2 上午10:43
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Services;


use Stichoza\GoogleTranslate\TranslateClient;

class Translate
{

    //protected $app_id = '20181102000229044';
    //protected $app_key = '1XCWk0jk4feYuR30SbDX';
    protected $app_id = '20181105000230297';
    protected $app_key = 'ULNO5BhZMlcHIMCYmzzA';
    protected $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate';

    protected $googleClient;

    protected static $instance = null;

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->googleClient = new TranslateClient('en', 'zh',['proxy'=>'socks5h://127.0.0.1:1080']);
    }

    public function googleApi($query,$from = 'en', $to = 'zh') {
        return $this->googleClient->setSource($from)->setTarget($to)->translate($query);
    }

    //翻译入口
    public function translate($query, $from = 'en', $to = 'zh')
    {
        $baiduQuery = str_replace("\n",'<br>',$query);
        $args = array(
            'q' => $baiduQuery,
            'appid' => $this->app_id,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,

        );
        $args['sign'] = $this->buildSign($baiduQuery, $this->app_id, $args['salt'], $this->app_key);
        $ret = $this->call($this->url, $args);
        $ret = json_decode($ret, true);
        if (isset($ret['error_code'])) {
            return $this->googleApi($query,$from,$to);
        }
        $trans = str_replace("<br>","\n",$ret['trans_result'][0]['dst']);
        $trans = str_replace("<BR>","\n",$trans);
        return $trans;
    }

    //加密
    function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

    //发起网络请求
    function call($url, $args=null, $method="post", $testflag = 0, $timeout = 10, $headers=array())
    {
        $ret = false;
        $i = 0;
        while($ret === false)
        {
            if($i > 1)
                break;
            if($i > 0)
            {
                sleep(1);
            }
            $ret = $this->callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = 10, $headers=array())
    {
        $ch = curl_init();
        if($method == "post")
        {
            $data = $this->convert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = $this->convert($args);
            if($data)
            {
                if(stripos($url, "?") > 0)
                {
                    $url .= "&$data";
                }
                else
                {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if($withCookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    function convert(&$args)
    {
        $data = '';
        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }

}