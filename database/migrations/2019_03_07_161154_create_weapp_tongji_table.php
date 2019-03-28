<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeappTongjiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weapp_tongji', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_oauth_id')->unsigned()->index();
            $table->string('page', 255)->index()->comment('页面路径');
            $table->string('start_time',13)->comment('进入时间，毫秒');
            $table->string('end_time',13)->comment('离开时间，毫秒');
            $table->integer('stay_time')->default(0)->comment('停留时间,毫秒');
            $table->integer('event_id')->unsigned()->index()->comment('事件id');
            $table->string('parent_refer',32)->index()->comment('父事件相关');
            $table->string('scene',64)->index()->comment('场景值');
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
        Schema::dropIfExists('weapp_tongji');
    }
}
