<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */



use Illuminate\Console\Command;

class AddUserToGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:addUserToGroup {groupId} {uid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定用户加入指定圈子中';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $groupId = $this->argument('groupId');
        $userId = $this->argument('uid');
    }

}