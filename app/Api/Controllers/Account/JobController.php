<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Models\UserInfo\JobInfo;
use Illuminate\Http\Request;

/**
 * 工作经历
 * @author: wanghui
 * @date: 2017/4/21 下午6:17
 * @email: wanghui@yonglibao.com
 */


class JobController extends Controller {

    protected $validateRules = [
        'company' => 'required',
        'title'   => 'required',
        'begin_time'   => 'required',
        'end_time'   => 'required',
        'description'   => 'required',

    ];

    //新建
    public function store(Request $request){
        $this->validate($request,$this->validateRules);
        $user = $request->user();

        $data = $request->all();
        $data['user_id'] = $user->id;

        $job = JobInfo::create($data);

        return self::createJsonData(true,['id'=>$job->id,'type'=>'job']);
    }

    //提交修改
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();
        $id = $data['id'];

        JobInfo::where('id',$id)->where('user_id',$user->id)->update($data);

        return self::createJsonData(true,['id'=>$id,'type'=>'job']);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        JobInfo::where('id',$id)->where('user_id',$user->id)->delete();

        return self::createJsonData(true,['id'=>$id,'type'=>'job']);
    }


}