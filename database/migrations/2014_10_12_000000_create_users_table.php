<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();                           //用户UID
            $table->string('name');                             //姓名
            $table->string('email',128)->index()->nullable();  //邮箱
            $table->string('mobile',24)->unique()->nullable();  //登录手机
            $table->string('password', 64);                     //登录密码
            $table->tinyInteger('gender')->nullable();          //性别: 1-男，2-女，0-保密
            $table->string('avatar')->default('');
            $table->date('birthday')->nullable();               //出生日期
            $table->string('province',12)->nullable();       //工作省份
            $table->string('city',12)->nullable();           //工作城市
            $table->string('title')->nullable();                //头衔
            $table->text('description')->nullable();            //个人简介
            $table->tinyInteger('status')->default(1);          //用户状态0-待审核，1已审核
            $table->string('site_notifications')->nullable();   //站内通知
            $table->string('email_notifications')->nullable();  //邮件通知策略
            $table->rememberToken();                            //记住登录状态
            $table->string('last_login_token')->comment('上次登录token')->nullable();
            $table->timestamps();                               //注册时间，上次更新时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
