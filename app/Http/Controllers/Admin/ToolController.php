<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/5/27
 * Time: 上午11:24
 */

namespace App\Http\Controllers\Admin;

use App\Logic\TagsLogic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ToolController extends AdminController
{

    /*清空缓存*/
    public function clearCache(Request $request)
    {
        if($request->isMethod('post')){
            $cacheItems = $request->input('cacheItems',[]);
            if(in_array('tags_question',$cacheItems)){
                TagsLogic::delCache();
            }
            if (in_array('home_index',$cacheItems)){
                Cache::forget('admin_index_dashboard');
            }

            return $this->success(route('admin.tool.clearCache'),'缓存更新成功');
        }
        return view('admin.tool.clearCache');

    }


    /*发送测试邮件*/
    public function sendTestEmail(Request $request){
        $validateRules = [
            'sendTo' => 'required|email',
            'content' => 'required|max:255',
        ];

        $this->validate($request,$validateRules);

        $mailData = $request->all();

        try{
            Mail::send('emails.test',$mailData, function($message) use ($mailData)
            {
                $message->to($mailData['sendTo'])->subject(Setting()->get('website_name').'邮件测试');
            });
            return response('ok');
        }catch (\Swift_SwiftException $e){
            return response($e->getMessage());
        }
    }

    public function upload(Request $request)
    {
        $validateRules = [
            'file' => 'required',
        ];
        $this->validate($request,$validateRules);
        if($request->hasFile('file')){
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