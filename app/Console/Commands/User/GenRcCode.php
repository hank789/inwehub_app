<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\User;
use Illuminate\Console\Command;

class GenRcCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:gen_rc_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为未生成推荐码的用户生成唯一推荐码';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereNull('rc_code')->get();
        foreach($users as $user){
            $user->rc_code = User::genRcCode();
            $user->save();
        }
    }

}