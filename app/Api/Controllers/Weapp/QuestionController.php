<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
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
        \Log::info('test',$request->all());
    }

    public function myList(Request $request){

    }

    public function allList(Request $request){

    }

    public function info(Request $request){

    }
}