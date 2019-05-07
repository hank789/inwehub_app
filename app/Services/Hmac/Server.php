<?php namespace App\Services\Hmac;

use App\Services\Container\Container;
use App\Services\Hmac\Exceptions\SignatureException;
use App\Services\Hmac\Lib\Token;
use App\Services\Hmac\Guards\CheckKey;
use App\Services\Hmac\Guards\CheckSignature;
use App\Services\Hmac\Guards\CheckTimestamp;
use App\Services\Hmac\Guards\CheckVersion;
use App\Services\Hmac\Lib\Auth;

/**
 * 服务端
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/11/6
 * Time: 下午4:00
 */
class Server
{
    /**
     * @var Token
     */
    protected $token;

    protected static $instance = null;

    public function __construct($app_id, $app_secret)
    {
        $this->token = new Token($app_id, $app_secret);
    }

    /**
     * 验证api请求的有效性
     * @return array
     */
    public function validate(array $params)
    {
        $auth = new Auth($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $params, array(
            new CheckKey,
            new CheckVersion,
            new CheckTimestamp,
            new CheckSignature
        ));

        try {
            $auth->attempt($this->token);
            return $this->response(1000, 'success');
        } catch (SignatureException $se) {
            return $this->response($se->getCode(), $se->getMessage());
        } catch (\Exception $e) {
            // return 4xx
            return $this->response($e->getCode(), $e->getMessage());
        }
    }

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self(config('app.app_id'),config('app.app_secret'));
        }
        return self::$instance;
    }

    private function response($code, $message)
    {
        return array('code' => $code, 'message' => $message);
    }
}