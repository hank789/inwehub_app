<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //结算表
        Schema::create('settlement', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->morphs('source');
            $table->integer('status')->comment('结算状态:0待结算,1结算中,2已结算,3结算失败')->default('0');
            $table->timestamp('settlement_date')->index()->comment('结算日期:Y-m-d')->nullable();
            $table->string('actual_amount')->nullable()->comment('实际结算金额');
            $table->string('actual_fee')->nullable()->comment('实际结算手续费');
            $table->timestamp('actual_settlement_date')->comment('实际结算日期:Y-m-d H:i:s')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settlement');

    }
}
