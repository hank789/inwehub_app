<?php

namespace App\Http\Controllers\Admin;

use App\Logic\TagsLogic;
use App\Models\Category;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Artisan;

class CategoryController extends AdminController
{

    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255|unique:categories',
    ];


    /**
     * 分类列表页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::orderBy('sort','asc')->orderBy('created_at','desc')->paginate(config('inwehub.admin.page_size'));
        return view("admin.category.index")->with(compact('categories'));
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
        if ($request->input('parent_id')) {
            $parent = Category::find($request->input('parent_id'));
            $category->type = $parent->type;
            $parent->grade = 1;
            $parent->save();
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
