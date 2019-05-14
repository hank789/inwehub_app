<?php namespace App\Services\Hmac\Lib;
/**
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/11/6
 * Time: 上午11:38
 */

class Token
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * Create a new Token
     *
     * @param string $key
     * @param string $secret
     * @return void
     */
    public function __construct($key, $secret)
    {
        $this->key    = $key;
        $this->secret = $secret;
    }

    /**
     * Get the key
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the secret
     *
     * @return string
     */
    public function secret()
    {
        return $this->secret;
    }
}
