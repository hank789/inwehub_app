<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/1/7
 * Time: 下午6:50
 */

namespace App\Models\Relations;


trait MorphManyFeedbackTrait
{

    public function feedbacks()
    {
        return $this->morphMany('App\Models\Feedback', 'source');
    }

}