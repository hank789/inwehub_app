<?php

namespace App\Http\Controllers\Admin;

use App\Cache\UserCache;
use App\Models\AddressBook;
use App\Models\Credit;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserInfo\EduInfo;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\ProjectInfo;
use App\Models\UserInfo\TrainInfo;
use App\Models\UserOauth;
use App\Models\UserTag;
use App\Services\City\CityData;
use App\Services\Registrar;
use Bican\Roles\Models\Role;
use Illuminate\Http\Request;
use App\Events\Frontend\System\Credit as CreditEvent;
use Illuminate\Support\Facades\Config;
use Excel;

class UserController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:100',
        'mobile' => 'required|max:255|unique:users',
        'password' => 'required|min:6|max:20',
    ];
    /**
     * 用户管理首页
     */
    public function index(Request $request)
    {
        $filter = $request->all();
        if (isset($filter['wechat_nickname']) && $filter['wechat_nickname']) {
            $oauths = UserOauth::where('nickname','like','%'.$filter['wechat_nickname'].'%')->get()->pluck('user_id')->toArray();
            $users = User::whereIn('id',$oauths)->paginate(Config::get('inwehub.admin.page_size'));
        } else {
            $query = $this->getUserQuery($request);
            if (isset($filter['order_by']) && $filter['order_by']){
                $orderBy = explode('|',$filter['order_by']);
                $users = $query->orderBy($orderBy[0],$orderBy[1])->paginate(Config::get('inwehub.admin.page_size'));
            } else {
                $users = $query->orderBy('id','desc')->paginate(Config::get('inwehub.admin.page_size'));
            }
        }
        return view('admin.user.index')->with('users',$users)->with('filter',$filter);
    }

    public function oauthUser(Request $request) {
        $filter =  $request->all();

        $query = UserOauth::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("id","=",$filter['user_id']);
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }

        if (isset($filter['wechat_nickname']) && $filter['wechat_nickname']) {
            $query->where('nickname','like','%'.$filter['wechat_nickname'].'%');
        }

        $users = $query->orderBy('created_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.user.oauth')->with('users',$users)->with('filter',$filter);
    }

    public function addressBook(Request $request) {
        $filter =  $request->all();

        $query = AddressBook::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("user_id","=",$filter['user_id']);
        }

        if( isset($filter['phone']) && $filter['phone']){
            $query->where('phone','=',$filter['phone']);
        }

        if( isset($filter['name']) && $filter['name']){
            $query->where('display_name','=',$filter['name']);
        }

        $addressbooks = $query->orderBy('created_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.user.addressBook')->with('addressBooks',$addressbooks)->with('filter',$filter);
    }

    public function exportAddressBook(Request $request){
        $filter = $request->all();
        $query = AddressBook::query();
        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("user_id","=",$filter['user_id']);
        }

        if( isset($filter['phone']) && $filter['phone']){
            $query->where('phone','=',$filter['phone']);
        }

        if( isset($filter['name']) && $filter['name']){
            $query->where('display_name','=',$filter['name']);
        }
        $cellData = [];
        $cellData[] = ['系统ID','通讯录ID','姓名','手机','通讯录所有者ID','通讯录所有者姓名'];
        $page = 1;
        $addressbooks = $query->orderBy('created_at','desc')->simplePaginate(100,['*'],'page',$page);
        while ($addressbooks->count() > 0) {
            foreach ($addressbooks as $user) {
                $cellData[] = [
                    $user->id,
                    $user->address_book_id,
                    $user->display_name,
                    $user->phone,
                    $user->user_id,
                    $user->user->name
                ];
            }
            $page ++;
            $addressbooks = $query->orderBy('created_at','desc')->simplePaginate(100,['*'],'page',$page);
        }
        Excel::create('users',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xlsx');
    }

    public function exportUsers(Request $request){
        $filter = $request->all();
        $query = $this->getUserQuery($request);
        if (isset($filter['order_by']) && $filter['order_by']){
            $orderBy = explode('|',$filter['order_by']);
            $users = $query->orderBy($orderBy[0],$orderBy[1])->get();
        } else {
            $users = $query->orderBy('id','desc')->get();
        }
        $cellData = [];
        $cellData[] = ['ID','姓名','微信昵称','手机','身份职业','专家认证','企业用户','问题数','回答数','档案完整度','账户余额','成长值','贡献值','注册时间','邀请者','邀请人数'];
        foreach ($users as $user) {
            $inviter=$user->getInviter();
            $cellData[] = [
                $user->id,
                $user->name,
                implode(',',$user->userOauth()->where('status',1)->take(1)->pluck('nickname')->toArray()),
                $user->mobile,
                $user->title,
                ($user->userData->authentication_status ? '是':'否'),
                ($user->userData->is_company ? '是':'否'),
                $user->userData->questions,
                $user->userData->answers,
                $user->info_complete_percent,
                $user->userMoney->total_money,
                $user->userData->credits,
                $user->userData->coins,
                $user->created_at,
                $inviter?$inviter->name:'',
                $user->getInvitedUserCount()
            ];
        }
        Excel::create('users',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xlsx');
    }

    protected function getUserQuery(Request $request){
        $filter =  $request->all();

        $query = User::query();

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("id","=",$filter['user_id']);
        }

        /*关键词过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where(function($subQuery) use ($filter) {
                return $subQuery->where('name','like',$filter['word'].'%')
                    ->orWhere('mobile','like',$filter['word'].'%');
            });
        }

        /*注册时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('status','=',$filter['status']);
        }
        //邀请码
        if( isset($filter['rc_code']) && $filter['rc_code'] ){
            $query->where('rc_code','=',$filter['rc_code']);
        }

        return $query;
    }

    /**
     * 显示用户添加页面
     */
    public function create()
    {
        $roles = Role::orderby('name','asc')->get();

        return view('admin.user.create')->with(compact('roles'));
    }

    /**
     * 保存创建用户信息
     */
    public function store(Request $request,Registrar $registrar)
    {

        $request->flash();
        $this->validate($request,$this->validateRules);

        $formData = $request->all();
        $formData['status'] = 1;
        $formData['visit_ip'] = $request->getClientIp();

        $user = $registrar->create($formData);
        $user->attachRole($request->input('role_id'));
        return $this->success(route('admin.user.index'),'用户添加成功！');

    }


    /**
     * 显示用户编辑页面
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::orderby('name','asc')->get();
        $cities = CityData::getCityByProvince($user->province);
        $hometown_cities = CityData::getCityByProvince($user->hometown_province);
        $data = [
            'provinces' => CityData::getAllProvince(),
            'cities' => $cities,
            'hometown_cities' => $hometown_cities
        ];
        return view('admin.user.edit')->with(compact('user','roles','data'));
    }

    /**
     * 保存用户修改
     */
    public function update(Request $request, $id)
    {
        $request->flash();
        $user = User::find($id);
        if(!$user){
            abort(404);
        }
        if($request->input('email')){
            $this->validateRules['email'] = 'required|email|max:255|unique:users,email,'.$user->id;
        }
        $this->validateRules['mobile'] = 'required|unique:users,mobile,'.$user->id;
        unset($this->validateRules['password']);
        $this->validate($request,$this->validateRules);
        $this->validateRules['password'] = 'sometimes|min:6';
        $password = $request->input('password');
        if($password)
        {
            $user->password = bcrypt($password);
        }
        $user->name = $request->input('name');
        $user->email = strtolower($request->input('email'));
        $user->mobile = $request->input('mobile');
        $user->company = $request->input('company');
        $user->title = $request->input('title','');
        $user->gender = $request->input('gender',0);
        $user->province = $request->input('province',0);
        $user->city = $request->input('city',0);
        $user->description = $request->input('description');
        $user->status = $request->input('status',0);
        $user->birthday = $request->input('birthday',null);
        $user->hometown_province = $request->input('hometown_province');
        $user->hometown_city = $request->input('hometown_city');
        $user->address_detail = $request->input('address_detail');
        $rcUid = $request->input('rc_uid',0);

        if($request->hasFile('avatar')){
            $user_id = $id;
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');

            if(in_array($extension, $extArray)){
                $user->addMediaFromRequest('avatar')->setFileName(User::getAvatarFileName($user_id,'origin').'.'.$extension)->toMediaCollection('avatar');
                $user->avatar = $user->getAvatarUrl();
            }
        }

        if ($rcUid && $rcUid != $user->rc_uid) {
            //邀请者增加积分
            $rc_user = User::find($rcUid);
            $action = Credit::KEY_INVITE_USER;
            event(new CreditEvent($rc_user->id,$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$user->id,'邀请好友注册成功'));
            
            $user->rc_uid = $rcUid;
        }
        $user->save();
        $user->detachAllRoles();
        $user->attachRole($request->input('role_id'));

        if($request->input('industry_tags') !== null){
            $industry_tags = $request->input('industry_tags');
            $tags = Tag::whereIn('id',explode(',',$industry_tags))->get();
            UserTag::detachByField($user->id,'industries');
            UserTag::multiIncrement($user->id,$tags,'industries');
        }
        if($request->input('skill_tags') !== null){
            $skill_tags = $request->input('skill_tags');
            $tags = Tag::whereIn('id',explode(',',$skill_tags))->get();
            UserTag::detachByField($user->id,'skills');
            UserTag::multiIncrement($user->id,$tags,'skills');
        }

        return $this->success(route('admin.user.index'),'用户修改成功');
    }

    /*用户审核*/
    public function verify(Request $request)
    {
        $userIds = $request->input('id');
        User::whereIn('id',$userIds)->update(['status'=>1]);
        return $this->success(route('admin.user.index').'?status=0','用户审核成功');

    }

    /**
     * 将用户设置为不可用
     */
    public function destroy(Request $request)
    {
        $userIds = $request->input('id');
        //User::destroy($userIds);
        User::whereIn('id',$userIds)->update(['status'=>-1]);

        return $this->success(url()->previous(),'用户禁用成功');
    }

    //解绑微信
    public function unbindWechat(Request $request) {
        $userIds = $request->input('id');
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user->mobile) {
                UserOauth::where('user_id',$userId)->whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])->delete();
            }
        }
        return $this->success(url()->previous(),'用户微信解绑成功');
    }

    public function itemInfo(Request $request){
        $type = $request->input('type');
        $user_id = $request->input('user_id');
        $item_id = $request->input('item_id');
        $object_item = '';
        $user = User::find($user_id);
        $title = '';
        $view = '';
        $product_tags = Tag::where('category_id',10)->get();
        $industry_tags = Tag::where('category_id',9)->get();
        switch($type){
            case 'jobs':
                $items = $user->jobs()->orderBy('begin_time','desc')->get();
                $object_item = JobInfo::findOrNew($item_id);
                $title = '工作经历';
                $view = 'admin.user.items_job';
                break;
            case 'projects':
                $items = $user->projects()->orderBy('begin_time','desc')->get();
                $object_item = ProjectInfo::findOrNew($item_id);
                $title = '项目经历';
                $view = 'admin.user.items_project';
                break;
            case 'edus':
                $items = $user->edus()->orderBy('begin_time','desc')->get();
                $object_item = EduInfo::findOrNew($item_id);
                $title = '教育经历';
                $view = 'admin.user.items_edu';
                break;
            case 'trains':
                $items = $user->trains()->orderBy('get_time','desc')->get();
                $object_item = TrainInfo::findOrNew($item_id);
                $title = '培训经历';
                $view = 'admin.user.items_train';
                break;
        }

        return view($view)->with('items',$items)->with('type',$type)->with('user_id',$user_id)
            ->with('title',$title)->with('object_item',$object_item)->with('product_tags',$product_tags)->with('industry_tags',$industry_tags);
    }

    public function storeItemInfo(Request $request)
    {
        $type = $request->input('type');
        $user_id = $request->input('user_id');
        $user = User::find($user_id);
        $data = $request->all();
        $id = $data['id'];

        if ($type == 'trains') {
            $validateRules = [
                'get_time'   => 'required|date_format:Y-m',
            ];
        }else{
            $validateRules = [
                'begin_time'   => 'required|date_format:Y-m',
                'end_time'   => 'required'
            ];
            if($data['begin_time'] > $data['end_time'] && $data['end_time'] != '至今'){
                return $this->error(route('admin.user.item.info',['item_id'=>$id,'user_id'=>$user->id,'type'=>$type]),'开始日期不能大于结束日期');
            }
        }

        $this->validate($request,$validateRules);

        $data['user_id'] = $user->id;

        $industry_tags = $data['industry_tags'];
        $product_tags = $data['product_tags'];

        unset($data['industry_tags']);
        unset($data['product_tags']);
        unset($data['id']);

        switch($type){
            case 'jobs':
                $item = JobInfo::updateOrCreate(['id'=>$id],$data);
                $title = '工作经历';
                break;
            case 'projects':
                $item = ProjectInfo::updateOrCreate(['id'=>$id],$data);
                $title = '项目经历';
                break;
            case 'edus':
                $item = EduInfo::updateOrCreate(['id'=>$id],$data);
                $title = '教育经历';
                break;
            case 'trains':
                $item = TrainInfo::updateOrCreate(['id'=>$id],$data);
                $title = '培训经历';
                break;
        }
        $tags = trim($industry_tags.','.$product_tags,',');
        /*添加标签*/
        if($tags){
            Tag::multiSaveByIds($tags,$item);
        }
        UserCache::delUserInfoCache($user->id);

        return $this->success(route('admin.user.item.info',['item_id'=>$id,'user_id'=>$user->id,'type'=>$type]),'操作成功');

    }

    public function destroyItemInfo(Request $request){
        $type = $request->input('type');
        $data = $request->all();
        $ids = $data['ids'];

        switch($type){
            case 'jobs':
                JobInfo::whereIn('id',$ids)->delete();
                $title = '工作经历';
                break;
            case 'projects':
                ProjectInfo::whereIn('id',$ids)->delete();
                $title = '项目经历';
                break;
            case 'edus':
                EduInfo::whereIn('id',$ids)->delete();
                $title = '教育经历';
                break;
            case 'trains':
                TrainInfo::whereIn('id',$ids)->delete();
                $title = '培训经历';
                break;
        }
        UserCache::delUserInfoCache($data['user_id']);

        return $this->success(route('admin.user.item.info',['item_id'=>$data['id'],'user_id'=>$data['user_id'],'type'=>$type]),'删除成功');
    }

    public function resumeInfo(Request $request){
        $user_id = $request->input('id');
        $user = User::find($user_id);
        $resumes = $user->getResumeMedias();
        return view('admin.user.resume_info')->with('resumes',$resumes);
    }
}
