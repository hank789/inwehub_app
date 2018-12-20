<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/6/9 下午1:51
 * @email: hank.huiwang@gmail.com
 */
use App\Jobs\UploadFile;
use Illuminate\Support\Facades\Storage;

class QuillLogic {

    public static function parseImages($json_content,$formatLink=true){
        $deltas = json_decode($json_content, true);
        if ($deltas !== null && count($deltas) > 0) {
            $ops = $deltas['ops'];
            foreach ($ops as &$delta) {
                if (array_key_exists('insert', $delta) === true &&
                    is_array($delta['insert']) === true &&
                    isset($delta['insert']['image'])) {
                    $base64 = $delta['insert']['image'];
                    $url = explode(';',$base64);
                    if(count($url) <=1){
                        $parse_url = parse_url($base64);
                        //非本地地址，存储到本地
                        if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                            $file_name = 'quill/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                            dispatch((new UploadFile($file_name,base64_encode(file_get_contents_curl($base64,false)))));
                            //Storage::disk('oss')->put($file_name,file_get_contents($base64));
                            $img_url = Storage::disk('oss')->url($file_name);
                            $delta['insert']['image'] = $img_url;
                        }
                        continue;
                    }
                    $url_type = explode('/',$url[0]);
                    $file_name = 'quill/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                    dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                    //Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                    $img_url = Storage::disk('oss')->url($file_name);
                    $delta['insert']['image'] = $img_url;
                } elseif ($formatLink && array_key_exists('insert', $delta) === true && is_array($delta['insert']) === false) {
                    $delta['insert'] = formatContentUrls($delta['insert']);
                }
            }
            $deltas['ops'] = $ops;
            return json_encode($deltas);
        } else {
            return false;
        }
    }

    public static function parseText($json_content){
        try {
            $quill = new \App\Third\Quill\Render($json_content, 'TEXT');
            return $quill->render();
        } catch (\Exception $e) {
            return $json_content;
        }
    }

    public static function parseHtml($json_content){
        try {
            $quill = new \App\Third\Quill\Render($json_content, 'HTML');
            return $quill->render();
        } catch (\Exception $e) {
            return $json_content;
        }
    }

}