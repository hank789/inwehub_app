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

    protected $fillable = ['category_id', 'status','tag_id', 'reviews', 'type','review_average_rate','review_rate_sum','updated_at'];

    public $timestamps = false;

    const TYPE_DEFAULT = 0;
    const TYPE_REVIEW = 1;

    public static function boot() {
        parent::boot();

        static::saving(function($tag){
            $tag->updated_at = date('Y-m-d H:i:s');
        });
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function tag()
    {
        return $this->belongsTo('App\Models\Tag');
    }
}
