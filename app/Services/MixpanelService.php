<?php
/**
 * @author: wanghui
 * @date: 2018/10/12 下午7:15
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Services;

/**
 * Class MixpanelService
 * https://mixpanel.com/help/reference/data-export-api
 * @package App\Services
 * // Example usage
// $api_secret = 'your secret';
//
// $mp = new MixpanelService::instance();
// $data = $mp->request(array('events', 'properties'), array(
//     'event' => 'pages',
//     'name' => 'page',
//     'type' => 'unique',
//     'unit' => 'day',
//     'interval' => '20',
//     'limit' => '20'
// ));
//
// var_dump($data);
 */
class MixpanelService
{
    protected static $instance = null;

    private $api_url = 'https://mixpanel.com/api';
    private $version = '2.0';
    private $api_secret;
    private $tryTimes = 0;

    public function __construct()
    {
        $this->api_secret = 'ba980a919a7ae78c251492b836345d7c';
    }
    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function request($methods, $params, $format='json') {
        // $end_point is an API end point such as events, properties, funnels, etc.
        // $method is an API method such as general, unique, average, etc.
        // $params is an associative array of parameters.
        // See http://mixpanel.com/api/docs/guides/api/
        $this->tryTimes++;
        $params['format'] = $format;

        $param_query = '';
        foreach ($params as $param => &$value) {
            if (is_array($value))
                $value = json_encode($value);
            $param_query .= '&' . urlencode($param) . '=' . urlencode($value);
        }

        $uri = '/' . $this->version . '/' . join('/', $methods) . '/';
        $request_url = $uri . '?' . $param_query;

        $headers = array("Authorization: Basic " . base64_encode($this->api_secret));
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->api_url . $request_url);
        curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);

        $res = json_decode($data,true);
        if (empty($res) && $this->tryTimes <= 3) return $this->request($methods,$params,$format);
        return $res;
    }

}