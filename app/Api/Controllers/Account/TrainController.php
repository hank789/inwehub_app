<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Models\UserInfo\TrainInfo;
use Illuminate\Http\Request;

/**
 * 培训经历
 * @author: wanghui
 * @date: 2017/4/21 下午6:17
 * @email: wanghui@yonglibao.com
 */


class TrainController extends Controller {

    protected $validateRules = [
        'certificate' => 'required',
        'agency'   => 'required',
        'get_time'  => 'required|date_format:Y-m',
        'description' => 'nullable'
    ];

    //新建
    public function store(Request $request){
        $this->validate($request,$this->validateRules);
        $user = $request->user();

        $data = $request->all();
        $data['user_id'] = $user->id;

        $train = TrainInfo::create($data);

        return self::createJsonData(true,['id'=>$train->id,'type'=>'train','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }

    //提交修改
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $user = $request->user();
        $data = $request->all();
        $id = $data['id'];
        unset($this->validateRules['id']);
        $update = [];
        foreach($this->validateRules as $field=>$rule){
            if(isset($data[$field])){
                $update[$field] = $data[$field];
            }
        }

        TrainInfo::where('id',$id)->where('user_id',$user->id)->update($update);

        return self::createJsonData(true,['id'=>$id,'type'=>'train']);
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        TrainInfo::where('id',$id)->where('user_id',$user->id)->delete();

        return self::createJsonData(true,['id'=>$id,'type'=>'train','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }


}