<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Recommendation
 *
 * @property int $id
 * @property string $subject
 * @property string $url
 * @property string $logo
 * @property bool $sort
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereLogo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Recommendation whereUrl($value)
 * @mixin \Eloquent
 */
class Recommendation extends Model
{
    protected $table = 'recommendations';
    protected $fillable = ['subject','url','logo','sort','status'];

}
