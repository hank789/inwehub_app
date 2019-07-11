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

    private $prefix2 = '';


    protected static $instance = null;

    /**
     * @var \Redis
     */
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
        $this->prefix2 = config('cache.prefix');
    }

    public function disconnect() {
        $this->client = null;
        self::$instance = null;
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

    public function del($key) {
        $this->client->del($this->prefix2.':'.$key);
    }

    public function increase($event, $target, $expire = 60, $times = 1)
    {
        $key = $this->key($event, $target);

        $limit = $this->client->incr($key);
        if ($expire) {
            $this->client->expire($key, $expire);
        }

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
        return $this->client->hIncrBy($this->prefix2.':'.$event,$key,$value);
    }

    public function hSet($event,$key,$value) {
        return $this->client->hSet($this->prefix2.':'.$event,$key,$value);
    }

    public function hGet($event,$key) {
        return $this->client->hGet($this->prefix2.':'.$event,$key);
    }

    public function hGetAll($event){
        return $this->client->hGetAll($this->prefix2.':'.$event);
    }

    public function hDel($event,$key) {
        return $this->client->hDel($this->prefix2.':'.$event,$key);
    }

    public function hClear($event) {
        $keys = $this->hGetAll($event);
        foreach ($keys as $key=>$val) {
            $this->client->hDel($this->prefix2.':'.$event,$key);
        }
    }

    public function sAdd($key,$value,$expire = 60) {
        $this->client->sAdd($this->prefix2.':'.$key,$value);
        if ($expire) {
            $this->client->expire($this->prefix2.':'.$key,$expire);
        }
        return true;
    }

    public function sMembers($key, $keyPrefix = '') {
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->sMembers($keyPrefix.$key);
    }

    public function sRem($key,$value,$keyPrefix = '') {
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->sRem($keyPrefix.$key,$value);
    }

    public function sIsMember($key,$value){
        return $this->client->sIsMember($this->prefix2.':'.$key,$value);
    }

    public function sClear($key, $keyPrefix = ''){
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        $members = $this->sMembers($key,$keyPrefix);
        foreach ($members as $member) {
            $this->sRem($key,$member,$keyPrefix);
        }
    }

    public function zAdd($key,$score,$value){
        return $this->client->zAdd($this->prefix2.':'.$key,$score,$value);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key in the range [start, end]
     * in reverse order. start and stop are interpretated as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     *
     * @param   string  $key
     * @param   int     $start
     * @param   int     $end
     * @param   bool    $withscore
     * @return  array   Array containing the values in specified range.
     * @link    http://redis.io/commands/zrevrange
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zRevRange('key', 0, -1); // array('val10', 'val2', 'val0')
     *
     * // with scores
     * $redis->zRevRange('key', 0, -1, true); // array('val10' => 10, 'val2' => 2, 'val0' => 0)
     * </pre>
     */
    public function zRevrange($key,$start,$end,$keyPrefix = ''){
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->zRevRange($keyPrefix.$key,$start,$end,'WITHSCORES');
    }

    public function zRevrangeByScore($key,$start,$end,$withscores=true,$keyPrefix = '') {
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->zRevRangeByScore($keyPrefix.$key,$start,$end,['withscores' => $withscores]);
    }

    public function zRangeByScore($key,$start,$end,$withscores=true,$keyPrefix = '') {
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->zRangeByScore($keyPrefix.$key,$start,$end,['withscores' => $withscores]);
    }

    public function zRem($key,$value,$keyPrefix = '') {
        if (empty($keyPrefix)) {
            $keyPrefix = $this->prefix2.':';
        }
        return $this->client->zRem($keyPrefix.$key,$value);
    }


    public function setVale($event, $target, $value,$expire = 60) {
        $key = $this->key($event, $target);
        return $this->client->setex($key,$expire,$value);
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
        $key = $this->prefix.$key;
        $count = $this->client->incr($key);
        while($count > $max){
            usleep(1000);
            $count = $this->client->incr($key);
        }
        $ttl = $this->client->pttl($key);
        if ($ttl <= 0) {
            $this->client->expire($key,$timeout);
        }
    }

    /**
     * 释放锁
     * @param $key
     */
    function lock_release($key){
        $this->client->del($this->prefix2.':'.$key);
    }

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self(Redis::connection());
        }
        return self::$instance;
    }
}