<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/6 下午2:57
 * @email: wanghui@yonglibao.com
 */

use App\Traits\ApiValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\CreateJsonResponseData;



class Controller extends BaseController
{
    use DispatchesJobs, ApiValidatesRequests, CreateJsonResponseData, \App\Traits\BaseController;

}