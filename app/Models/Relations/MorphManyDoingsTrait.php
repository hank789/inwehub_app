<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/1/7
 * Time: 下午6:50
 */

namespace App\Models\Relations;


trait MorphManyDoingsTrait
{

    public function doings()
    {
        return $this->morphMany('App\Models\Doing', 'source');
    }

}