<?php

namespace App\Http\Controllers\Admin;

use App\Logic\TagsLogic;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class CategoryController extends AdminController
{

    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255|unique:categories',
        'sort' => 'required|integer',
        'summary' => 'required'
    ];


    /**
     * 分类列表页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();
        $query = Category::orderBy('created_at','desc');
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%');
        }
        if( isset($filter['id']) && $filter['id'] ){
            $query->where('id',$filter['id']);
        }
        if( isset($filter['parent_id']) && $filter['parent_id']>=0 ){
            $query->where('parent_id',$filter['parent_id']);
        }
        $categories = $query->paginate(config('inwehub.admin.page_size'));
        return view("admin.category.index")->with(compact('categories','filter'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flash();
        $this->validate($request,$this->validateRules);
        $types = $request->input("types",'articles');
        $formData = $request->all();
        $formData['type'] = $types;
        if ($formData['parent_id']) {
            $parent = Category::find($formData['parent_id']);
            $formData['type'] = $parent->type;
            $formData['grade'] = 0;
            $parent->grade = 1;
            $parent->save();
        } else {
            $formData['parent_id'] = 0;
        }
        if($request->hasFile('icon')){
            $file = $request->file('icon');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'category/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $formData['icon'] = $img_url;
        }
        Category::create($formData);
        TagsLogic::delCache();
        return $this->success(route('admin.category.index'),'分类添加成功');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::find($id);
        if(!$category){
            return $this->error(route('admin.category.index'),'分类不存在，请核实');
        }
        return view('admin.category.edit')->with(compact('category'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if(!$category){
            return $this->error(route('admin.category.index'),'分类不存在，请核实');
        }

        $this->validateRules['slug'] = "required|max:255|unique:categories,slug,".$category->id;

        $oldParentId = $category->parent_id;
        $this->validate($request,$this->validateRules);
        $category->name = $request->input('name');
        $category->slug = $request->input('slug');
        $category->sort = $request->input('sort');
        $category->status = $request->input('status');
        $category->parent_id = $request->input('parent_id');
        $category->summary = $request->input('summary');
        if ($request->input('parent_id')) {
            $parent = Category::find($request->input('parent_id'));
            $category->type = $parent->type;
            $parent->grade = 1;
            $parent->save();
        } else {
            $formData['parent_id'] = 0;
            $formData['grade'] = 0;
        }

        if($request->hasFile('icon')){
            $file = $request->file('icon');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'category/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $category->icon = $img_url;
        }

        $category->save();
        if ($oldParentId) {
            $oldParent = Category::find($oldParentId);
            $children = Category::where('parent_id',$oldParentId)->count();
            if ($children <= 0) {
                $oldParent->grade = 0;
                $oldParent->save();
            }
        }
        TagsLogic::delCache();
        return $this->success(route('admin.category.index'),'分类添加成功');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Category::destroy($request->input('ids'));
        TagsLogic::delCache();
        return $this->success(route('admin.category.index'),'分类删除成功');
    }
}
