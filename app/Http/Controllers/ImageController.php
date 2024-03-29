<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{

    /**
     * 显示用户头像
     * @param $avatar_name
     * @return mixed
     */
    public function avatar($avatar_name)
    {
        list($user_id,$size) = explode('_',str_replace(".jpg",'',$avatar_name));
        $user = User::find($user_id);
        return $user->avatar;
    }



    public function show($image_name)
    {
        $imageFile = storage_path('app/'.str_replace("-","/",$image_name));
        if(!is_file($imageFile)){
            abort(404);
        }
        return response()->file($imageFile);
    }



    /*编辑器图片上传*/
    public function upload(Request $request)
    {
        $validateRules = [
            'file' => 'required|image|max:'.config('inwehub.upload.image.max_size'),
        ];

        if($request->hasFile('file')){
            $this->validate($request,$validateRules);
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'attachments/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            return response($img_url);
        }
        return response('error');

    }

}
