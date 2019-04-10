<?php namespace App\Services;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2018/9/14 下午5:48
 * @email:    hank.HuiWang@gmail.com
 */

class RuoKuaiService {

    public static function dama($imageData,$typeid = 3060) {
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

}