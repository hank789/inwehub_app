<?php
/**
 * @author: wanghui
 * @date: 2017/11/28 下午2:35
 * @email: hank.huiwang@gmail.com
 */

namespace App\Services;


class BaiduMap
{
    protected static $instance = null;

    private  $ak = '15jmwzuSXpwxzu51RaOQBLeE4lrD6gf8VcCn';	//服务核心,替换成自己的AK http://lbsyun.baidu.com/apiconsole/key?application=key
    //sn校验模式目前无法使用
    private  $check = 0;//请求校验方式 为 sn 校验方式时需改为1
    private  $sk = 'KvUw3VwjD7xtIeFl15tKbciV2x2qqdiEj'; //请求校验方式 为 sn 校验方式时需填写
    private  $method = 'GET';
    private  $output = 'json'; //json or xml
    private  $coord = 'bd09ll';//为空是百度墨卡托坐标,bd09ll 是百度经纬度坐标
    private  $coding = 'utf-8';//返回编码类型utf-8 or gbk
    private  $url = 'https://api.map.baidu.com/';

    public function __construct()
    {
        $this->ak = config('map.baidu.ak');
    }

    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * POI数据
     * @param  string  $query      [关键词][多个关键词用'$'隔开]
     * @param  integer $type       [0:默认通过城市检索][1:通过坐标圆点&半径检索][3:通过矩形框检索]
     * @param  string  $region     城市
     * @param  string  $location   [lat,lng][38.76623,116.43213]
     * @param  integer $radius     半径
     * @param  string  $bounds    [lat,lng(左下角坐标),lat,lng(右上角坐标)] [38.76623,116.43213,39.54321,116.46773]
     * @param  integer $scope      [1:基本][2:详细]
     * @param  integer $page_size  [每页展示条数][max=20*count($query)]
     * @param  integer $page_num   [页数]
     * @param  string  $tag        [标签项][用','分割] http://lbsyun.baidu.com/index.php?title=lbscloud/poitags
     * @param  string  $filter     过滤条件[http://lbsyun.baidu.com/index.php?title=webapi/guide/webservice-placeapi]
     * @param  integer $coord_type [1:wgs84ll即GPS经纬度][2:gcj02ll即国测局经纬度坐标][3:bd09ll即百度经纬度坐标][4:bd09mc即百度米制坐标]
     * @link http://lbsyun.baidu.com/index.php?title=webapi/guide/webservice-placeapi
     */
    public function place($query = '',$type = 0, $region = '上海',$location = '',$radius = 2000,$bounds = '',$scope = 2,$page_size = 10,$page_num = 0,$tag = '',$filter = '',$coord_type = 3){
        switch ($type) {
            case 0:
                $params['region'] = $region;
                break;
            case 1:
                $params['location'] = $location;
                if ($radius > 0) {
                    $params['radius'] = $radius;
                }
                break;
            case 2:
                $params['bounds'] = $bounds;
                break;
            default:
                $params['region'] = $region;
        }
        $params['query'] = $query;
        $params['scope'] = $scope;
        $params['city_limit'] = false;
        $params['page_size'] = $page_size;
        $params['page_num'] = $page_num;
        $params['tag'] = $tag;
        $params['filter'] = $filter;
        $params['coord_type'] = $coord_type;
        $params['output'] = $this->output;
        return $this->_sendHttp('place/v2/search',$params);
    }

    public function placeSuggestion($query,$region='上海',$lat='',$lng=''){
        $params = [];
        $params['query'] = $query;
        $params['region'] = $region;
        $params['city_limit'] = false;
        if ($lat && $lng) {
            $params['location'] = $lat.','.$lng;
        }
        $params['output'] = $this->output;
        return $this->_sendHttp('place/v2/suggestion',$params);
    }

    /**
     * 普通IP定位
     * @param  string $ip ip
     * @link http://lbsyun.baidu.com/index.php?title=webapi/ip-api
     */
    public function locationip($ip = '127.0.0.1'){
        $params['ip'] = $ip;
        $params['coor'] = $this->coord;
        $params['ak'] = $this->ak;
        return $this->_sendHttp('location/ip',$params);
    }
    /**
     * 高精度IP定位
     * @param  string  $ip         ip
     * @param  string  $qterm      pc|mb
     * @param  integer $extensions 1|2|3
     * @link http://lbsyun.baidu.com/index.php?title=webapi/high-acc-ip
     */
    public function highacciploc($ip = '127.0.0.1',$qterm = 'pc',$extensions = 3){
        $params['qcip'] = $ip;
        $params['qterm'] = $qterm;
        $params['extensions'] = $extensions;
        $params['coord'] = $this->coord;
        return $this->_sendHttp('highacciploc/v1',$params);
    }

    /**
     * 根据经纬度查找位置信息
     * @param $lat
     * @param $lng
     * @return mixed
     */
    public function geocoder($lat,$lng) {
        $params['location'] = $lat.','.$lng;
        $params['coordtype'] = $this->coord;
        $params['pois'] = 1;
        $params['output'] = $this->output;
        return $this->_sendHttp('geocoder/v2/',$params);
    }

    /**
     * 生成URL
     * @param  string $uri
     * @param  array $params
     */
    private function _sendHttp($uri,$params){
        if($this->method === 'GET'){
            $url = $this->url . $uri . '?ak=' . $this->ak;
            unset($params['ak']);
            foreach ($params as $key => $v) {
                $url .="&{$key}=" . urlencode($v);
            }
            \Log::info('test',[$url]);
            $data = $this->_curl($url);
        } else {
            $url = urlencode($this->url . $uri);
            $data = $this->_curl($url,$params);
        }
        return json_decode($data,true);
    }
    /**
     * 生成发送HTTP请求
     * @param  string $url
     * @param  array $postData
     */
    private function _curl($url,$postData = NULL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //https请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        if(is_array($postData) && 0 < count($postData)){
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}