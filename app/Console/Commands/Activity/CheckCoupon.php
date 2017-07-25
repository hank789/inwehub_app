<?php namespace App\Console\Commands\Activity;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Activity\Coupon;
use Illuminate\Console\Command;

class CheckCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ac:check:coupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '红包过期检查';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $coupons = Coupon::where('coupon_status',Coupon::COUPON_STATUS_PENDING)->where('expire_at','<=',date('Y-m-d H:i:s'))->get();
        foreach($coupons as $coupon){
            $coupon->coupon_status = Coupon::COUPON_STATUS_EXPIRED;
            $coupon->save();
        }
    }

}