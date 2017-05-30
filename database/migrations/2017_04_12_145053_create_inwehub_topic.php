<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInwehubTopic extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::connection('inwehub_read')->create('topic', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();                  //文章发起人


            $table->string('title',500);                          //文章标题

            $table->string('summary',500);                        //导读、摘要

            $table->integer('views')->unsigned()->default(0);                 //查看数

            $table->integer('collections')->unsigned()->default(0);           //收藏数

            $table->integer('comments')->unsigned()->default(0);              //评论数

            $table->integer('supports')->unsigned()->default(0);              //支持数、推荐数目

            $table->integer('order')->unsigned()->default(0)->index();              //排序

            $table->tinyInteger('status')->default(0)->index();                        //状态0待审核,1已审核

            $table->timestamp('publish_date')->index();

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
        Schema::connection('inwehub_read')->drop('topic');
    }
}
