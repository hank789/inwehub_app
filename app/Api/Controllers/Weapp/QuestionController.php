<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Models\WeappQuestion\WeappQuestion;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class QuestionController extends Controller {

    public function store(Request $request){
        $validateRules = [
            'description' => 'required|max:500',
            'is_public'=> 'required'
        ];
        $this->validate($request,$validateRules);

        $data = $request->all();
        $question = WeappQuestion::create([
            'title' => $data['description'],
            'is_public' => $data['is_public'],
            'status'    => 1
        ]);
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $question->addMediaFromRequest($image_file)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('weapp');
            }
        }
        \Log::info('test',$request->all());
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function addImage(Request $request){
        $validateRules = [
            'id' => 'required|integer',
            'image_file'=> 'required|image'
        ];
        $this->validate($request,$validateRules);

        $data = $request->all();
        $question = WeappQuestion::find($data['id']);
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $question->addMediaFromRequest($image_file)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('weapp');
            }
        }
        \Log::info('test',$request->all());
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function myList(Request $request){

    }

    public function allList(Request $request){

    }

    public function info(Request $request){

    }
}