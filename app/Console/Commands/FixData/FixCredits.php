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
        foreach ($users as $user) {
            dispatch(new FixUserCredits($user->id));
        }
    }

}