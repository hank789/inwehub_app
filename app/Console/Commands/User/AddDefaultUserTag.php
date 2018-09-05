<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\User;
use App\Models\UserTag;
use Illuminate\Console\Command;

class AddDefaultUserTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add_default_tag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为用户生成默认tag';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        foreach($users as $user){
            $userTag = UserTag::where('user_id',$user->id)->where('tag_id',0)->first();
            if (!$userTag) {
                UserTag::create([
                    'user_id' => $user->id,
                    'tag_id'  => 0,
                ]);
            }
        }
    }

}