<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
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

        ProjectInfo::create($data);

        return self::createJsonData(true);
    }

    //提交修改
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();
        $id = $data['id'];

        ProjectInfo::where('id',$id)->where('user_id',$user->id)->update($data);

        return self::createJsonData(true);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        ProjectInfo::where('id',$id)->where('user_id',$user->id)->delete();

        return self::createJsonData(true);
    }


}