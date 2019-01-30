<?php

namespace App\Http\Controllers\Admin\Review;

use App\Http\Controllers\Admin\AdminController;
use App\Logic\TagsLogic;
use App\Models\Category;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:128',
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
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->leftJoin('tags','tag_id','=','tags.id');

        $filter['category_id'] = $request->input('category_id',-1);

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', $filter['word'].'%')->orderByRaw('case when name like "'.$filter['word'].'" then 0 else 2 end');
        }

        /*分类过滤*/
        if( $filter['category_id']> 0 ){
            $query->where('tag_category_rel.category_id','=',$filter['category_id']);
        }

        if( isset($filter['status']) && $filter['status'] >=0 ){
            $query->where('status',$filter['status']);
        }

        if( isset($filter['id']) && $filter['id'] ){
            $query->where('tag_id',$filter['id']);
        }

        if (isset($filter['onlyZh']) && $filter['onlyZh']) {
            $query->whereRaw('length(name)!=char_length(name)');
        }

        if (isset($filter['order_by']) && $filter['order_by']) {
            $orderBy = explode('|',$filter['order_by']);
            $query->orderBy('tag_category_rel.'.$orderBy[0],$orderBy[1]);
        } elseif (!(isset($filter['word']) && $filter['word'])){
            $query->orderBy('tag_category_rel.tag_id','desc');
        }

        $fields = ['tag_category_rel.id','tag_category_rel.tag_id','tag_category_rel.category_id','tag_category_rel.status','tag_category_rel.reviews','tags.name','tags.logo','tags.summary','tags.created_at'];
        $tags = $query->select($fields)->paginate(20);
        return view("admin.review.product.index")->with('tags',$tags)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.review.product.create');
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
        $category_ids = $request->input('category_id');
        unset($data['category_id']);
        $data['category_id'] = $category_ids[0];
        $tag = Tag::where('name',$request->input('name'))->first();
        if (!$tag) {
            $tag = Tag::create($data);
        } else {
            unset($data['name']);
            $tag->update($data);
        }
        TagsLogic::cacheProductTags($tag);
        foreach ($category_ids as $category_id) {
            if ($category_id<=0) continue;
            $rel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category_id)->first();
            if (!$rel) {
                TagCategoryRel::create([
                    'tag_id' => $tag->id,
                    'category_id' => $category_id,
                    'type' => TagCategoryRel::TYPE_REVIEW,
                    'status' => $request->input('status',1)
                ]);
            }
        }
        $tag->updated_at = date('Y-m-d H:i:s');
        $tag->save();
        TagsLogic::delCache();
        TagsLogic::delRelatedProductsCache();
        return $this->success(route('admin.review.product.index'),'产品创建成功');
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
        $tag = TagCategoryRel::find($id);
        if(!$tag){
           abort(404);
        }
        //$categories = $tag->categories->pluck('id')->toArray();
        return view('admin.review.product.edit')->with('tag',$tag);
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
        $tagRel = TagCategoryRel::find($id);
        if(!$tagRel){
            return $this->error(route('admin.review.product.index'),'产品不存在，请核实');
        }
        $oldStatus = $tagRel->status;
        $newStatus = $request->input('status');
        $tag = Tag::find($tagRel->tag_id);
        $this->validateRules['name'] = 'required|max:128|unique:tags,name,'.$tag->id;
        $this->validate($request,$this->validateRules);
        $tag->name = $request->input('name');
        $tag->summary = $request->input('summary');
        $tag->description = strip_tags($request->input('description'));
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $tag->logo = $img_url;
        }
        $tag->save();
        TagsLogic::cacheProductTags($tag);
        $category_ids = $request->input('category_id');
        $returnUrl = url()->previous();
        $delete = true;
        if ($category_ids) {
            foreach ($category_ids as $category_id) {
                if ($category_id<=0) continue;
                if ($category_id == $tagRel->category_id) $delete = false;

                $newRel = TagCategoryRel::firstOrCreate([
                    'tag_id' => $tag->id,
                    'category_id' => $category_id
                ],[
                    'tag_id' => $tag->id,
                    'category_id' => $category_id,
                    'type' => TagCategoryRel::TYPE_REVIEW,
                    'status' => $request->input('status',1)
                ]);
                if ($newRel->status != $request->input('status',1)) {
                    $newRel->status = $request->input('status',1);
                    $newRel->save();
                }
            }
            if ($delete) {
                TagCategoryRel::where('id',$id)->where('reviews',0)->delete();
                $returnUrl = route('admin.review.product.index');
            }
        }
        if ($newStatus == 1 && $oldStatus == 0) {
            $submissions = Submission::where('category_id',$tagRel->tag_id)->where('status',1)->get();
            $count = 0;
            $rates = 0;
            foreach ($submissions as $submission) {
                if (is_array($submission->data['category_ids']) && in_array($tagRel->category_id,$submission->data['category_ids'])) {
                    $count++;
                    $rates+=$submission->rate_star;
                }
            }
            $tagRel->calcRate();
            $info = Tag::getReviewInfo($tagRel->tag_id);
            Tag::where('id',$tagRel->tag_id)->update([
                'reviews' => $info['review_count']
            ]);
        }
        $tag->updated_at = date('Y-m-d H:i:s');
        $tag->save();
        if ($newStatus != $oldStatus) {
            TagsLogic::delRelatedProductsCache();
        }
        TagsLogic::delCache();
        return $this->success($returnUrl,'产品修改成功');
    }

    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryIds = explode(',',$request->input('category_id',0));
        $albumIds = explode(',',$request->input('album_id',0));
        $categoryIds = array_merge($categoryIds,$albumIds);
        if($ids){
            $idArray = explode(",",$ids);
            foreach ($idArray as $id) {
                $tag = Tag::find($id);
                $cids = $tag->categories->pluck('id')->toArray();
                foreach ($categoryIds as $categoryId) {
                    if ($categoryId<=0) continue;
                    foreach ($cids as $key=>$cid) {
                        if ($categoryId == $cid) {
                            unset($cids[$key]);
                        }
                    }
                    TagCategoryRel::firstOrCreate([
                        'tag_id' => $id,
                        'category_id' => $categoryId
                    ],
                        [
                            'tag_id' => $id,
                            'category_id' => $categoryId,
                            'type' => TagCategoryRel::TYPE_REVIEW
                        ]);
                }
                \Log::info('test',$cids);
                if (count($cids)) {
                    TagCategoryRel::where('tag_id',$id)->whereIn('category_id',$cids)->delete();
                }
                $tag->updated_at = date('Y-m-d H:i:s');
                $tag->save();
            }
        }
        return response('success');
    }

    //审核
    public function setVeriy(Request $request) {
        $articleId = $request->input('id');
        $article = TagCategoryRel::find($articleId);
        if ($article->status) {
            $article->status = 0;
        } else {
            $article->status = 1;
        }
        $article->save();
        if ($article->status) {
            return response('failed');
        }
        return response('success');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $tagIds = $request->input('ids');
        TagCategoryRel::where('id',$tagIds)->delete();
        return response('success');
    }

}
