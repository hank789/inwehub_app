<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller
{
    public function index()
    {
        return view('web::index');
    }
}