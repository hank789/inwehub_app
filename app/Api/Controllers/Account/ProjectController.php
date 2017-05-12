<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Tag;
use App\Models\UserInfo\ProjectInfo;
use Illuminate\Http\Request;

/**
 * 项目经历
 * @author: wanghui
 * @date: 2017/4/21 下午6:17
 * @email: wanghui@yonglibao.com
 */


class ProjectController extends Controller {

    protected $validateRules = [
        'project_name' => 'required',
        'customer_name' => 'required',
        'title'   => 'required',
        'begin_time'   => 'required|date_format:Y-m',
        'end_time'   => 'required',
        'description'   => 'required',
        'industry_tags'  => 'max:128',
        'product_tags'   => 'max:128',
    ];

    //新建
    public function store(Request $request){
        $this->validate($request,$this->validateRules);
        $user = $request->user();

        $data = $request->all();

        if($data['begin_time'] > $data['end_time'] && $data['end_time'] != '至今'){
            throw new ApiException(ApiException::USER_DATE_RANGE_INVALID);
        }

        $data['user_id'] = $user->id;

        $industry_tags = $data['industry_tags']?implode(',',$data['industry_tags']):'';
        $product_tags = $data['product_tags']?implode(',',$data['product_tags']):'';

        unset($data['industry_tags']);
        unset($data['product_tags']);

        $project = ProjectInfo::create($data);

        $tags = trim($industry_tags.','.$product_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSave($industry_tags,$project);
        }

        return self::createJsonData(true,['id'=>$project->id,'type'=>'project','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }

    //提交修改
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();

        if($data['begin_time'] > $data['end_time'] && $data['end_time'] != '至今'){
            throw new ApiException(ApiException::USER_DATE_RANGE_INVALID);
        }

        $id = $data['id'];

        $project = ProjectInfo::find($id);
        if($project->user_id != $user->id){
            return self::createJsonData(false,['id'=>$id,'type'=>'project'],ApiException::BAD_REQUEST,'bad request');
        }

        $industry_tags = $data['industry_tags']?implode(',',$data['industry_tags']):'';
        $product_tags = $data['product_tags']?implode(',',$data['product_tags']):'';

        unset($data['industry_tags']);
        unset($data['product_tags']);

        ProjectInfo::where('id',$id)->update($data);

        $tags = trim($industry_tags.','.$product_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSave($industry_tags,$project);
        }

        return self::createJsonData(true,['id'=>$id,'type'=>'project']);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        ProjectInfo::where('id',$id)->where('user_id',$user->id)->delete();

        return self::createJsonData(true,['id'=>$id,'type'=>'project','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }


}