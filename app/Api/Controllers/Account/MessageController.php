<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
            $contact_id = Role::getCustomerUserId();
        }

        $contact = User::find($contact_id);
        $messages = $user->conversations()
            ->where('contact_id', $contact_id)
            ->orderBy('im_conversations.id', 'desc')
            ->simplePaginate(Config::get('api_data_page_size'))->toArray();

        $this->markAllAsRead($contact_id);

        $messages['data'] = array_reverse($messages['data']);
        $user_avatars = [];
        $user_avatars[$user->id] = $user->avatar;
        $user_avatars[$contact->id] = $contact->avatar;

        foreach ($messages['data'] as &$item) {
            $item['avatar'] = $user_avatars[$item['user_id']];
        }
        return self::createJsonData(true,$messages);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'text'    => 'required_without:img',
            'img'    => 'required_without:text',
            'contact_id' => 'required|integer',
        ]);

        if ($request->contact == Auth::user()->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $contact_id = $request->input('contact_id');
        if (!$contact_id) {
            //客服
            $contact_id = Role::getCustomerUserId();
        }
        $base64Img = $request->input('img');
        $data = [];
        $data['text'] = $request->input('text');
        if ($base64Img) {
            $url = explode(';',$base64Img);
            $url_type = explode('/',$url[0]);
            $file_name = 'message/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
            dispatch((new UploadFile($file_name,(substr($url[1],6)))));
            $data['img'] = Storage::disk('oss')->url($file_name);
        }
        $message = Auth::user()->messages()->create([
            'data' => $data,
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
