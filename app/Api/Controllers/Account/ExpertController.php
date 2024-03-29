<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Events\Frontend\Expert\Recommend;
use App\Exceptions\ApiException;
use App\Models\Authentication;
use App\Models\Tag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

/**
 * @author: wanghui
 * @date: 2017/4/21 下午1:54
 * @email: hank.huiwang@gmail.com
 */

class ExpertController extends Controller {

    //申请专家认证
    public function apply(Request $request){
        //$data = $request->all();
        $data = [];
        $user = $request->user();
        $authentication = $user->authentication;
        $update = false;
        if($authentication){
            if ($authentication->status == 0){
                throw new ApiException(ApiException::EXPERT_NEED_CONFIRM);
            } else if ($authentication->status == 1) {
                throw new ApiException(ApiException::BAD_REQUEST);
            } else {
                $update = true;
            }
        }

        $validateRules = [
            'name' => 'required|max:128',
            'gender'    => 'required|in:1,2',
            'company'   => 'required|max:128',
            'address_detail'  => 'required|max:255',
            'email'            => 'required|email',
            'description'         => 'required|max:1000',
            'title' => 'required|max:255'
        ];
        $user = $request->user();
        $validateRules['email'] = 'required|email|max:255|unique:users,email,'.$user->id;
        $this->validate($request,$validateRules);

        $data['user_id'] = $user->id;
        $data['status'] = 0;

        if ($update){
            $authentication->update($data);
        } else {
            Authentication::create($data);
        }


        $user->name = $request->input('name');
        $user->email = strtolower($request->input('email'));
        $user->gender = $request->input('gender');
        $user->title = $request->input('title');
        $user->company = $request->input('company');
        $user->description = $request->input('description');
        $user->address_detail = $request->input('address_detail');
        $user->save();
        self::$needRefresh = true;
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
            'work_years'      => 'required',
            'mobile'      => 'required|cn_phone',
            'industry_tags'      => 'required',
            'description'      => 'required'
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if(RateLimiter::instance()->increase('expert:recommend',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $head_img_url_0 = '';
        $head_img_url_1 = '';

        if($request->hasFile('images_0')){
            $file_0 = $request->file('images_0');
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'expert/recommend/'.$user_id.'/'.md5($file_0->getFilename()).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $head_img_url_0 = Storage::disk('oss')->url($file_name);
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'名片格式错误');
            }
        }

        if($request->hasFile('images_1')){
            $file_1 = $request->file('images_1');
            $extension = strtolower($file_1->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'expert/recommend/'.$user_id.'/'.md5($file_1->getFilename()).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_1));
                $head_img_url_1 = Storage::disk('oss')->url($file_name);
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'名片格式错误');
            }
        }

        $data = $request->all();
        $tagNames = Tag::whereIn('id',explode(',',$data['industry_tags']))->pluck('name')->toArray();
        event(new Recommend($user_id,$data['name'],trans_gender_name($data['gender']),implode(',',$tagNames),$data['work_years'],$data['mobile'],$data['description'],[$head_img_url_0,$head_img_url_1]));

        return self::createJsonData(true);

    }

}