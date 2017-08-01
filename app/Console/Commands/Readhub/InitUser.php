<?php namespace App\Console\Commands\Readhub;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use Illuminate\Console\Command;

class InitUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readhub:user:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化阅读站的用户信息';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        foreach($users as $user){
            ReadHubUser::initUser($user);
        }
    }

}