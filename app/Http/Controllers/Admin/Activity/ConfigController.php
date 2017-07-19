<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use App\Http\Requests;

class ConfigController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validateRules = [
            'ac_first_ask_begin_time' => 'required|date_format:Y-m-d H:i',
            'ac_first_ask_end_time' => 'required|date_format:Y-m-d H:i|after:ac_first_ask_begin_time',
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            $data = $request->except('_token');
            unset($data['_token']);
            foreach($data as $name=>$value){
                Setting()->set($name,$value);
            }
            Setting()->clearAll();

            return $this->success(route('admin.activity.config'),'设置成功');
        }

        return view('admin.activity.config.index');
    }

}
