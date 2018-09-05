<?php namespace App\Console\Commands\Pay;
/**
 * @author: wanghui
 * @date: 2017/6/30 下午2:35
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Pay\Order;
use Illuminate\Console\Command;
use Payment\Client\Query;
use Payment\Config;

class WechatQueryPay extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pay:wechat:pay:query {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微信支付订单查询';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $order = Order::find($id);
        //查询一次订单状态
        switch($order->pay_channel){
            case Order::PAY_CHANNEL_WX_PUB:
                $pay_config = config('payment')['wechat_pub'];
                break;
            case Order::PAY_CHANNEL_WX_APP:
                $pay_config = config('payment')['wechat'];
                break;
        }
        if(isset($pay_config)){
            try{
                $ret = Query::run(Config::WX_CHARGE,$pay_config,['out_trade_no'=>$order->order_no]);
                var_dump($ret);
            } catch (\Exception $e){
                var_dump($e->getMessage());
                \Log::error('查询微信支付订单失败',['msg'=>$e->getMessage(),'order'=>$order]);
            }

        }
    }
}