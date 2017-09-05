<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_notice', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->tinyInteger('notification_type')->default(1)->comment('通知分类');
            $table->string('title');
            $table->string('url');
            $table->tinyInteger('status')->default(0);   //状态
            $table->string('setting')->nullable();;
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string("current_app_version",32)->default('1.0.0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_notice');
    }
}
