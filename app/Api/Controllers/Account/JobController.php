<?php namespace App\Api\Controllers\Account;
use App\Api\Controllers\Controller;
use App\Cache\UserCache;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Tag;
use App\Models\UserInfo\JobInfo;
use Illuminate\Http\Request;
use App\Models\User;


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
        'begin_time'   => 'required|date_format:Y-m',
        'end_time'   => 'required',
        'industry_tags'  => 'required',
        'product_tags'   => 'required',
        'description' => 'nullable'
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

        $job = JobInfo::create($data);

        $tags = trim($industry_tags.','.$product_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSaveByIds($tags,$job);
        }
        UserCache::delUserInfoCache($user->id);

        $percent = $user->getInfoCompletePercent();
        $this->creditAccountInfoCompletePercent($user->id,$percent);

        return self::createJsonData(true,['id'=>$job->id,'type'=>'job','account_info_complete_percent'=>$percent]);
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

        $job = JobInfo::find($id);
        if($job->user_id != $user->id){
            return self::createJsonData(false,['id'=>$id,'type'=>'job'],ApiException::BAD_REQUEST,'bad request');
        }
        $industry_tags = $data['industry_tags']?implode(',',$data['industry_tags']):'';
        $product_tags = $data['product_tags']?implode(',',$data['product_tags']):'';

        unset($data['industry_tags']);
        unset($data['product_tags']);

        unset($this->validateRules['id']);
        $update = [];
        foreach($this->validateRules as $field=>$rule){
            if(isset($data[$field])){
                $update[$field] = $data[$field];
            }
        }

        $job->update($update);


        $tags = trim($industry_tags.','.$product_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSaveByIds($tags,$job);
        }

        UserCache::delUserInfoCache($user->id);

        return self::createJsonData(true,['id'=>$id,'type'=>'job']);
    }

    public function showList(Request $request){
        /**
         * @var User
         */
        $user = $request->user();
        $jobs = $user->jobs()->orderBy('begin_time','desc')->get();
        foreach($jobs as &$job){
            $job->industry_tags = '';
            $job->product_tags = '';

            $job->industry_tags = TagsLogic::formatTags($job->tags()->where('category_id',9)->get());
            $job->product_tags = TagsLogic::formatTags($job->tags()->where('category_id',10)->get());
        }
        return self::createJsonData(true,$jobs->toArray());
    }

    //删除
    public function destroy(Request $request){
        $id = $request->input('id');
        $user = $request->user();
        $job = JobInfo::findOrFail($id);
        if($job->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $job->delete();
        UserCache::delUserInfoCache($user->id);

        return self::createJsonData(true,['id'=>$id,'type'=>'job','account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }


}