<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Test
 *
 * @mixin \Eloquent
 */
class Test extends Model
{
    protected $table = 'test';
    protected $fillable = ['uuid', 'text'];
}
