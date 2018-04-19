<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: wanghui@yonglibao.com
 */

class WeappController extends Controller
{
    public function getDemandShareLongInfo($id)
    {
        return view('h5::weapp.demandShareLong');
    }

    public function getDemandShareShortInfo($id){
        return view('h5::weapp.demandShareShort');
    }

}