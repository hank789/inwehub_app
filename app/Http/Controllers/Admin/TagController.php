<?php

namespace App\Http\Controllers\Admin;

use App\Logic\TagsLogic;
use App\Models\Category;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\UserTag;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TagController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:128|unique:tags',
        'url' => 'sometimes|max:128',
        'summary' => 'sometimes',
        'description' => 'sometimes|max:65535',
    ];


    /**
     *标签管理
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();
        $query = TagCategoryRel::leftJoin('tags','tag_id','=','tags.id');

        $filter['category_id'] = $request->input('category_id',-1);

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%')->orderByRaw('case when name like "'.$filter['word'].'" then 0 else 2 end');
        } else {
            $query->orderBy('tag_category_rel.tag_id','desc');
        }

        /*分类过滤*/
        if( $filter['category_id']> 0 ){
            $query->where('tag_category_rel.category_id','=',$filter['category_id']);
        }

        if( isset($filter['id']) && $filter['id'] ){
            $query->where('tag_id',$filter['id']);
        }
        $fields = ['tag_category_rel.id','tag_category_rel.tag_id','tag_category_rel.category_id','tag_category_rel.status','tag_category_rel.reviews','tags.name','tags.logo','tags.summary','tags.created_at'];

        $tags = $query->select($fields)->paginate(20);
        return view("admin.tag.index")->with('tags',$tags)->with('filter',$filter);


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tag.create');
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
        $data = $request->all();
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $data['logo'] = $img_url;
        }
        if ($data['category_id']) {
            $data['category_id'] = $data['category_id'][0];
        } else {
            $data['category_id'] = 0;
        }
        $tag = Tag::create($data);
        foreach ($request->input('category_id') as $category_id) {
            if ($category_id<=0) continue;
            $category = Category::find($category_id);
            TagCategoryRel::create([
                'tag_id' => $tag->id,
                'category_id' => $category_id,
                'type' => $category->type == 'enterprise_review'?TagCategoryRel::TYPE_REVIEW:TagCategoryRel::TYPE_DEFAULT
            ]);
        }
        TagsLogic::delCache();
        return $this->success(route('admin.tag.index'),'标签创建成功');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tag = Tag::find($id);
        if(!$tag){
           abort(404);
        }
        $categories = $tag->categories->pluck('id')->toArray();
        return view('admin.tag.edit')->with('tag',$tag)->with('tag_categories',$categories);
    }

    public function checkNameExist(Request $request, $id) {
        $name = $request->input('name');
        $exist = Tag::where('name',$name)->first();
        if ($exist && $exist->id != $id) {
            return response('failed');
        }
        return response('success');
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
        $request->flash();
        $tag = Tag::find($id);
        if(!$tag){
            return $this->error(route('admin.tag.index'),'话题不存在，请核实');
        }
        $mergeR = $request->input('mergeR');
        if ($mergeR == 1) {
            $this->validateRules['name'] = 'required|max:128';
        } else {
            $this->validateRules['name'] = 'required|max:128|unique:tags,name,'.$id;
        }

        $this->validate($request,$this->validateRules);
        $name = $request->input('name');
        if ($mergeR == 1) {
            $exist = Tag::where('name',$name)->first();
            if ($exist && $exist->id != $id) {
                //合并
                Taggable::where('tag_id',$id)->update(['tag_id'=>$exist->id]);
                UserTag::where('tag_id','=',$id)->update(['tag_id'=>$exist->id]);
                $tag->delete();
                return $this->success(route('admin.tag.index'),'标签合并成功');
            }
        }

        $oldCid = $tag->category_id;
        $tag->name = $name;
        $tag->summary = $request->input('summary');
        $tag->description = $request->input('description');
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $tag->logo = $img_url;
        }
        $tag->save();
        TagCategoryRel::where('tag_id',$tag->id)->where('reviews',0)->delete();
        foreach ($request->input('category_id') as $category_id) {
            if ($category_id<=0) continue;
            $category = Category::find($category_id);
            TagCategoryRel::firstOrCreate([
                'tag_id' => $tag->id,
                'category_id' => $category_id
            ],[
                'tag_id' => $tag->id,
                'category_id' => $category_id,
                'type' => $category->type == 'enterprise_review'?TagCategoryRel::TYPE_REVIEW:TagCategoryRel::TYPE_DEFAULT
            ]);
        }
        TagsLogic::delCache();
        return $this->success(url()->previous(),'标签修改成功');
    }

    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryIds = $request->input('category_id',0);
        if($ids){
            $idArray = explode(",",$ids);
            TagCategoryRel::whereIn('tag_id',$idArray)->where('reviews',0)->delete();
            foreach ($idArray as $id) {
                foreach ($categoryIds as $categoryId) {
                    if ($categoryId<=0) continue;
                    TagCategoryRel::firstOrCreate([
                        'tag_id' => $id,
                        'category_id' => $categoryId
                    ],[
                        'tag_id' => $id,
                        'category_id' => $categoryId
                    ]);
                }
            }
        }
        return $this->success(url()->previous(),'分类修改成功');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $tagIds = $request->input('id');
        Tag::destroy($tagIds);
        return $this->success(url()->previous(),'标签删除成功');
    }

}
