<?php namespace App\Api\Controllers\Project;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Company\Project;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/6/7 上午11:28
 * @email: wanghui@yonglibao.com
 */

class ProjectController extends Controller {

    protected $validateRules = [
        'project_name' => 'required|max:255',
        'project_amount'   => 'required|numeric',
        'project_address'   => 'required|max:255',
        'company_name'   => 'required|max:255',
        'description' => 'nullable'
    ];

    public function submit(Request $request)
    {
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        if($user->userData->is_company != 1){
            throw new ApiException(ApiException::USER_SUBMIT_PROJECT_NEED_COMPANY);
        }

        $data = $request->all();

        $data['user_id'] = $user->id;

        $project = Project::create($data);

        return self::createJsonData(true,['id'=>$project->id]);
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
        $items = $query->orderBy('id','DESC')->paginate(10);
        $list = [];
        foreach($items as $item){
            $list[] = $item->toArray();
        }
        return self::createJsonData(true,$list);
    }

    public function update(Request $request)
    {
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();

        $id = $data['id'];

        $project = Project::find($id);
        if($project->user_id != $user->id){
            return self::createJsonData(false,['id'=>$id],ApiException::BAD_REQUEST,'bad request');
        }

        unset($this->validateRules['id']);
        $update = [];
        foreach($this->validateRules as $field=>$rule){
            if(isset($data[$field])){
                $update[$field] = $data[$field];
            }
        }

        $project->update($update);

        return self::createJsonData(true,['id'=>$id]);
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