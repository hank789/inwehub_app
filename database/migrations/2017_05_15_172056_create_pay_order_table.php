<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //订单表
        Schema::create('pay_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('order_no')->unique()->commnet('商户订单号,系统生成');
            $table->string('transaction_id')->nullable()->commnet('记录三方支付放回的订单号');
            $table->string('subject')->commnet('支付title');
            $table->string('body')->nullable()->comment('支付详情');
            $table->string('amount')->comment('支付金额');
            $table->string('return_param')->nullable()->comment('请求自定义参数');
            $table->string('client_ip',32);
            $table->string('response_msg')->nullable()->comment('第三方响应信息');
            $table->string('finish_time',32)->nullable()->comment('支付完成时间,Y-m-d H:i:s');
            $table->json('response_data')->nullable()->comment('第三方返回完整信息');
            $table->tinyInteger('pay_channel')->default(1)->comment('支付方式:1微信app支付,2微信公众号支付,3微信扫码支付,4微信刷卡支付,5微信小程序支付,6微信wap支付,7支付宝app支付');
            $table->tinyInteger('status')->default(0)->comment('订单状态:0待支付,1支付处理中,2支付成功,3支付失败');
            $table->timestamps();
        });

        //订单关联表
        Schema::create('pay_order_gables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pay_order_id')->unsigned()->index();  //订单ID
            $table->morphs('pay_order_gable');
            $table->timestamps();
        });

        //提现表
        Schema::create('withdraw', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('order_no')->unique()->commnet('商户订单号,系统生成');
            $table->string('transaction_id')->nullable()->commnet('记录三方支付放回的订单号');
            $table->string('amount')->comment('提现金额');
            $table->string('return_param')->nullable()->comment('请求自定义参数');
            $table->string('client_ip',32);
            $table->string('response_msg')->nullable()->comment('第三方响应信息');
            $table->string('finish_time',32)->nullable()->comment('提现完成时间,Y-m-d H:i:s');
            $table->json('response_data')->nullable()->comment('第三方返回完整信息');
            $table->tinyInteger('withdraw_channel')->default(1)->comment('提现方式:1微信,2支付宝');
            $table->tinyInteger('status')->default(0)->comment('提现状态:0待处理,1处理中,2处理成功,3处理失败');
            $table->timestamps();
        });

        //用户资金表
        Schema::create('user_money', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->primary();              //用户UID
            $table->decimal('total_money',10,2)->unsigned()->default(0)->comment('总金额');
            $table->decimal('settlement_money',10,2)->unsigned()->default(0)->comment('结算中的金额');

        });

        //用户资金流水表
        Schema::create('user_money_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->morphs('source');
            $table->string('before_money')->comment('未交易前账户金额');
            $table->string('change_money')->comment('交易金额');
            $table->tinyInteger('io')->default(1)->comment('初入账:1入账,-1出账');
            $table->tinyInteger('money_type')->default(1)->comment('资金类型:1提问,2回答,3提现');
            $table->tinyInteger('status')->default(1)->comment('提现状态:0处理中,1处理成功,2处理失败');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['name' => 'need_pay_actual','value' => '1'],
            ['name' => 'withdraw_suspend','value' => '0'],
            ['name' => 'withdraw_auto','value' => '0'],
            ['name' => 'withdraw_day_limit','value' => '1'],
            ['name' => 'withdraw_per_min_money','value' => '10'],
            ['name' => 'withdraw_per_max_money','value' => '2000'],
            ['name' => 'pay_method_weixin','value' => '1'],
            ['name' => 'pay_method_ali','value' => '0'],
            ['name' => 'pay_method_iap','value' => '0'],
            ['name' => 'pay_settlement_cycle','value' => '3']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pay_order');
        Schema::drop('pay_order_gables');
        Schema::drop('user_money_log');
        Schema::drop('user_money');

    }
}
