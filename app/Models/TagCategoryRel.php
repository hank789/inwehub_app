<?php

namespace App\Models;

use App\Logic\WilsonScoreNorm;
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

    protected $fillable = ['category_id', 'status','tag_id', 'reviews', 'type','review_average_rate','review_rate_sum','support_rate','updated_at'];

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

    public function calcRate() {
        $submissions = Submission::where('category_id',$this->tag_id)->where('status',1)->get();
        $rates = [];
        foreach ($submissions as $submission) {
            if (!isset($submission->data['category_ids'])) {
                continue;
            }
            if (is_array($submission->data['category_ids']) && in_array($this->category_id,$submission->data['category_ids'])) {
                $rates[] = $submission->rate_star;
            }
        }
        $this->reviews = count($rates);
        $this->review_rate_sum = array_sum($rates);
        if ($this->reviews > 0) {
            $info = varianceCalc($rates);
            $this->review_average_rate = WilsonScoreNorm::instance($info['average'],$this->reviews)->score();
        } else {
            $this->review_average_rate = 0;
        }
        $this->save();
    }

}
