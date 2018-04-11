<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: wanghui@yonglibao.com
 */

class ServiceController extends Controller
{
    public function register()
    {
        return view('h5::service');
    }

    public function about(){
        return view('h5::test');
    }

}