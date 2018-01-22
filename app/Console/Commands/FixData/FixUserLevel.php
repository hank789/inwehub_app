<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */
use App\Models\User;
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
            $level = $user->getUserLevel();
            $user->userData->user_level = $level;
            $user->userData->save();
            $user->is_expert = ($user->authentication && $user->authentication->status == 1) ? 1 : 0;
            $user->save();
        }
    }

}