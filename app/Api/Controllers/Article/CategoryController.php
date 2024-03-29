<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/11/14 上午11:07
 * @email: hank.huiwang@gmail.com
 */

class CategoryController extends Controller {

    /**
     * Searches categories. Mostly used for submiting new submissions.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategories(Request $request)
    {
        $name = $request->input('name');
        $query = Category::query()->where('slug','like','channel_%');
        if ($name) {
            $query = $query->where('name', 'like', '%'.$request->name.'%');
        }

        $data = $query->select('name','id')->take(100)->get()->pluck('name','id');
        $list = [];
        foreach ($data as $cid=>$cname){
            $list[] = [
                'value' => $cid,
                'text'  => $cname
            ];
        }
        return self::createJsonData(true, $list);
    }

}