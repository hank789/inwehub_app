<?php
/**
 * Created by PhpStorm.
 * User: lacorey
 * Date: 16/9/20
 * Time: 下午6:34
 */

namespace App\Third\Push\Getui\Facades;


use Illuminate\Support\Facades\Facade;

class Getui extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Getui';
    }
}