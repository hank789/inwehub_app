<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //红包
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->tinyInteger('coupon_type')->default(1)->comment('红包类型:1首次提问')->index();
            $table->string('coupon_value',24)->default(0)->comment('红包金额');
            $table->tinyInteger('coupon_status')->default(1)->comment('红包状态:1未使用 2已使用 3已过期 默认为1')->index();
            $table->string('expire_at',19)->nullable()->comment('过期时间');
            $table->tinyInteger('days')->nullable()->comment('有效期');
            $table->string('used_at',19)->comment('使用日期')->default('');
            $table->integer('used_object_id',19)->nullable()->comment('使用对象id');
            $table->tinyInteger('used_object_type')->nullable()->comment('使用对象类型');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('pay_order', function (Blueprint $table) {
            $table->string('actual_amount')->default(0)->after('amount');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coupons');

    }
}
