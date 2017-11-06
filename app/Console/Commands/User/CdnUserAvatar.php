<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use Illuminate\Console\Command;

class CdnUserAvatar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:cdn:avatar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '保存用户微信等三方头像到cdn';

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
            $user->avatar = saveImgToCdn($user->avatar);
            $user->save();
            $user->userData->user_level = $level;
            $user->userData->save();
            ReadHubUser::initUser($user);
        }
    }

}