<?php

namespace App\Models\Relations;


trait BelongsToProjectTrait
{
    /**
     * Get the user relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Company\Project');
    }
}