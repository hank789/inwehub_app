<?php namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{

    /*编辑器图片上传*/
    public function upload(Request $request)
    {
        $validateRules = [
            'img_name' => 'required|max:'.config('inwehub.upload.image.max_size'),
        ];
        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if($request->hasFile('img_name')){
            $file_0 = $request->file('img_name');
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'answer/'.$user_id.'/'.date('Ymd').'/'.md5($file_0->getFilename()).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $head_img_url_0 = Storage::disk('oss')->url($file_name);
                return self::createJsonData(true,['url'=>$head_img_url_0]);
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'格式错误');
            }
        } else {
            $img_name = $request->input('img_name');
            $urls = parse_url($img_name);
            if (isset($urls['scheme'])) {
                $file_name = 'attachments/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                Storage::disk('oss')->put($file_name,file_get_contents($img_name));
                return self::createJsonData(true,['url'=>Storage::url($file_name)]);
            }
        }
        return self::createJsonData(false,[],ApiException::BAD_REQUEST,'格式错误');
    }

}
