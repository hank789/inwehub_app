<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */
use App\Models\Answer;
use App\Models\LoginRecord;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\AwakeUserQuestion;
use App\Services\RateLimiter;
use Illuminate\Console\Command;

class AwakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:awake-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天早上8点和下午19点唤起未登陆用户';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loginRecords = LoginRecord::where('created_at','>=',date('Y-m-d 00:00:00'))->where('created_at','<=',date('Y-m-d 23:59:59'))->distinct()->pluck('user_id')->toArray();
        $users = User::get();
        foreach ($users as $user) {
            if (RateLimiter::instance()->getValue('push_notify_user_'.date('Ymd'),$user->id) >= 2) continue;
            if (in_array($user->id,$loginRecords)) continue;
            $notified = false;
            $userTags = UserTag::where('views','>=',1)->pluck('user_id')->toArray();
            $userTags = array_merge($userTags,UserTag::where('articles','>=',1)->pluck('user_id')->toArray());
            $userTags = array_merge($userTags,UserTag::where('supports','>=',1)->pluck('user_id')->toArray());
            $userTags = array_merge($userTags,UserTag::where('answers','>=',1)->pluck('user_id')->toArray());
            $userTags = array_merge($userTags,UserTag::where('adoptions','>=',1)->pluck('user_id')->toArray());
            $userTags = array_merge($userTags,UserTag::where('industries','>=',1)->pluck('user_id')->toArray());
            $userTags = array_unique($userTags);
            if ($userTags) {
                $questionIds = Taggable::whereIn('tag_id',$userTags)->where('taggable_type','=','App\Models\Question')->pluck('taggable_id')->toArray();
                if ($questionIds) {
                    $questions = Question::whereIn('id',$questionIds)->where('question_type',2)->where('status','<=',6)->where('price','>',0)->get();
                    foreach ($questions as $question) {
                        if($user->id == $question->user_id) continue;
                        $invitation = QuestionInvitation::where('user_id',$user->id)->where('from_user_id',0)->where('question_id',$question->id)->first();
                        if ($invitation) continue;
                        $answer = Answer::where('question_id',$question->id)->where('user_id',$user->id)->first();
                        if ($answer) continue;
                        QuestionInvitation::create([
                            'from_user_id'=> 0,
                            'question_id'=> $question->id,
                            'user_id'=> $user->id,
                            'send_to'=> 'auto' //标示自动匹配
                        ]);
                        $user->notify(new AwakeUserQuestion($user->id, $question));
                        $notified = true;
                        break;
                    }
                }
            }
            if (!$notified) {
                $questions = Question::where('question_type',2)->where('status','<=',6)->where('price','>',0)->get();
                foreach ($questions as $question) {
                    if($user->id == $question->user_id) continue;
                    $invitation = QuestionInvitation::where('user_id',$user->id)->where('from_user_id',0)->where('question_id',$question->id)->first();
                    if ($invitation) continue;
                    $answer = Answer::where('question_id',$question->id)->where('user_id',$user->id)->first();
                    if ($answer) continue;
                    QuestionInvitation::create([
                        'from_user_id'=> 0,
                        'question_id'=> $question->id,
                        'user_id'=> $user->id,
                        'send_to'=> 'auto' //标示自动匹配
                    ]);
                    $user->notify(new AwakeUserQuestion($user->id, $question));
                    break;
                }
            }
        }
    }

}