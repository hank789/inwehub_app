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
        $data = $result['result'];
        if (isset($data['formatted_address'])) {
            $places[] = [
                'name' => $data['sematic_description'],
                'address' => $data['formatted_address'],
                'distance' => 0
            ];
        }
        if (isset($data['pois'])) {
            foreach ($data['pois'] as $item) {
                $places[] = [
                    'name' => $item['name'],
                    'address' => $item['addr'],
                    'distance' => $item['distance']
                ];
            }
        }
        $return = [
            'data' => $places,
            'current_page' => 1,
            'per_page' => 40,
            'from' => 1,
            'to' => count($places)
        ];

        return self::createJsonData(true,$return);
    }

}