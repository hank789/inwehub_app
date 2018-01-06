<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Activity\Coupon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CouponObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  Coupon  $coupon
     * @return void
     */
    public function created(Coupon $coupon)
    {
        $fields[] = [
            'title' => '红包类型',
            'value' => $coupon->getCouponTypeName(),
            'short' => false
        ];

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'     => 'good',
                    'fields' => $fields
                ]
            )->send('用户'.$coupon->user->id.'['.$coupon->user->name.']领取了红包,金额:'.$coupon->coupon_value);
    }



}