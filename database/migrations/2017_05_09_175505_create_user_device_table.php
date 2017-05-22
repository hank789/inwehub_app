<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_device', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->string('client_id',128)->comment('推送设备唯一标示');
            $table->string('device_token',128)->comment('Android - 2.2+ (支持): 设备的唯一标识号，通常与clientid值一致。iOS - 4.5+ (支持): 设备的DeviceToken值，向APNS服务器发送推送消息时使用');
            $table->string('appid',128)->nullable()->comment('第三方推送服务的应用标识');
            $table->string('appkey',128)->nullable()->comment('第三方推送服务器的应用键值');
            $table->integer('device_type')->comment('设备类型,1安卓,2苹果')->default('1');
            $table->tinyInteger('status')->default(1)->comment('状态:1登陆,0未登录');   //状态
            $table->timestamps();
            $table->unique(['user_id','client_id','device_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_device');
    }
}
