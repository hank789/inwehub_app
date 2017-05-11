<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Events\Frontend\Expert\Recommend;
use App\Exceptions\ApiException;
use App\Models\Authentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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

        Authentication::create($data);

        return self::createJsonData(true,['status'=>1,'tips'=>'稍安勿躁，我们正在审核中!']);
    }

    public function info(Request $request){
        $user = $request->user();
        $authentication = $user->authentication;
        $res['status'] = 0;
        $res['tips'] = '速速申请成为专家,参与延伸服务!';
        if(!empty($authentication)){
            if($authentication->status == 0){
                $res['status'] = 1;
                $res['tips'] = '稍安勿躁,我们正在审核中!';
            }elseif($authentication->status == 1){
                $res['status'] = 2;
                $res['tips'] = '恭喜您,您已经是专家啦!';
            }else{
                $res['status'] = 3;
                $res['tips'] = '很抱歉,认证失败!';
            }
        }

        return self::createJsonData(true,$res);
    }

    public function recommend(Request $request){
        $validateRules = [
            'name'      => 'required',
            'gender'      => 'required|in:0,1,2',
            'work_years'      => 'required|between:1,70',
            'mobile'      => 'required|cn_phone',
            'industry_tags'      => 'required',
            'description'      => 'required',
            'images'  => 'required|image',
        ];
        $this->validate($request,$validateRules);
        if($request->hasFile('head_img')){
            $user_id = $request->user()->id;
            $file = $request->file('head_img');
            $extension = strtolower($file->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');

            if(in_array($extension, $extArray)){
                $file_name = 'expert/recommend/'.$user_id.'/'.md5($file->getFilename()).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file));
                $head_img_url = Storage::disk('oss')->url($file_name);
                $data = $request->all();
                event(new Recommend($user_id,$data['name'],$data['gender'],$data['industry_tags'],$data['work_years'],$data['mobile'],$data['description'],$head_img_url));
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'名片格式错误');
            }
            return self::createJsonData(true);
        }
        return self::createJsonData(false,[],ApiException::BAD_REQUEST,'推荐失败');

    }

}