<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Credit as CreditModel;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Console\Command;

class FixUserLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:user-level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复用户等级数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        foreach($users as $user){
            $total_coins = CreditModel::where('user_id',$user->id)->sum('coins');
            $total_credits = CreditModel::where('user_id',$user->id)->sum('credits');
            $userData = UserData::find($user->id);
            $userData->coins = $total_coins;
            $userData->credits = $total_credits;
            $userData->save();
            //更新用户等级
            $next_level = $user->getUserLevel();
            if ($next_level != $userData->user_level) {
                $userData->user_level = $next_level;
                $userData->save();
            }

            $user->is_expert = ($user->authentication && $user->authentication->status == 1) ? 1 : 0;
            $user->save();
        }
    }

}