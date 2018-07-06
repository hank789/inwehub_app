<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TagCategoryRel
 *
 * @property int $id
 * @property int $tag_id
 * @property int category_id
 * @mixin \Eloquent
 */
class TagCategoryRel extends Model
{
    protected $table = 'tag_category_rel';

    protected $fillable = ['category_id', 'tag_id'];

    public $timestamps = false;
}
