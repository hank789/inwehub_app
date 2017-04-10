<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/10 下午3:53
 * @email: wanghui@yonglibao.com
 */
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UploadController extends Controller {

    public function uploadImg(Request $request) {
        $validateRules = [
            'file' => 'required|image|max:'.config('intervapp.upload.image.max_size'),
        ];

        if($request->hasFile('file')){
            $this->validate($request,$validateRules);
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'image/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::put($filePath,File::get($file));
            return self::createJsonData(true,['img_name'=>$filePath,'img_url'=>Storage::get($filePath)]);
        }
        return self::createJsonData(false,[],ApiException::BAD_REQUEST,'fail');
    }

    public function showImg(Request $request){
        $this->validate($request,['img_name'=>'required']);
        $filePath = $request->input('img_name');
        if(Storage::exists($filePath)){
            return self::createJsonData(true,['img_name'=>$filePath,'img_url'=>Storage::url($filePath)]);
        }else{
            return self::createJsonData(false,['img_name'=>$filePath,'img_url'=>''],ApiException::FILE_NOT_EXIST,'文件不存在');
        }
    }

}