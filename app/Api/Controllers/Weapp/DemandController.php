<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2018/3/12 上午11:55
 * @email: wanghui@yonglibao.com
 */
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\CloseDemand;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Models\Weapp\DemandUserRel;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

class DemandController extends controller {


    public function showList(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'type'   => 'required|in:all,mine'
        ];
        $this->validate($request,$validateRules);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
        }
        $type = $request->input('type');
        $data = [];
        switch ($type){
            case 'all':
                $list = DemandUserRel::where('demand_user_rel.user_id',$user->id)->leftJoin('demand','demand_user_rel.demand_id','=','demand.id')->where('status',Demand::STATUS_PUBLISH)->select('demand_user_rel.*')->orderBy('demand_user_rel.id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $item) {
                    $demand = Demand::find($item->demand_id);
                    $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
                    $total_unread = 0;
                    foreach ($rooms as $im_room) {
                        $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                        $total_unread += $im_count;
                    }
                    $data[] = [
                        'id'    => $demand->id,
                        'title' => $demand->title,
                        'publisher_name'=>$oauth->nickname,
                        'publisher_avatar'=>$oauth->avatar,
                        'publisher_title'=>$demand->user->title,
                        'publisher_company'=>$demand->user->company,
                        'address' => $demand->address,
                        'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                        'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                        'salary' => $demand->salary,
                        'status' => $demand->status,
                        'view_number'  => $demand->views,
                        'communicate_number' => $rooms->count(),
                        'unread_number' => $total_unread,
                        'created_time'=>$demand->created_at->diffForHumans()
                    ];
                }
                break;
            case 'mine':
                $list = Demand::where('user_id',$user->id)->orderBy('status','asc')->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $demand) {
                    $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
                    $total_unread = 0;
                    foreach ($rooms as $im_room) {
                        $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                        $total_unread += $im_count;
                    }
                    $data[] = [
                        'id'    => $demand->id,
                        'title' => $demand->title,
                        'publisher_name'=>$oauth->nickname,
                        'publisher_avatar'=>$oauth->avatar,
                        'publisher_title'=>$demand->user->title,
                        'publisher_company'=>$demand->user->company,
                        'address' => $demand->address,
                        'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                        'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                        'salary' => $demand->salary,
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
        return self::createJsonData(true,$return);
    }

    public function detail(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
        }
        $demand = Demand::findOrFail($request->input('id'));
        $demand->increment('views');
        $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
        $rooms = Room::where('source_id',$demand->id)->where('source_type',get_class($demand))->get();
        $candidates = [];
        foreach ($rooms as $room) {
            $candidate = $room->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
            $candidates[] = [
                'room_id' => $room->id,
                'user_avatar' => $candidate->avatar
            ];
        }
        $data = [
            'publisher_user_id'=>$oauth->user_id,
            'publisher_name'=>$oauth->nickname,
            'publisher_avatar'=>$oauth->avatar,
            'publisher_title'=>$demand->user->title,
            'publisher_company'=>$demand->user->company,
            'publisher_email'=>$demand->user->email,
            'publisher_phone' => $demand->user->mobile,
            'is_author' => $demand->user_id == $user->id ? true:false,
            'title' => $demand->title,
            'address' => $demand->address,
            'salary' => $demand->salary,
            'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
            'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
            'project_begin_time' => $demand->project_begin_time,
            'description' => $demand->description,
            'views' => $demand->views,
            'status' => $demand->status
        ];
        $rel = DemandUserRel::where('user_id',$user->id)->where('demand_id',$demand->id)->first();
        if (!$rel) {
            DemandUserRel::create([
                'user_id'=>$user->id,
                'demand_id'=>$demand->id
            ]);
        }
        $data['candidates'] = $candidates;
        return self::createJsonData(true,$data);
    }

    public function store(Request $request) {
        $validateRules = [
            'title'=> 'required|max:255',
            'address'=> 'required|max:255',
            'salary' => 'required|numeric',
            'industry' => 'required',
            'project_cycle' => 'required|integer',
            'project_begin_time' => 'required|date',
            'description' => 'required|max:2000',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        if(RateLimiter::instance()->increase('weapp_create_demand',$user->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $address = $request->input('address');

        $demand = Demand::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'address' => is_array($address)?json_encode($address):$address,
            'salary' => $request->input('salary'),
            'industry' => $request->input('industry'),
            'project_cycle' => $request->input('project_cycle'),
            'project_begin_time' => $request->input('project_begin_time'),
            'description' => $request->input('description'),
            'status' => Demand::STATUS_PUBLISH,
            'expired_at' => strtotime('+7 days'),
        ]);
        DemandUserRel::create([
            'user_id'=>$user->id,
            'demand_id'=>$demand->id
        ]);
        $this->dispatch((new CloseDemand($demand->id))->delay(Carbon::createFromTimestamp(strtotime('+7 days'))));
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function update(Request $request){
        $validateRules = [
            'id'   => 'required|integer',
            'title'=> 'required|max:255',
            'address'=> 'required|max:255',
            'salary' => 'required|numeric',
            'industry' => 'required',
            'project_cycle' => 'required|integer',
            'project_begin_time' => 'required|date',
            'description' => 'required|max:2000',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        if(RateLimiter::instance()->increase('weapp_update_demand',$user->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $demand = Demand::findOrFail($request->input('id'));
        if ($demand->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $address = $request->input('address');

        $demand->update([
            'title' => $request->input('title'),
            'address' => is_array($address)?json_encode($address):$address,
            'salary' => $request->input('salary'),
            'industry' => $request->input('industry'),
            'project_cycle' => $request->input('project_cycle'),
            'project_begin_time' => $request->input('project_begin_time'),
            'description' => $request->input('description'),
        ]);
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function close(Request $request){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        if(RateLimiter::instance()->increase('weapp_close_demand',$user->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $demand = Demand::findOrFail($request->input('id'));
        if ($demand->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $demand->status = Demand::STATUS_CLOSED;
        $demand->save();
        return self::createJsonData(true,['id'=>$demand->id]);
    }

}