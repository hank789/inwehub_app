<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, \App\Traits\BaseController;

    /**
     * 操作成功提示
     * @param $url string
     * @param $message 消息内容
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function success($url,$message)
    {
        Session::flash('message',$message);
        Session::flash('message_type',2);
        return redirect($url);
    }


    protected function error($url,$message)
    {
        Session::flash('message',$message);
        Session::flash('message_type',1);
        return redirect($url);
    }


    protected function showErrorMsg($url , $message){
        return view('errors.error')->with(compact('url','message'));
    }

    /**
     * 成功回调
     * @param $message
     */
    protected function ajaxSuccess($message){
        $data = array(
            'code' => 0,
            'message' => $message
        );
        return response()->json($data);
    }


    /**
     * 错误处理
     * @param $code
     * @param $message
     */
    protected function ajaxError($code,$message){
        $data = array(
            'code' => $code,
            'message' => $message
        );
        return response()->json($data);
    }
}
