<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class AnswerController extends Controller {

    public function store(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'description' => 'required',
            'question_id'=> 'required',
            'device' => 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $description = $request->input('description');
        $quillContent = [];
        $quillContent['ops'][] = [
            'insert' => $description
        ];
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'quill/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $quillContent['ops'][] = [
                    'insert' => [
                        'image' => $img_url
                    ]
                ];
            }
        }
        return $this->storeAnswer($user,json_encode($quillContent),$request);
    }

}