<?php namespace App\Services;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2018/9/14 下午5:48
 * @email:    hank.HuiWang@gmail.com
 */

class RuoKuaiService {

    public static function ruokuai($imageData,$typeid = 3060) {
        $damaUrl = 'http://api.ruokuai.com/create.json';
        $ch = curl_init();
        $fileName = time().str_random(7).'.jpg';
        Storage::disk('local')->put('attachments/'.$fileName,$imageData);
        $imagePath = storage_path('app/attachments/'.$fileName);
        \Log::info('RuoKuaiService::dama',[$imagePath]);
        $curlFile = curl_file_create($imagePath,'image/jpeg','a.jpg');
        $postFields = array('username' => 'hankwang',
            'password' => md5('hank8831'),
            'typeid' => $typeid,	//4位的字母数字混合码   类型表http://www.ruokuai.com/pricelist.aspx
            'timeout' => 60,	//中文以及选择题类型需要设置更高的超时时间建议90以上
            'softid' => 1,	//改成你自己的
            'softkey' => 'b40ffbee5c1cf4e38028c197eb2fc751',	//改成你自己的
            'image' => $curlFile
        );

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL,$damaUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);	//设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);


        $result = curl_exec($ch);

        curl_close($ch);

        \Log::info('RuoKuaiService::dama',[$result]);
        Storage::disk('local')->delete('attachments/'.$fileName);
        return json_decode($result,true);
    }

    public static function dama($imageData,$typeid = 3060) {
        $result = self::ruokuai($imageData,$typeid);
        if (!isset($result['Result'])) {
            $newType = '1006';
            switch ($typeid) {
                case 3060:
                    $newType = '1006';
                    break;
                case 2040:
                    $newType = '3004';
                    break;
            }
            $result = self::yundama($imageData,$newType);
        }
        return $result;
    }

    public static function yundama($imageData,$typeid = '1006') {
        $damaUrl = 'http://api.yundama.com/api.php?method=upload';
        $ch = curl_init();
        $fileName = time().str_random(7).'.jpg';
        Storage::disk('local')->put('attachments/'.$fileName,$imageData);
        $imagePath = storage_path('app/attachments/'.$fileName);
        \Log::info('RuoKuaiService::dama',[$imagePath]);
        $curlFile = curl_file_create($imagePath,'image/jpeg','a.jpg');
        $postFields = array('username' => 'hankwang',
            'password' => 'Wanghui8831',
            'codetype' => $typeid,	//4位的字母数字混合码   类型表http://www.ruokuai.com/pricelist.aspx
            'timeout' => 60,	//中文以及选择题类型需要设置更高的超时时间建议90以上
            'appid' => 1,	//改成你自己的
            'appkey' => '22cc5376925e9387a23cf797cb9ba745',	//改成你自己的
            'file' => $curlFile
        );

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL,$damaUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);	//设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);


        $result = curl_exec($ch);

        curl_close($ch);

        \Log::info('RuoKuaiService::dama',[$result]);
        Storage::disk('local')->delete('attachments/'.$fileName);
        $re = json_decode($result,true);
        if ($re['ret'] == 0) {
            $count = 4;
            while ($count >=0) {
                $count--;
                $re2 = json_decode(file_get_contents('http://api.yundama.com/api.php?method=result&cid='.$re['cid']),true);
                if (isset($re2['text']) && $re2['text']) {
                    return ['Result' => $re2['text']];
                }
                sleep(1);
            }
        }
    }

    public static function jianjiaoshuju($imageData,$typeid = 'ne6') {
        $host = "http://apigateway.jianjiaoshuju.com";
        $path = "/api/v_1/yzm.html";
        $method = "POST";
        $appcode = "EDB46FC62F97E79270D892829E73A135";
        $appKey = "AKID1e7b2a870d79b80a5c4f9153d6e52772";
        $appSecret = "938005655fb599abbb0f8b12b2684da8";
        $headers = array();
        array_push($headers, "appcode:" . $appcode);
        array_push($headers, "appKey:" . $appKey);
        array_push($headers, "appSecret:" . $appSecret);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "";
        $bodys = "v_pic=".base64_encode($imageData)."&v_type=".$typeid;
        $url = $host . $path;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $result = curl_exec($curl);
        curl_close($curl);

        \Log::info('RuoKuaiService::jianjiaoshuju',[$result]);
        return json_decode($result,true);
    }

}