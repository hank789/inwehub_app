<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\UserRegistrationCode;
use Illuminate\Console\Command;

class CheckRgCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check:rg_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '邀请码失效检查';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $codes = UserRegistrationCode::where('status',UserRegistrationCode::CODE_STATUS_PENDING)->where('expired_at','<=',date('Y-m-d H:i:s'))->get();
        foreach($codes as $code){
            $code->status = UserRegistrationCode::CODE_STATUS_EXPIRED;
            $code->save();
        }
    }

}