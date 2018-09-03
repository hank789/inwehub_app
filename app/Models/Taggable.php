<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Taggable
 *
 * @property int $id
 * @property int $tag_id
 * @property int $taggable_id
 * @property string $taggable_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereTagId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereTaggableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereTaggableType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Taggable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Taggable extends Model
{
    protected $table = 'taggables';

    protected $fillable = ['taggable_type', 'taggable_id', 'tag_id', 'is_display'];


    public static function hottest($type='all',$pageSize=20)
    {
       $tagIds = Tag::pluck('id');
       $query =  DB::table('taggables')->select('tag_id',DB::raw('COUNT(id) as total_num'))
            ->whereIn('tag_id',$tagIds);
       if($type=='questions'){
           $query->where('taggable_type','=','App\Models\Question');
       }elseif($type=='articles'){
           $query->where('taggable_type','=','App\Models\Article');
       }

       $taggables = $query->groupBy('tag_id')
            ->orderBy('total_num','desc')
            ->paginate($pageSize);
        return $taggables;
    }

    /*全局热门标签*/
    public static function globalHotTags( $type='all' )
    {
        return Cache::remember('hot_tags_'.$type,300,function() use($type){
            $tags = self::hottest($type,25);
            $tags->map(function($tag){
                $tagInfo = Tag::find($tag->tag_id);
                if(!$tagInfo){
                    $tag->name = '';
                }else{
                    $tag->name = $tagInfo->name;
                }
            });
            return $tags;
        });
    }

}
