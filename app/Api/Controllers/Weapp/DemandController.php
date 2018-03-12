<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2018/3/12 上午11:55
 * @email: wanghui@yonglibao.com
 */
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\CloseDemand;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Models\Weapp\DemandUserRel;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DemandController extends controller {


    public function showList(Request $request){
        $validateRules = [
            'type'   => 'required|in:all,mine'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $type = $request->input('type');
        $data = [];
        switch ($type){
            case 'all':
                $list = DemandUserRel::where('user_id',$user->id)->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $item) {
                    $demand = Demand::find($item->demand_id);
                    $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $data[] = [
                        'title' => $demand->title,
                        'avatar' => $oauth->avatar,
                        'address' => $demand->address,
                        'industry' => $demand->industry,
                        'salary' => $demand->salary
                    ];
                }
                break;
            case 'mine':
                $list = Demand::where('user_id',$user->id)->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
                foreach ($list as $demand) {
                    $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                    $data[] = [
                        'title' => $demand->title,
                        'avatar' => $oauth->avatar,
                        'address' => $demand->address,
                        'industry' => $demand->industry,
                        'salary' => $demand->salary
                    ];
                }
                break;
        }
        $return = $list->toArray();
        $return['data'] = $data;
        return self::createJsonData(true,$return);
    }

    public function detail(Request $request){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $demand = Demand::findOrFail($request->input('id'));
        $demand->increment('views');
        $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
        $data = [
            'publisher_name'=>$oauth->nickname,
            'publisher_avatar'=>$oauth->avatar,
            'publisher_title'=>$demand->user->title,
            'publisher_company'=>$demand->user->company,
            'publisher_email'=>$demand->user->email,
            'title' => $demand->title,
            'address' => $demand->address,
            'salary' => $demand->salary,
            'industry' => $demand->industry,
            'project_cycle' => $demand->project_cycle,
            'project_begin_time' => $demand->project_begin_time,
            'description' => $demand->description,
        ];
        $rel = DemandUserRel::where('user_id',$user->id)->where('demand_id',$demand->id)->first();
        if (!$rel) {
            DemandUserRel::create([
                'user_id'=>$user->id,
                'demand_id'=>$demand->id
            ]);
        }
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
        $demand = Demand::create([
            'title' => $request->input('title'),
            'address' => $request->input('address'),
            'salary' => $request->input('salary'),
            'industry' => $request->input('industry'),
            'project_cycle' => $request->input('project_cycle'),
            'project_begin_time' => $request->input('project_begin_time'),
            'description' => $request->input('description'),
            'status' => Demand::STATUS_PUBLISH,
            'expired_at' => strtotime('+7 days'),
        ]);
        $this->dispatch((new CloseDemand($demand->id))->delay(Carbon::createFromTimestamp(strtotime('+7 days'))));
        return self::createJsonData(true,['id'=>$demand->id]);
    }

    public function update(Request $request){
        $validateRules = [
            'id'   => 'required|integer',
            'title'=> 'required|max:255',
            'address'=> 'required|max:255',
            'salary' => 'required|float',
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
        $demand->update([
            'title' => $request->input('title'),
            'address' => $request->input('address'),
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