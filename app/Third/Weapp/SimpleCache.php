<?php

/**
 * User: JiaweiXS
 * Date: 2017/7/29
 */

namespace App\Third\Weapp;

class SimpleCache
{
	private static $cacheDir;
	private static $cacheFile;
	private static $cacheTime;

	public static function init($cache_dir,$cache_time=600){
		self::$cacheDir = $cache_dir;
		self::$cacheTime = $cache_time;
		self::$cacheFile = $cache_dir.'/simplecache.scache';
	}

	public static function get($key,$default=''){

		$data = self::readAndRender();
		self::checkTimeoutAndSave($data);

		if(isset($data[$key])){
			return $data[$key]['value'];
		}else{
			return $default;
		}
	}

	public static function set($key,$value,$time = false){
		if(!$time) $time = self::$cacheTime;

		$data = self::readAndRender();
		$data[$key] = ['value'=>$value,'time'=>time()+$time];

		return self::checkTimeoutAndSave($data);
	}

	private static function readAndRender(){
		if(!file_exists(self::$cacheDir)){
			mkdir(self::$cacheDir);
		}

		if(file_exists(self::$cacheFile)){
			$json = file_get_contents(self::$cacheFile);
			$data = json_decode($json,true);
			if(!is_array($data)){
				$data = [];
			}
		}else{
			$data = [];
		}

		return $data;
	}

	private static function checkTimeoutAndSave(&$data){
		$cur_time = time();
		foreach($data as $k=>$v){
			if($cur_time>$data[$k]['time']){
				unset($data[$k]);
			}
		}

		$content = json_encode($data);
		if(file_put_contents(self::$cacheFile,$content)){
			return true;
		}else{
			return false;
		}
	}
}
