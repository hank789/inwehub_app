<?php namespace App\Services\Spiders;
use GuzzleHttp\Client;

/**
 * @author: wanghui
 * @date: 2019/1/16 下午5:54
 * @email:    hank.HuiWang@gmail.com
 */

class Sclient {

    protected static $instance = null;

    protected $client;


    public function __construct()
    {
        $this->client = new Client(['cookies' => true,'verify' => false]);
    }

    /**
     * Get the QueryList single instance
     *
     * @return Sclient
     */
    public static function getInstance()
    {
        self::$instance || self::$instance = new self();
        return self::$instance;
    }

    public function get($url,$args = [], $others = []) {
        $options = [];
        if ($args) {
            $options['query'] = $args;
        }
        if ($others) {
            $options = array_merge($options,$others);
        }
        $response = $this->client->get($url,$options);
        return $response->getBody();
    }

    public function post($url,$args = [], $others = []) {
        $options = [];
        if ($args) {
            $options['form_params'] = $args;
        }
        if ($others) {
            $options = array_merge($options,$others);
        }
        $response = $this->client->post($url,$options);
        return $response->getBody();
    }

    public function refreshCookie() {
        $this->client->getConfig('cookies')->clear();
    }

}