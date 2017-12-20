<?php namespace App\Http\Controllers\Admin\IM;
use App\Exceptions\ApiException;
use App\Http\Controllers\Admin\AdminController;
use App\Jobs\SendMessage;
use App\Models\IM\Conversation;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * @author: wanghui
 * @date: 2017/10/23 下午2:31
 * @email: wanghui@yonglibao.com
 */

class CustomerController extends AdminController {


    public function index(Request $request)
    {
        $filter =  $request->all();

        //客服
        $role = Role::customerService()->first();
        $role_user = RoleUser::where('role_id',$role->id)->first();
        if (!$role_user) {
            throw new ApiException(ApiException::ERROR);
        }
        $contact_id = $role_user->user_id;
        $query = Conversation::where('im_conversations.user_id',$contact_id);
        $query = $query->leftJoin('im_messages','message_id','=','im_messages.id');


        if(isset($filter['user_id']) && $filter['user_id'] > 0 ){
            $query = $query->where('contact_id','=',$filter['user_id']);
        }

        if(isset($filter['is_unread']) && $filter['is_unread'] > 0 ){
            $query = $query->whereNull('im_messages.read_at');
        }

        $messages = $query->select('im_conversations.*','im_messages.read_at')->groupBy('contact_id')->orderBy('im_conversations.id','desc')->paginate(20);
        return view('admin.im.customer.index')->with(compact('filter','messages'));
    }


    public function group(Request $request){
        $validateRules = [
            'message' => 'required|min:5'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            //客服
            $role = Role::customerService()->first();
            $role_user = RoleUser::where('role_id',$role->id)->first();
            if (!$role_user) {
                throw new ApiException(ApiException::ERROR);
            }
            $contact_id = $role_user->user_id;
            $this->dispatch(new SendMessage($request->input('message'),$contact_id));
            return $this->success(route('admin.im.customer.group'),'发送成功');
        }

        return view('admin.im.customer.group')->with('message');
    }

    public function groupTest(Request $request){
        $validateRules = [
            'test_message' => 'required|min:5',
            'test_user_id' => 'required|integer'
        ];
        if($request->isMethod('post')){
            $this->validate($request,$validateRules);
            //客服
            $role = Role::customerService()->first();
            $role_user = RoleUser::where('role_id',$role->id)->first();
            if (!$role_user) {
                throw new ApiException(ApiException::ERROR);
            }
            $contact_id = $role_user->user_id;
            $this->dispatch(new SendMessage($request->input('test_message'),$contact_id,[$request->input('test_user_id')]));
            Session::flash('message','测试发送成功');
            Session::flash('message_type',2);
            return view('admin.im.customer.group')->with('message',$request->input('test_message'));
        }

        return view('admin.im.customer.group');
    }




}