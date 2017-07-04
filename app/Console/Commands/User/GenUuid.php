<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\User;
use Illuminate\Console\Command;

class GenUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:gen_uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为未生成uuid的用户生成uuid';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereNull('uuid')->get();
        foreach($users as $user){
            $user->uuid = gen_user_uuid();
            $user->save();
        }
    }

}