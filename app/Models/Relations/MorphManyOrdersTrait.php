<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/1/7
 * Time: 下午6:50
 */

namespace App\Models\Relations;


trait MorphManyOrdersTrait
{

    public function orders()
    {
        return $this->morphToMany('App\Models\Pay\Order', 'pay_order_gable',null,null,'pay_order_id');
    }

}