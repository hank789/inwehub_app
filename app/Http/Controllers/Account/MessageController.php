<?php

namespace App\Http\Controllers\Account;

use App\Exceptions\ApiException;
use App\Jobs\SendMessage;
use App\Models\IM\MessageRoom;
use App\Models\IM\RoomUser;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Notifications\NewMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'text' => 'required|max:65535',
        'to_user_id' => 'required|integer',
        'from_user_id' => 'required|integer'
    ];


    /**
     * 我的私信首页
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
        $loginUser = Auth()->user();

        /*子查询进行分组*/
        $subQuery = Message::where("to_user_id","=",$loginUser->id)->where("to_deleted","=",0)->orderBy("created_at","desc");

        /*联查子查询再进行排序*/
        $messages = DB::table(DB::raw("({$subQuery->toSql()}) as t "))
            ->mergeBindings($subQuery->getQuery())
            ->select("*")
            ->groupBy("from_user_id")
            ->orderBy("created_at","desc")
            ->paginate(Config::get('api_data_page_size'));

        $messages->map(function($message) {
            $message->fromUser = User::find($message->from_user_id);
        });

        return view('theme::message.index')->with('messages',$messages);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $toUser = User::find($request->input('to_user_id'));
        if(!$toUser){
            abort(404);
        }
        $fromUser = User::find($request->input('from_user_id'));
        if(!$fromUser){
            abort(404);
        }

        $this->validate($request,$this->validateRules);

        $this->dispatch(new SendMessage($request->input('text'),$fromUser->id,[$toUser->id]));

        return $this->success(route('auth.message.show',['room_id'=>$request->input('room_id')]),'消息发送成功');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($room_id)
    {

        //客服
        $role = Role::customerService()->first();
        $role_user = RoleUser::where('role_id',$role->id)->first();
        if (!$role_user) {
            throw new ApiException(ApiException::ERROR);
        }
        $customer_id = $role_user->user_id;
        $user = User::find($customer_id);
        $roomUser = RoomUser::where('room_id',$room_id)->where('user_id','!=',$customer_id)->first();

        $messages = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $room_id)
            ->select('im_messages.*')
            ->orderBy('im_messages.id', 'asc')
            ->paginate(Config::get('api_data_page_size'));

        return view('theme::message.show')->with('toUser',$roomUser->user)->with('fromUser',$user)->with('messages',$messages)->with('room_id',$room_id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $loginUser = $request->user();
        $message = Message::find($id);

        if(!$message){
            abort(404);
        }


        /*收件人删除*/
        if( $message->to_user_id === $loginUser->id )
        {
            $message->to_deleted = 1;
            $message->save();
        }else if( $message->from_user_id === $loginUser->id ){
            $message->from_deleted = 1;
            $message->save();
        }else{
            return response('error');
        }

        /*删除双方都删除过的信息*/
        if( $message->to_deleted == 1 && $message->from_deleted == 1 ){
            $message->delete();
        }

        return response('ok');

    }

    public function destroySession(Request $request,$from_user_id)
    {

        $loginUser = $request->user();

        /*删除给我的消息*/

        Message::where('to_user_id','=',$loginUser->id)
               ->where('from_user_id','=',$from_user_id)
               ->update(['to_deleted'=>1]);

        /*删除我发的消息*/
        Message::where('to_user_id','=',$from_user_id)
            ->where('from_user_id','=',$loginUser->id)
            ->update(['from_deleted'=>1]);


        /*删除双方都删除的所有消息*/

        Message::where('to_deleted','=',1)->where('from_deleted','=',1)->delete();

        return response('ok');


    }


}
