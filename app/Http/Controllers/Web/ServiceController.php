<?php namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;

/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:49
 * @email: hank.huiwang@gmail.com
 */

class ServiceController extends Controller
{
    public function register()
    {
        return view('h5::service');
    }

    public function about(){
        return view('h5::test');
    }

    public function getQuestionShareImage($qid,$uid){
        $question = Question::findOrFail($qid);
        $user = User::findOrFail($uid);
        $data = [
            'username' => $user->name,
            'user_avatar' => $user->avatar,
            'price' => $question->price,
            'question_title' => $question->title,
            'question_username' => $question->hide?'匿名':$question->user->name,
            'qrcode' => config('app.mobile_url').'#/ask/offer/answers/'.$question->id,
            'tags' => $question->tags()->wherePivot('is_display',1)->get()->toArray()
        ];
        return view('h5::image.questionShareLong')->with('data',$data);
    }

}