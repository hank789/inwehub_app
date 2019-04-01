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
class ProductUserRel extends Model
{
    protected $table = 'product_user_rel';

    protected $fillable = ['user_id', 'status','tag_id'];

    public $timestamps = false;

    const STATUS_BLOCK = 0;
    const STATUS_OK = 1;

    public function tag()
    {
        return $this->belongsTo('App\Models\Tag');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
