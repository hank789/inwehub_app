<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/6/9 下午1:51
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Support\Facades\Storage;

class QuillLogic {

    public static function parseTmages($json_content){
        $deltas = json_decode($json_content, true);
        if ($deltas !== null && count($deltas) > 0) {
            $ops = $deltas['ops'];
            foreach ($ops as &$delta) {
                if (array_key_exists('insert', $delta) === true &&
                    is_array($delta['insert']) === true &&
                    isset($delta['insert']['image'])) {
                    $base64 = $delta['insert']['image'];
                    $file_name = 'quill/'.date('Ymd').md5($base64).'.png';
                    Storage::disk('oss')->put($file_name,$base64);
                    $img_url = Storage::disk('oss')->url($file_name);
                    $delta['insert']['image'] = $img_url;
                }
            }
            $deltas['ops'] = $ops;
            return $deltas;
        } else {
            return false;
        }
    }

}