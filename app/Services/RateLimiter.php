<?php namespace App\Services;
use Illuminate\Support\Facades\Redis;

/**
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/9/29
 * Time: 下午3:50
 */

class RateLimiter extends Singleton
{

    const STATUS_GOOD = 0;

    const STATUS_BAD = 1;

    private $prefix = 'rate-limit';

    protected static $instance = null;

    /**
     * @var \Redis
     */
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    private function key($event,$target)
    {
        return implode(
            ':',
            array(
                $this->prefix,
                $event,
                $target
            )
        );
    }

    public function clear($event,$target)
    {
        $key = $this->key($event,$target);

        $this->client->del($key);
    }

    public function increase($event, $target, $expire = 60, $times = 1)
    {
        $key = $this->key($event, $target);

        $limit = $this->client->incr($key);
        if ($expire) $this->client->expire($key, $expire);

        if ($limit > $times) {
            return self::STATUS_BAD;
        } else {
            return self::STATUS_GOOD;
        }
    }

    public function increaseBy($event, $target, $value = 1 , $expire = 60)
    {
        $key = $this->key($event, $target);

        $limit = $this->client->incrBy($key,$value);
        if ($expire) $this->client->expire($key, $expire);
        return $limit;
    }

    public function hIncrBy($event,$key,$value){
        return $this->client->hIncrBy('inwehub:'.$event,$key,$value);
    }

    public function hGetAll($event){
        return $this->client->hGetAll('inwehub:'.$event);
    }

    public function sAdd($key,$value,$expire = 60) {
        $this->client->sAdd($key,$value);
        if ($expire) {
            $this->client->expire($key,$expire);
        }
        return true;
    }

    public function sMembers($key) {
        return $this->client->sMembers($key);
    }

    public function sRem($key,$value) {
        return $this->client->sRem($key,$value);
    }

    public function sIsMember($key,$value){
        return $this->client->sIsMember($key,$value);
    }

    public function sClear($key){
        $members = $this->sMembers($key);
        foreach ($members as $member) {
            $this->sRem($key,$member);
        }
    }


    public function setVale($event, $target, $value,$expire = 60) {
        $key = $this->key($event, $target);
        return $this->client->set($key,$value,$expire);
    }


    public function getValue($event, $target){
        $key = $this->key($event, $target);
        return $this->client->get($key);
    }

    /**
     * 尝试获得锁，等待直到获得为止,防止事件并发
     * 对于$max=1的事件（即不允许并发）执行完后一定要执行lock_release方法
     * @param $key
     * @param int $max 最大并发数
     * @param int $timeout 有效时间内
     * @return bool
     */
    function lock_acquire($key,$max=1,$timeout=5){
        $count = $this->client->incr($key);
        $this->client->expire($key,$timeout);
        $max = $max + 1;
        while($count >= $max){
            $count = $this->client->incr($key);
            usleep(1000);
        }
        return true;
    }

    /**
     * 释放锁
     * @param $key
     */
    function lock_release($key){
        $this->client->del($key);
    }

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self(Redis::connection());
        }
        return self::$instance;
    }
}