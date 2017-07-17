<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notice
 *
 * @property int $id
 * @property string $subject
 * @property string $url
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Notice whereUrl($value)
 * @mixin \Eloquent
 */
class Notice extends Model
{
    protected $table = 'notices';
    protected $fillable = ['subject', 'url','status','img_url','order'];

}
