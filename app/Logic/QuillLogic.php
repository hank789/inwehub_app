<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/6/9 下午1:51
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Support\Facades\Storage;

class QuillLogic {

    public static function parseImages($json_content){
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
                        continue;
                    }
                    $url_type = explode('/',$url[0]);
                    $file_name = 'quill/'.date('Ymd').md5($base64).'.'.$url_type[1];
                    Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                    $img_url = Storage::disk('oss')->url($file_name);
                    $delta['insert']['image'] = $img_url;
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