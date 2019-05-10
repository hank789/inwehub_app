<?php namespace App\Services\Hmac\Lib;
/**
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/11/6
 * Time: 上午11:39
 */

class Request
{
    const VERSION = '1.0.0';

    const PREFIX = 'auth_';

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $params;

    /**
     * @var integer
     */
    private $timestamp;

    /**
     * Create a new Request
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param integer $timestamp
     */
    public function __construct($method, $uri, array $params = array(), $timestamp = null)
    {
        $this->method    = strtoupper($method);
        $this->uri       = $uri;
        $this->params    = $params;
        $this->timestamp = $timestamp ?: time();
    }

    /**
     * Sign the Request with a Token
     *
     * @param Token  $token
     * @param string $prefix
     * @return array
     */
    public function sign(Token $token, $prefix = self::PREFIX)
    {
        $auth = array(
            $prefix . 'version'   => self::VERSION,
            $prefix . 'key'       => $token->key(),
            $prefix . 'timestamp' => $this->timestamp,
        );

        $payload = $this->payload($auth, $this->params);

        $signature = $this->signature($payload, $this->method, $this->uri, $token->secret());

        $auth[$prefix . 'signature'] = $signature;

        return $auth;
    }

    /**
     * Create the payload
     *
     * @param array $auth
     * @param array $params
     * @return array
     */
    private function payload(array $auth, array $params)
    {
        $payload = array_merge($auth, $params);
        $payload = array_change_key_case($payload, CASE_LOWER);

        ksort($payload);

        return $payload;
    }

    /**
     * Create the signature
     *
     * @param array $payload
     * @param string $method
     * @param string $uri
     * @param string $secret
     * @return string
     */
    private function signature(array $payload, $method, $uri, $secret)
    {
        $s = '';
        foreach ($payload as $key=>$val) {
            $s .= $key.'='.$val.'&';
        }
        $payload = implode("\n", array($method, $uri, $s));

        return hash_hmac('sha256', $payload, $secret);
    }
}