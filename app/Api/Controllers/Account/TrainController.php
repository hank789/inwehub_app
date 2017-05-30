<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Cache\UserCache;
use App\Exceptions\ApiException;
use App\Models\UserInfo\TrainInfo;
use Illuminate\Http\Request;
use App\Models\User;

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
        UserCache::delUserInfoCache($user->id);
        $percent = $user->getInfoCompletePercent();
        $this->creditAccountInfoCompletePercent($user->id,$percent);
        return self::createJsonData(true,['id'=>$train->id,'type'=>'train','account_info_complete_percent'=>$percent]);
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
        $train = TrainInfo::findOrFail($id);
        if($train->user_id != $user->id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $train->update($update);

        UserCache::delUserInfoCache($user->id);

        return self::createJsonData(true,['id'=>$id,'type'=>'train']);
    }

    public function showList(Request $request){
        /**
         * @var User
         */
        $user = $request->user();
        $trains = $user->trains()->orderBy('get_time','desc')->get();
        return self::createJsonData(true,$trains->toArray());
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        $train = TrainInfo::findOrFail($id);
        if($train->user_id != $user->id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $train->delete();
        UserCache::delUserInfoCache($user->id);

        return self::createJsonData(true,['id'=>$id,'type'=>'train','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }


}