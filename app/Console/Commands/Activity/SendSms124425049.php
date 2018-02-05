<?php namespace App\Console\Commands\Activity;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\SendPhoneMessage;
use App\Models\Activity\Coupon;
use App\Models\User;
use Illuminate\Console\Command;

class SendSms124425049 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ac:send:sms:124425049 {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '2018二月新春活动';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        if($id){
            $users = User::where('id',$id)->get();
        } else {
            $users = User::where('status',1)->get();
        }
        foreach ($users as $user) {
            dispatch((new SendPhoneMessage($user->mobile,['name' => $user->name],'201802-happy-activity')));
        }

    }

}