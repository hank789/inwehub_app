<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInwehubNews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('inwehub')->create('news', function (Blueprint $table) {

            $table->increments('id');
            $table->string('url',255)->unique();                            //新闻链接
            $table->string('title',255);                          //新闻标题
            $table->integer('user_id')->unsigned()->index();                  //发起人
            $table->integer('topic_id')->unsigned()->index();                  //所属话题
            $table->string('site_name',255);                          //站点名字
            $table->string('mobile_url',255);                       //手机站url
            $table->string('author_name',255);                      //作者名字
            $table->timestamp('publish_date')->index();             //发布日期
            $table->tinyInteger('status')->default(0)->index();                        //状态0待审核,1已审核
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
        Schema::connection('inwehub')->drop('news');
    }
}
