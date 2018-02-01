<?php namespace App\Api\Controllers;
use App\Models\Attention;
use App\Models\LoginRecord;
use App\Models\UserData;
use App\Services\BaiduMap;
use App\Services\GeoHash;
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
        $name = $request->input('name');
        $places = [];
        if ($name) {
            $ip = $request->getClientIp();
            $location = $this->findIp($ip);
            $result = BaiduMap::instance()->placeSuggestion($name,$location[1]??'上海',$request->input('latitude'),$request->input('longitude'));
            $data = $result['result'];
            foreach ($data as $item) {
                $places[] = [
                    'name' => $item['name'],
                    'address' => $item['city'].$item['district'],
                    'distance' => null
                ];
            }
        } else {
            $result = BaiduMap::instance()->geocoder($request->input('latitude'),$request->input('longitude'));
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
        }
        $return = [
            'data' => $places,
            'current_page' => 1,
            'per_page' => count($places) + 10,
            'from' => 1,
            'to' => count($places)
        ];

        return self::createJsonData(true,$return);
    }

    //附近的人
    public function nearbyUser(Request $request){
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $page = $request->input('page',1);
        $user = $request->user();
        if ($longitude) {
            $geohash = new GeoHash();

            $hash = $geohash->encode($latitude, $longitude);

            // 决定查询范围，值越大，获取的范围越小
            // 当geohash base32编码长度为8时，精度在19米左右，而当编码长度为9时，精度在2米左右，编码长度需要根据数据情况进行选择。
            $pre_hash = substr($hash, 0, 3);

            //取出相邻八个区域
            $neighbors = $geohash->neighbors($pre_hash);
            array_push($neighbors, $pre_hash);

            $values = '';
            foreach ($neighbors as $key=>$val) {
                $values .= '\'' . $val . '\'' .',';
            }
            $values = substr($values, 0, -1);
        }

        $query = UserData::where('user_id','!=',$user->id);
        if ($longitude) {
            $query = $query->whereRaw('LEFT(`geohash`,3) IN ('.$values.')');
        }
        $userDatas = $query->orderBy('geohash','asc')->get();
        $per_page = 30;
        $return = [
            'current_page' => $page,
            'next_page_url' => null,
            'per_page'     => $per_page,
            'from'         => ($page-1) * $per_page + 1,
            'to'           => $page * $per_page,
            'data'         => []
        ];
        $data = [];
        foreach ($userDatas as $userData) {
            if (empty($longitude) || !is_numeric($userData->longitude) || !is_numeric($userData->latitude)) {
                $distance = '未知';
            } else {
                $distance = getDistanceByLatLng($userData->longitude,$userData->latitude,$longitude,$latitude);
                $distance = bcadd($distance,0,0);
            }
            $is_followed = 0;
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$userData->user_id)->first();
            if ($attention){
                $is_followed = 1;
            }
            $data[] = [
                'id' => $userData->user_id,
                'name' => $userData->user->name,
                'avatar' => $userData->user->avatar,
                'description'=>$userData->user->description,
                'is_followed' => $is_followed,
                'distance' => $distance,
                'distance_format' => distanceFormat($distance),
                'longitude' => $userData->longitude,
                'latitude'  => $userData->latitude
            ];
        }

        usort($data,function ($a,$b) {
            if ($a['distance'] == '未知') return -1;
            if ($a['distance'] == $b['distance']) return 0;
            return ($a['distance'] < $b['distance'])? -1 : 1;
        });
        $pageData = array_chunk($data,$per_page);
        $return['data'] = $pageData[$page-1]??[];
        $return['total'] = count($data);
        return self::createJsonData(true,$return);
    }

}