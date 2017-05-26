<?php namespace App\Cache;
/**
 * @author: wanghui
 * @date: 2017/5/26 下午1:56
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Support\Facades\Cache;

Class UserCache {

    public static function getUserInfoCache($uid){
        $cache_key = 'user_info:'.$uid;
        return Cache::get($cache_key);
    }

    public static function delUserInfoCache($uid){
        $cache_key = 'user_info:'.$uid;
        return Cache::forget($cache_key);
    }

    public static function setUserInfoCache($uid,array $info){
        $cache_key = 'user_info:'.$uid;
        return Cache::forever($cache_key,$info);
    }

}