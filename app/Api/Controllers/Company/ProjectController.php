<?php namespace App\Api\Controllers\Company;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Company\Company;
use App\Models\Company\Project;
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


    public function publishStepOne(Request $request)
    {
        $validateRules = [
            'project_name'      => 'required',
            'project_type'      => 'required|in:1,2',
            'project_stage'     => 'required|in:1,2,3',
            'project_description'     => 'required'
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


        return self::createJsonData(true,['id'=>$project->id]);
    }

    public function publishStepTwo(Request $request)
    {
        $validateRules = [
            'project_id'      => 'required|integer',
            'worker_num'      => 'required|in:1,2,3,4,5,6,7',
            'worker_level'     => 'required|in:1,2,3',
            'project_amount'     => 'required|numeric',
            'billing_mode'     => 'required|in:1,2',
            'begin_time'     => 'required',
            'project_cycle'     => 'required|in:1,2,3,4,5,6,7,8,9',
            'work_intensity'     => 'required|in:1,2,3,4,5,6,7,8,9',
            'remote_work'     => 'required|in:1,2',
            'travel_expense'     => 'required|in:1,2',
            'work_address'     => 'required',
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


        $newData = [
            'user_id' => $user_id,
            'project_id' => $data['project_name'],
            'worker_num' => $data['project_type'],
            'worker_level' => $data['project_stage'],
            'project_amount' => $data['project_description'],
            '' => $data[''],
        ];
        $project = Project::create($newData);
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


        return self::createJsonData(true,['id'=>$project->id]);
    }

}