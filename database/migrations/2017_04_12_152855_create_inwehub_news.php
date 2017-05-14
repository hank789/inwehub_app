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
        Schema::connection('inwehub')->create('news_info', function (Blueprint $table) {
            $table->increments('_id');
            $table->string('title', 1024)->default('')->comment('文章标题');
            $table->string('source_url',1024)->default('')->comment('原文地址');
            $table->string('cover_url',1024)->default('')->comment('封面图URL');
            $table->string('description',2048)->default('')->comment('文章摘要');
            $table->dateTime('date_time')->nullable()->comment('文章推送时间')->index();
            $table->integer('mp_id')->default(0)->index()->comment('对应的公众号ID');
            $table->integer('read_count')->default(0)->comment('阅读数');
            $table->integer('like_count')->default(0)->comment('点攒数');
            $table->integer('comment_count')->default(0)->comment('评论数');
            $table->string('content_url',1024)->default('')->unique()->comment('文章永久地址');
            $table->string('mobile_url',1024)->default('')->comment('手机站url');
            $table->string('site_name',255)->default('')->comment('站点名字');                          //站点名字
            $table->string('author',50)->default('')->comment('作者');
            $table->integer('msg_index')->default(0)->comment('一次群发中的图文顺序 1是头条 ');
            $table->integer('copyright_stat')->default(0)->comment('11表示原创 其它表示非原创');
            $table->integer('qunfa_id')->default(0)->comment('群发消息ID');
            $table->integer('source_type')->default(1)->index()->comment('来源:1微信公众号,2feed');
            $table->integer('type')->default(0)->comment('消息类型');
            $table->integer('topic_id')->unsigned()->default(0)->index();                  //所属话题
            $table->tinyInteger('status')->default(1)->index();                        //状态0待审核,1已审核
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
        Schema::connection('inwehub')->drop('news_info');
    }
}
