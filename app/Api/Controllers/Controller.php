<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/6 下午2:57
 * @email: hank.huiwang@gmail.com
 */

use App\Traits\ApiValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as AppController;
use App\Traits\CreateJsonResponseData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\BaseController;



class Controller extends AppController
{
    use AuthorizesRequests, DispatchesJobs, ApiValidatesRequests, CreateJsonResponseData, BaseController;

}