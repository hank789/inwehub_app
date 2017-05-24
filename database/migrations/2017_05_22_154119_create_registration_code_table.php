<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_registration_code', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recommend_uid')->default('0')->unsigned()->index();
            $table->string('keyword')->nullable()->comment('邀请对象关键词');
            $table->string('code',32)->unique()->commnet('注册码');
            $table->tinyInteger('status')->default(0)->comment('状态:0未生效,1已生效,2已使用');
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
        Schema::drop('user_registration_code');

    }
}
