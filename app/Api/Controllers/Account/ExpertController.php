<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Authentication;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/21 下午1:54
 * @email: wanghui@yonglibao.com
 */

class ExpertController extends Controller {

    //申请专家认证
    public function apply(Request $request){
        //$data = $request->all();
        $data = [];
        $user = $request->user();
        $authentication = $user->authentication;
        if(!empty($authentication)){
            throw new ApiException(ApiException::EXPERT_NEED_CONFIRM);
        }

        $data['user_id'] = $user->id;
        $data['real_name'] = $user->name;
        $data['title'] = $user->title;
        $data['gender'] = $user->gender;
        $data['id_card'] = '';
        $data['id_card_image'] = '';
        $data['skill'] = '';
        $data['skill_image'] = '';
        $data['status'] = 0;

        logger('test1',$data);
        Authentication::create($data);

        return self::createJsonData(true,['tips'=>'稍安勿躁，我们正在审核中!']);

    }

}