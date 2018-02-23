<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$filter='all')
    {

        $validator = Validator::make($request->all(), [
            'query' => 'required|max:128',
        ]);

        if ($validator->fails())
        {
            return $this->error(route('website.index'),'搜索关键词不能为空');
        }

        $word = trim($request->input('query'));

        $folder = '';
        if($filter === 'all' || $filter === 'feeds'){
            $filter = 'feeds';
            $folder = 'Feed\\';
        }
        $model =  App::make('App\Models\\'.$folder.ucfirst(str_singular($filter)));
        $list = $model::search($word)->paginate(15);



        return view('theme::search.index')->with('word',$word)->with('filter',$filter)->with('list',$list);
    }


}
