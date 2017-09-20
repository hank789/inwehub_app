<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\User;
use Illuminate\Console\Command;
use Tymon\JWTAuth\JWTAuth;

class RefreshUserLoginToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:refresh:loginToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '使所有APP用户下线，重新登录';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(JWTAuth $JWTAuth)
    {
        $users = User::all();
        foreach($users as $user){
            try {
                if ($user->last_login_token) {
                    $JWTAuth->refresh($user->last_login_token);
                }
            } catch (\Exception $e){
                \Log::error($e->getMessage());
            }
        }
    }

}