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
        $this->client->expire($key, $expire);

        if ($limit > $times) {
            return self::STATUS_BAD;
        } else {
            return self::STATUS_GOOD;
        }
    }

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self(Redis::connection());
        }
        return self::$instance;
    }
}