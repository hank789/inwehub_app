<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecommendQa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operate_recommend_qa', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');                   //主题
            $table->string('user_name');                       //名字
            $table->string('user_avatar_url');                      //头像地址
            $table->integer('price'); //金额
            $table->tinyInteger('sort');                 //排序字段 ASC
            $table->tinyInteger('status')->default(1);   //状态
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
        Schema::drop('operate_recommend_qa');
    }
}
