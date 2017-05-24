<?php namespace App\Api\Controllers\Account;

use App\Exceptions\ApiException;
use App\Models\Attention;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Http\Requests;

class FollowController extends Controller
{

    /**
     * 添加模型的关注包含问题、用户等
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type, Request $request)
    {

        $validateRules = [
            'id' => 'required|integer',
        ];

        $this->validate($request,$validateRules);

        $source_id = $request->input('id');

        if($source_type === 'question'){
            $source  = Question::find($source_id);
            $subject = $source->title;
        }else if($source_type === 'user'){
            $source  = User::find($source_id);
            $subject = $source->name;
        }else if($source_type==='tag'){
            $source  = Tag::find($source_id);
            $subject = $source->name;
        }

        if(!$source){
            abort(404);
        }

        /*再次关注相当于是取消关注*/
        $attention = Attention::where("user_id",'=',$request->user()->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
        if($attention){
            $attention->delete();
            if($source_type==='user'){
                $source->userData->decrement('followers');
            }else{
                $source->decrement('followers');
            }
            return self::createJsonData(true,[],ApiException::SUCCESS,'取消关注成功');
        }

        $data = [
            'user_id'     => $request->user()->id,
            'source_id'   => $source_id,
            'source_type' => get_class($source),
        ];

        $attention = Attention::create($data);

        if($attention){
            switch($source_type){
                case 'question' :
                    $this->notify($request->user()->id,$source->user_id,'follow_question',$subject,$source->id);
                    $this->doing($request->user()->id,'follow_question',get_class($source),$source_id,$subject);
                    $source->increment('followers');
                    break;
                case 'user':
                    $source->userData->increment('followers');
                    $this->notify($request->user()->id,$source->id,'follow_user');
                    break;
                case 'tag':
                    $source->increment('followers');
                    break;
            }
        }

        return self::createJsonData(true,[],ApiException::SUCCESS,'关注成功');


    }




}
