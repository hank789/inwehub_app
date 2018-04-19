<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2018/3/12 上午11:55
 * @email: wanghui@yonglibao.com
 */
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\CloseDemand;
use App\Jobs\UploadFile;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Models\Weapp\DemandUserRel;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

class DemandController extends controller {


    public function showList(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'type'   => 'required|in:all,mine'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $type = $request->input('type');
        $data = [];
        $closedId = 0;
        switch ($type){
            case 'all':
                $list = DemandUserRel::where('demand_user_rel.user_oauth_id',$oauth->id)->leftJoin('demand','demand_user_rel.demand_id','=','demand.id')->select('demand_user_rel.*')->orderBy('status','ASC')->orderBy('demand.id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $item) {
                    $demand = Demand::find($item->demand_id);
                    $demand_user_oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
                    $total_unread = 0;
                    $total = 0;
                    foreach ($rooms as $im_room) {
                        if ($im_room->user_id == $user->id || $demand->user_id == $user->id) {
                            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                            $total_unread += $im_count;
                            $total += MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->count();
                        }
                    }
                    if ($closedId == 0 && $demand->status == Demand::STATUS_CLOSED) {
                        $closedId = $demand->id;
                    }
                    $data[] = [
                        'id'    => $demand->id,
                        'title' => $demand->title,
                        'publisher_name'=>$demand->user->name,
                        'publisher_avatar'=>$demand_user_oauth->avatar,
                        'publisher_title'=>$demand->user->title,
                        'publisher_company'=>$demand->user->company,
                        'address' => $demand->address,
                        'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                        'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                        'salary' => salaryFormat($demand->salary),
                        'salary_upper' => salaryFormat($demand->salary_upper?:$demand->salary),
                        'salary_type' => $demand->salary_type,
                        'status' => $demand->status,
                        'view_number'  => $demand->views,
                        'communicate_number' => $total,
                        'unread_number' => $total_unread,
                        'created_time'=>$demand->created_at->diffForHumans()
                    ];
                }
                break;
            case 'mine':
                $list = Demand::where('user_id',$user->id)->orderBy('status','asc')->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $demand) {
                    $demand_user_oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
                    $total_unread = 0;
                    foreach ($rooms as $im_room) {
                        if ($im_room->user_id == $user->id || $demand->user_id == $user->id) {
                            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                            $total_unread += $im_count;
                        }
                    }
                    if ($closedId == 0 && $demand->status == Demand::STATUS_CLOSED) {
                        $closedId = $demand->id;
                    }
                    $data[] = [
                        'id'    => $demand->id,
                        'title' => $demand->title,
                        'publisher_name'=>$demand->user->name,
                        'publisher_avatar'=>$demand_user_oauth->avatar,
                        'publisher_title'=>$demand->user->title,
                        'publisher_company'=>$demand->user->company,
                        'address' => $demand->address,
                        'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                        'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                        'salary' => salaryFormat($demand->salary),
                        'salary_upper' => salaryFormat($demand->salary_upper?:$demand->salary),
                        'salary_type' => $demand->salary_type,
                        'status' => $demand->status,
                        'view_number'  => $demand->views,
                        'communicate_number' => $rooms->count(),
                        'unread_number' => $total_unread,
                        'created_time'=>$demand->created_at->diffForHumans()
                    ];
                }
                break;
        }
        $return = $list->toArray();
        $return['data'] = $data;
        $return['closedDemandId'] = $closedId;
        return self::createJsonData(true,$return);
    }

    public function detail(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $demand = Demand::findOrFail($request->input('id'));
        $demand->increment('views');
        $is_author = ($demand->user_id == $user->id ? true:false);
        $demand_oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
        $im_count = 0;
        if ($is_author) {
            $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
            foreach ($rooms as $room) {
                $im_count += MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
            }
        }
        $data = [
            'publisher_user_id'=>$demand_oauth->user_id,
            'publisher_name'=>$demand->user->name,
            'publisher_avatar'=>$demand_oauth->avatar,
            'publisher_title'=>$demand->user->title,
            'publisher_company'=>$demand->user->company,
            'publisher_email'=>$demand->user->email,
            'publisher_phone' => $demand->user->mobile,
            'is_author' => $is_author,
            'title' => $demand->title,
            'address' => $demand->address,
            'salary' => $demand->salary,
            'salary_upper' => $demand->salary_upper?:$demand->salary,
            'salary_type' => $demand->salary_type,
            'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
            'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
            'project_begin_time' => $demand->project_begin_time,
            'description' => $demand->description,
            'expired_at'  => $demand->expired_at,
            'views' => $demand->views,
            'status' => $demand->status
        ];
        $rel = DemandUserRel::where('user_oauth_id',$oauth->id)->where('demand_id',$demand->id)->first();
        if (!$rel) {
            DemandUserRel::create([
                'user_oauth_id'=>$oauth->id,
                'demand_id'=>$demand->id
            ]);
        }
        $data['im_count'] = $im_count;
        return self::createJsonData(true,$data);
    }

    public function store(Request $request,JWTAuth $JWTAuth,WeApp $wxxcx) {
        $validateRules = [
            'title'=> 'required|max:255',
            'address'=> 'required|max:255',
            'salary' => 'required|numeric',
            'salary_type' => 'required|numeric',
            'industry' => 'required',
            'project_cycle' => 'required|integer',
            'project_begin_time' => 'required|date',
            'description' => 'required|max:2000',
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        if(RateLimiter::instance()->increase('weapp_create_demand',$oauth->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $salary_upper = $request->input('salary_upper',0);
        if ($salary_upper > 0 && $salary_upper < $request->input('salary')) {
            throw new ApiException(ApiException::USER_WEAPP_SALARY_INVALID);
        }
        $address = $request->input('address');
        $formId = $request->input('formId');

        $demand = Demand::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'address' => $address,
            'salary' => $request->input('salary'),
            'salary_upper' => $salary_upper,
            'salary_type' => $request->input('salary_type'),
            'industry' => $request->input('industry'),
            'project_cycle' => $request->input('project_cycle'),
            'project_begin_time' => $request->input('project_begin_time'),
            'description' => $request->input('description'),
            'status' => Demand::STATUS_PUBLISH,
            'expired_at' => date('Y-m-d',strtotime('+7 days')),
        ]);
        DemandUserRel::create([
            'user_oauth_id'=>$oauth->id,
            'demand_id'=>$demand->id
        ]);
        if ($formId) {
            RateLimiter::instance()->sAdd('user_formId_'.$user->id,$formId,60*60*24*6);
        }

        $file_name = 'demand/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
        $page = 'pages/detail/detail';
        $scene = 'demand_id='.$demand->id;
        try {
            $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
            $this->dispatch(new UploadFile($file_name,base64_encode($qrcode)));
            $url = Storage::disk('oss')->url($file_name);
            RateLimiter::instance()->hSet('demand-qrcode',$demand->id,$url);
        } Catch (\Exception $e) {

        }

        $this->dispatch((new CloseDemand($demand->id))->delay(Carbon::createFromTimestamp(strtotime(date('Y-m-d',strtotime('+7 days'))))));
        event(new SystemNotify('小程序用户发布了新的需求',$demand->toArray()));
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function update(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer',
            'title'=> 'required|max:255',
            'address'=> 'required|max:255',
            'salary' => 'required|numeric',
            'salary_type' => 'required|numeric',
            'industry' => 'required',
            'project_cycle' => 'required|integer',
            'project_begin_time' => 'required|date',
            'description' => 'required|max:2000',
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        if(RateLimiter::instance()->increase('weapp_update_demand',$oauth->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $demand = Demand::findOrFail($request->input('id'));
        if ($demand->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $salary_upper = $request->input('salary_upper',0);
        if ($salary_upper > 0 && $salary_upper < $request->input('salary')) {
            throw new ApiException(ApiException::USER_WEAPP_SALARY_INVALID);
        }
        $address = $request->input('address');
        $formId = $request->input('formId');

        $demand->update([
            'title' => $request->input('title'),
            'address' => $address,
            'salary' => $request->input('salary'),
            'salary_upper' => $salary_upper,
            'salary_type' => $request->input('salary_type'),
            'industry' => $request->input('industry'),
            'project_cycle' => $request->input('project_cycle'),
            'project_begin_time' => $request->input('project_begin_time'),
            'description' => $request->input('description'),
        ]);
        if ($formId) {
            RateLimiter::instance()->sAdd('user_formId_'.$user->id,$formId,60*60*24*6);
        }
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function close(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        if(RateLimiter::instance()->increase('weapp_close_demand',$oauth->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $demand = Demand::findOrFail($request->input('id'));
        if ($demand->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $demand->status = Demand::STATUS_CLOSED;
        $demand->expired_at = date('Y-m-d');
        $demand->save();
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function getRooms(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $demand = Demand::findOrFail($request->input('id'));
        $im_rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->paginate(Config::get('inwehub.api_data_page_size'));
        $im_list = [];
        foreach ($im_rooms as $im_room) {
            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
            $last_message = MessageRoom::where('room_id',$im_room->id)->orderBy('id','desc')->first();
            $demand = Demand::find($im_room->source_id);
            $contact = User::find($im_room->user_id==$user->id?$demand->user_id:$im_room->user_id);
            $item = [
                'unread_count' => $im_count,
                'avatar'       => $contact->avatar,
                'name'         => $contact->name,
                'room_id'      => $im_room->id,
                'contact_id'   => $contact->id,
                'contact_uuid' => $contact->uuid,
                'last_message' => [
                    'id' => $last_message?$last_message->message_id:0,
                    'text' => '',
                    'data'  => $last_message?$last_message->message->data:['text'=>'','img'=>''],
                    'read_at' => $last_message?$last_message->message->read_at:'',
                    'created_at' => $last_message?(string)$last_message->created_at:''
                ]
            ];
            $im_list[] = $item;
        }
        $return = $im_rooms->toArray();
        $return['data'] = $im_list;
        $return['demand'] = $demand->toArray();

        return self::createJsonData(true,$return);
    }

    public function getShareImage(Request $request){
        $validateRules = [
            'id'   => 'required|integer',
            'type' => 'required|in:1,2'
        ];
        $this->validate($request,$validateRules);
        $type = $request->input('type',1);
        if ($type == 1) {
            //分享到朋友圈的长图
            $collection = 'images_big';
            $showUrl = 'getDemandShareLongInfo';
        } else {
            //分享到公众号的短图
            $collection = 'images_small';
            $showUrl = 'getDemandShareShortInfo';
        }
        $demand = Demand::findOrFail($request->input('id'));
        if($demand->getMedia($collection)->isEmpty()){
            $snappy = App::make('snappy.image');
            $image = $snappy->getOutput(config('app.url').'/weapp/'.$showUrl.'/'.$demand->id);
            $demand->addMediaFromBase64(base64_encode($image))->toMediaCollection($collection);
        }
        $demand = Demand::find($request->input('id'));
        $url = $demand->getMedia($collection)->last()->getUrl();
        return self::createJsonData(true,['url'=>$url]);
    }

}