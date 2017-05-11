<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\UserInfo\EduInfo;
use Illuminate\Http\Request;

/**
 * 教育经历
 * @author: wanghui
 * @date: 2017/4/21 下午6:17
 * @email: wanghui@yonglibao.com
 */


class EduController extends Controller {

    protected $validateRules = [
        'school' => 'required',
        'major'   => 'required',
        'degree'  => 'required|in:本科,硕士,大专,博士,其它',
        'begin_time'   => 'required|date_format:Y-m',
        'end_time'   => 'required|date_format:Y-m',
        'description'   => 'required',
    ];

    //新建
    public function store(Request $request){
        $this->validate($request,$this->validateRules);
        $user = $request->user();

        $data = $request->all();
        if($data['begin_time'] > $data['end_time']){
            throw new ApiException(ApiException::USER_DATE_RANGE_INVALID);
        }

        $data['user_id'] = $user->id;

        $edu = EduInfo::create($data);

        return self::createJsonData(true,['id'=>$edu->id,'type'=>'edu','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }

    //提交修改
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();
        if($data['begin_time'] > $data['end_time']){
            throw new ApiException(ApiException::USER_DATE_RANGE_INVALID);
        }

        $id = $data['id'];
        $edu = EduInfo::find($id);
        if($edu->user_id != $user->id){
            return self::createJsonData(false,['id'=>$id,'type'=>'project'],ApiException::BAD_REQUEST,'bad request');
        }
        EduInfo::where('id',$id)->update($data);

        return self::createJsonData(true,['id'=>$id,'type'=>'edu']);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        EduInfo::where('id',$id)->where('user_id',$user->id)->delete();

        return self::createJsonData(true,['id'=>$id,'type'=>'edu','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }


}