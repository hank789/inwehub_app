<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\User;
use Illuminate\Console\Command;

class GenUserInfoCompletePercent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:gen_info_complete_percent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成用户的信息完整度';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        foreach($users as $user){
            $user->info_complete_percent = $user->getInfoCompletePercent();
            $user->save();
        }
    }

}