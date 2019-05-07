<?php namespace App\Services\Hmac;
use App\Services\Hmac\Lib\Request;
use App\Services\Hmac\Lib\Token;
use App\Services\Curl;

/**
 * 客户端
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/11/6
 * Time: 下午4:00
 */

class Client {

    /**
     * @var Token
     */
    protected $token;

    protected static $instance = null;

    public function __construct($app_id,$app_secret){
        $this->token = new Token($app_id,$app_secret);
    }

    /**
     * @param $url;api地址
     * @param array $params 参数
     * @param string $method http method
     * @return array
     */
    public function request($url, array $params = array(),$method = 'POST'){
        $parse_url = parse_url($url);
        $request = new Request($method,$parse_url['path'],$params);
        $auth = $request->sign($this->token);
        $http = Curl::getInstance();
        $result = $http->post($url,array_merge($auth, $params));
        return json_decode($result['data'],true);
    }

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self(config('app.app_id'),config('app.app_secret'));
        }
        return self::$instance;
    }

}