<?php namespace App\Services\City;
use Illuminate\Support\Facades\Cache;

/**
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/9/9
 * Time: 下午3:58
 */

class CityData
{

	public static function getProvinceName($province_code){
		if(empty($province_code)) return '';
		$provinces = self::getAll();
		foreach($provinces as $province){
			if($province['value'] == $province_code){
				return $province['text'];
			}
		}
		return '';
	}

	public static function getCityName($province_code, $city_code){
		if(empty($city_code)) return '';
		$provinces = self::getAll();
		foreach($provinces as $province){
			if($province['value'] == $province_code){
				$cities = $province['children'];
				foreach($cities as $city){
					if($city['value'] == $city_code){
						return $city['text'];
					}
				}
			}
		}
		return '';
	}

	public static function getAllProvince(){
		$provinces = self::getAll();
		$list = [];
		foreach($provinces as $province){
			$list[$province['value']] = $province['text'];
		}
		return $list;
	}

	public static function getCityByProvince($province_code){
		$provinces = self::getAll();
		$list = [];
		foreach($provinces as $province){
			if($province['value'] == $province_code){
				$cities = $province['children'];
				foreach($cities as $city){
					$list[$city['value']] = $city['text'];
				}
			}
		}
		return $list;
	}

	/**
	 * 获取所有省市
	 * @return mixed
	 */
	public static function getAll(){
		$cache = Cache::get('china_city_data');
		if(empty($cache)){
			$cache = file_get_contents(__DIR__ . '/china_city_code.json');
			Cache::forever('china_city_data',$cache);
		}
		return json_decode($cache,true);
	}

}