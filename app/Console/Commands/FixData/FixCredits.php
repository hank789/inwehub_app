<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Jobs\FixUserCredits;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\Credit as CreditModel;

class FixCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复积分数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        Collection::where('subject','付费围观')->delete();
        CreditModel::whereIn('action',[
            CreditModel::KEY_NEW_UPVOTE,
            CreditModel::KEY_ANSWER_UPVOTE,
            CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE,
            CreditModel::KEY_READHUB_SUBMISSION_UPVOTE
        ])->delete();
        CreditModel::whereIn('action',[
            CreditModel::KEY_NEW_COLLECT,
            CreditModel::KEY_PRO_OPPORTUNITY_SIGNED,
            CreditModel::KEY_READHUB_SUBMISSION_COLLECT,
            CreditModel::KEY_COMMUNITY_ANSWER_COLLECT
        ])->delete();
        CreditModel::whereIn('action',[
            CreditModel::KEY_ANSWER,
            CreditModel::KEY_FIRST_ANSWER,
            CreditModel::KEY_FIRST_COMMUNITY_ANSWER,
            CreditModel::KEY_COMMUNITY_ANSWER])->delete();
        CreditModel::whereIn('action',['readhub_new_comment',CreditModel::KEY_NEW_COMMENT])->delete();
        CreditModel::whereIn('action',['rate_answer','feedback_rate_answer','new_answer_feedback'])->delete();
        CreditModel::whereIn('action',[
            CreditModel::KEY_READHUB_SUBMISSION_SHARE,
            CreditModel::KEY_ANSWER_SHARE,
            CreditModel::KEY_COMMUNITY_ANSWER_SHARE
        ])->delete();
        CreditModel::whereIn('action',[CreditModel::KEY_NEW_FOLLOW,CreditModel::KEY_COMMUNITY_ASK_FOLLOWED])->delete();

        foreach ($users as $user) {
            dispatch(new FixUserCredits($user->id));
        }
    }

}