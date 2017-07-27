<?php namespace App\Api\Controllers\Project;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Company\Company;
use App\Models\Company\Project;
use App\Models\Company\ProjectDetail;
use App\Models\Tag;
use App\Services\City\CityData;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * @author: wanghui
 * @date: 2017/6/7 上午11:28
 * @email: wanghui@yonglibao.com
 */

class ProjectController extends Controller {


    public function info(Request $request)
    {
        $data = $request->all();
        if(isset($data['id']) && $data['id']){
            $project = Project::find($data['id']);
        }else {
            $project = Project::firstOrNew(['status'=>Project::STATUS_DRAFT]);
        }
        $images = [];
        $detail = ProjectDetail::firstOrNew(['project_id'=>$project->id]);

        if($project->id){
            $mediaItems = $project->getMedia('project');
            foreach($mediaItems as $mediaItem){
                $images[] = $mediaItem->getUrl();
            }
        }
        $return = [
            'project_id' => $project->id,
            'status'     => $project->status,
            'project_name' => $project->project_name,
            'project_type' => $project->project_type,
            'project_stage' => $project->project_stage,
            'project_description' => $project->project_description,
            'images' => $images,
            'worker_num' => $detail->worker_num,
            'worker_level' => $detail->worker_level,
            'project_amount' => $detail->project_amount,
            'billing_mode' => $detail->billing_mode,
            'project_begin_time' => $detail->project_begin_time,
            'project_cycle' => $detail->project_cycle,
            'work_intensity' => $detail->work_intensity,
            'remote_work' => $detail->remote_work,
            'travel_expense' => $detail->travel_expense,
            'work_address' => json_decode($detail->work_address,true),
            'company_name' => $detail->company_name,
            'company_description' => $detail->company_description,
            'company_industry_tags' => TagsLogic::formatTags($detail->tags()->where('category_id',9)->get()),
            'company_represent_person_is_self' => $detail->company_represent_person_is_self,
            'company_represent_person_name' => $detail->company_represent_person_name,
            'company_represent_person_title' => $detail->company_represent_person_title,
            'company_represent_person_phone' => $detail->company_represent_person_phone,
            'company_represent_person_email' => $detail->company_represent_person_email,
            'company_billing_title' => $detail->company_billing_title,
            'company_billing_bank' => $detail->company_billing_bank,
            'company_billing_account' => $detail->company_billing_account,
            'company_billing_taxes' => $detail->company_billing_taxes,
            'qualification_requirements' => json_decode($detail->qualification_requirements),
            'other_requirements' => json_decode($detail->other_requirements),
            'is_view_resume' => $detail->is_view_resume,
            'is_apply_request' => $detail->is_apply_request,

        ];

        return self::createJsonData(true,$return);

    }

    public function myList(Request $request)
    {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $query = $request->user()->companyProjects();
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $items = $query->where('status','!=',Project::STATUS_DRAFT)->orderBy('id','DESC')->paginate(10);
        $list = [];
        foreach($items as $item){
            $info = $item->toArray();
            $detail = ProjectDetail::findOrNew($info['id']);
            $list[] = [
                'id' => $item['id'],
                'project_name' => $item['project_name'],
                'company_represent_person_name' => $detail->company_represent_person_name,
                'company_name' => $detail->company_name,
                'status'       => $item['status'],
                'updated_at'   => (string)$detail->updated_at
            ];
        }
        return self::createJsonData(true,$list);
    }


    public function publishStepOne(Request $request)
    {
        $validateRules = [
            'project_name'      => 'required|max:1024',
            'project_type'      => 'required|in:1,2',
            'project_stage'     => 'required|in:1,2,3',
            'project_description'     => 'required|max:3072'
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if(RateLimiter::instance()->increase('project:apply',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();

        $newData = [
            'user_id' => $user_id,
            'project_name' => $data['project_name'],
            'project_type' => $data['project_type'],
            'project_stage' => $data['project_stage'],
            'project_description' => $data['project_description'],
            'status' => Project::STATUS_DRAFT
        ];
        if(isset($data['project_id']) && $data['project_id']){
            $project = Project::findOrFail($data['project_id']);
            if($project->user_id != $user_id) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            $project->update($newData);
        } else {
            $project = Project::create($newData);
        }
        if(isset($data['deleted_images']) && $data['deleted_images']){
            $mediaItems = $project->getMedia('project');
            $mediaItemUrls = [];
            foreach($mediaItems as $mediaItem){
                $mediaItemUrls[$mediaItem->getUrl()] = $mediaItem;
            }
            foreach($data['deleted_images'] as $deleted_image){
                if(isset($mediaItemUrls[$deleted_image])){
                    $mediaItemUrls[$deleted_image]->delete();
                }
            }
        }

        $image_name = 'image_';
        for($i=0;$i<=9;$i++){
            $filename = $image_name.$i;
            if($request->hasFile($filename)){
                $file = $request->file($filename);
                $extension = strtolower($file->getClientOriginalExtension());
                $extArray = array('png', 'gif', 'jpeg', 'jpg');
                if(in_array($extension, $extArray)){
                    $project->addMediaFromRequest($filename)->setFileName(md5($file->getFilename()).'.'.$extension)->toMediaCollection('project');
                }
            }elseif(isset($data[$filename])) {
                $project->addMediaFromBase64($data[$filename])->toMediaCollection('project');
            }
        }
        $images_url = [];
        foreach($project->getMedia('project') as $img){
            try{
                $images_url[] = $img->getUrl();
            } catch (\Exception $e) {

            }
        }



        return self::createJsonData(true,['id'=>$project->id,'images'=>$images_url]);
    }

    public function publishStepTwo(Request $request)
    {
        $validateRules = [
            'project_id'      => 'required|integer',
            'worker_num'      => 'required|in:1,2,3,4,5,6,7',
            'worker_level'     => 'required|in:1,2,3',
            'project_amount'     => 'required|numeric',
            'billing_mode'     => 'required|in:1,2',
            'project_begin_time'     => 'required|max:12',
            'project_cycle'     => 'required|in:1,2,3,4,5,6,7,8,9',
            'work_intensity'     => 'required|in:1,2,3,4,5,6,7,8,9',
            'remote_work'     => 'required|in:1,2',
            'travel_expense'     => 'required|in:1,2',
            'work_address'     => 'required|array',
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;
        if(RateLimiter::instance()->increase('project:apply',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();

        $project = Project::find($data['project_id']);
        if(!$project){
            throw new ApiException(ApiException::PROJECT_NOT_FIND);
        }
        if($project->user_id != $user_id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $newData = [
            'user_id' => $user_id,
            'project_id' => $data['project_id'],
            'worker_num' => $data['worker_num'],
            'worker_level' => $data['worker_level'],
            'project_amount' => $data['project_amount'],
            'billing_mode' => $data['billing_mode'],
            'project_begin_time' => $data['project_begin_time'],
            'project_cycle' => $data['project_cycle'],
            'work_intensity' => $data['work_intensity'],
            'remote_work' => $data['remote_work'],
            'travel_expense' => $data['travel_expense'],
            'work_address' => json_encode($data['work_address'])
        ];
        $detail = ProjectDetail::find($data['project_id']);
        if($detail){
            $detail->update($newData);
        } else {
            ProjectDetail::create($newData);
        }

        return self::createJsonData(true,['id'=>$project->id]);
    }

    public function publishStepThree(Request $request)
    {
        $validateRules = [
            'project_id'      => 'required|integer',
            'company_name'      => 'required|max:1024',
            'company_description'     => 'required|max:3072',
            'company_industry_tags'     => 'required|array',
            'company_represent_person_is_self'     => 'required|in:0,1',
            'company_represent_person_name'     => 'required|max:64',
            'company_represent_person_title'     => 'required|max:64',
            'company_represent_person_phone'     => 'required|max:64',
            'company_represent_person_email'     => 'required|email|max:68',
            'company_billing_title'     => 'max:68',
            'company_billing_bank'     => 'max:68',
            'company_billing_account'     => 'max:68',
            'company_billing_taxes'     => 'max:68',
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;
        if(RateLimiter::instance()->increase('project:apply',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();

        $project = Project::find($data['project_id']);
        if(!$project){
            throw new ApiException(ApiException::PROJECT_NOT_FIND);
        }
        if($project->user_id != $user_id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }


        $newData = [
            'project_id' => $data['project_id'],
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'],
            'company_represent_person_is_self' => $data['company_represent_person_is_self'],
            'company_represent_person_name' => $data['company_represent_person_name'],
            'company_represent_person_title' => $data['company_represent_person_title'],
            'company_represent_person_phone' => $data['company_represent_person_phone'],
            'company_represent_person_email' => $data['company_represent_person_email'],
            'company_billing_title' => $data['company_billing_title'],
            'company_billing_bank' => $data['company_billing_bank'],
            'company_billing_account' => $data['company_billing_account'],
            'company_billing_taxes' => $data['company_billing_taxes']

        ];
        $detail = ProjectDetail::findOrFail($data['project_id']);
        $detail->update($newData);

        $industry_tags = $data['company_industry_tags']?implode(',',$data['company_industry_tags']):'';

        $tags = trim($industry_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSaveByIds($tags,$detail);
        }

        return self::createJsonData(true,['id'=>$project->id]);
    }

    public function publishStepFour(Request $request)
    {
        $validateRules = [
            'project_id'      => 'required|integer',
            'qualification_requirements'      => 'nullable|array',
            'other_requirements'     => 'nullable|array',
            'is_view_resume'     => 'required|in:0,1',
            'is_apply_request'     => 'required|in:0,1'
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;
        if(RateLimiter::instance()->increase('project:apply',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();

        $project = Project::find($data['project_id']);
        if(!$project){
            throw new ApiException(ApiException::PROJECT_NOT_FIND);
        }

        if($project->user_id != $user_id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }


        $newData = [
            'project_id' => $data['project_id'],
            'qualification_requirements' => json_encode($data['qualification_requirements']),
            'other_requirements' => json_encode($data['other_requirements']),
            'is_view_resume' => $data['is_view_resume'],
            'is_apply_request' => $data['is_apply_request'],
        ];
        $detail = ProjectDetail::findOrFail($data['project_id']);
        $detail->update($newData);
        $project->status = Project::STATUS_PENDING;
        $project->save();

        return self::createJsonData(true,['id'=>$project->id]);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        $project = Project::findOrFail($id);
        if($project->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $project->delete();
        return self::createJsonData(true,['id'=>$id]);
    }


}