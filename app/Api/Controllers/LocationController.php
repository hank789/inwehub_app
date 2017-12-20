<?php namespace App\Api\Controllers;
use App\Services\BaiduMap;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/12/20 下午3:50
 * @email: wanghui@yonglibao.com
 */

class LocationController extends Controller {

    public function nearbySearch(Request $request){
        $validateRules = [
            'longitude'   => 'required',
            'latitude' => 'required'
        ];
        $this->validate($request, $validateRules);
        $result = BaiduMap::instance()->geocoder($request->input('latitude'),$request->input('longitude'));
        $places = [];
        if (isset($result['result']) && isset($result['formatted_address'])) {
            $places[] = [
                'name' => $result['formatted_address'],
                'address' => $result['formatted_address'],
                'distance' => 0
            ];
        }
        if (isset($result['pois'])) {
            foreach ($result['pois'] as $item) {
                $places[] = [
                    'name' => $item['name'],
                    'address' => $item['addr'],
                    'distance' => $item['distance']
                ];
            }
        }

        return self::createJsonData(true,$places);
    }

}