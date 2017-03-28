<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\Models\XsSearch
 *
 * @mixin \Eloquent
 */
class XsSearch extends Model
{

    public static function getSearch(){
        /** @var Search $search */
        $search = App::make('search');
        return $search->search();
    }


}
