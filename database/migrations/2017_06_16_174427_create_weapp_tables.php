<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeappTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger("source")->after('status')->default('0')->comment('注册来源');
        });

        Schema::create('weapp_questions', function (Blueprint $table) {

            $table->increments('id')->unsigned();                             //问题ID

            $table->integer('user_id')->unsigned()->default(0)->index();                  //问题发起人UID

            $table->string('title',500);                          //问题标题

            $table->smallInteger('price')->default(0);            //问题价格

            $table->tinyInteger('is_public')->default(0);              //匿名提问

            $table->integer('answers')->unsigned()->default(0);               //回答数

            $table->integer('views')->unsigned()->default(0);                 //查看数

            $table->integer('comments')->unsigned()->default(0);              //评论数

            $table->tinyInteger('status')->default(0);            //提问状态0待审核,1已审核

            $table->timestamps();                                 //创建和更新时间

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['source']);
        });

        Schema::drop('weapp_questions');
    }
}
