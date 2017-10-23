<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\IM\Message;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function getMessages(Request $request)
    {
        $this->validate($request, [
            'contact_id' => 'required|integer',
            'page'       => 'required|integer',
        ]);

        $user = $request->user();
        $contact_id = $request->input('contact_id');
        if (!$contact_id) {
            //客服
            $role = Role::customerService()->first();
            $role_user = RoleUser::where('role_id',$role->id)->first();
            if (!$role_user) {
                throw new ApiException(ApiException::ERROR);
            }
            $contact_id = $role_user->user_id;
        }

        $messages = $user->conversations()
            ->where('contact_id', $contact_id)
            ->simplePaginate(40)->toArray();


        $this->markAllAsRead($contact_id);

        return self::createJsonData(true,$messages);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'text'    => 'required',
            'contact_id' => 'required|integer',
        ]);

        if ($request->contact == Auth::user()->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $contact_id = $request->input('contact_id');
        if (!$contact_id) {
            //客服
            $role = Role::customerService()->first();
            $role_user = RoleUser::where('role_id',$role->id)->first();
            if (!$role_user) {
                throw new ApiException(ApiException::ERROR);
            }
            $contact_id = $role_user->user_id;
        }
        $message = Auth::user()->messages()->create([
            'data' => array_only($request->all(), ['text']),
        ]);

        Auth::user()->conversations()->attach($message, [
            'contact_id' => $contact_id
        ]);

        User::find($contact_id)->conversations()->attach($message, [
            'contact_id' => Auth::user()->id,
        ]);

        // broadcast the message to the other person
        $contact = User::find($contact_id);
        $contact->notify(new NewMessage($contact_id,$message));


        return self::createJsonData(true, $message->toArray());
    }


    /**
     * marks all conversation's messages as read.
     *
     * @param int $contact_id
     *
     * @return void
     */
    protected function markAllAsRead($contact_id)
    {
        Auth::user()->conversations()->where('contact_id', $contact_id)->get()->map(function ($m) {
            if (Auth::user()->id != $m->user_id) $m->update(['read_at' => Carbon::now()]);
        });
    }
}
