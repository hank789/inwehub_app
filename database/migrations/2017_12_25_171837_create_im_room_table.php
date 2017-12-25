<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im_room', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('创建者id');
            $table->integer('r_type')->unsigned()->comment('房间类型，1为私聊，2为群聊');
            $table->string('r_name',64)->nullable()->comment('房间名字');
            $table->string('r_description')->nullable()->comment('房间描述');
            $table->timestamps();
        });
        Schema::table('im_messages', function (Blueprint $table) {
            $table->integer('room_id')->unsigned()->default(0)->index()->after('user_id');;
        });
        Schema::create('im_room_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->comment('用户id');
            $table->integer('room_id')->unsigned()->index();
            $table->timestamps();
            $table->unique(['user_id','room_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('im_room');
        Schema::dropIfExists('im_room_user');
    }
}
