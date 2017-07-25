<?php

namespace App\Models\Relations;


trait BelongsToProjectTrait
{
    /**
     * Get the user relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Company\Project');
    }
}