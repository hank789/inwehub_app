<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ScraperWechat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraper_wechat_add_mp_list', function (Blueprint $table) {
            $table->increments('_id');
            $table->string('name', 50)->default('')->comment('要添加的公众号名称');                  // 名称
            $table->string('wx_hao',50)->default('')->comment('公众号的微信号');                     // 描述
            $table->timestamps();
        });
        Schema::create('scraper_wechat_mp_info', function (Blueprint $table) {
            $table->increments('_id');
            $table->string('name', 50)->default('')->comment('公众号名称');                  // 名称
            $table->string('wx_hao',20)->default('')->comment('公众号的微信号');                     // 描述
            $table->string('company',100)->default('')->comment('主体名称');                     // 描述
            $table->string('description',200)->default('')->comment('功能简介');
            $table->string('logo_url',200)->default('')->comment('logo url');
            $table->string('qr_url',200)->default('')->comment('二维码URL');
            $table->dateTime('create_time')->nullable()->comment('加入牛榜时间');
            $table->dateTime('update_time')->nullable()->comment('最后更新时间');
            $table->integer('rank_article_release_count')->default(0)->comment('群发次数');
            $table->integer('rank_article_count')->default(0)->comment('群发篇数');
            $table->integer('last_qunfa_id')->default(0)->comment('最后的群发ID');
            $table->dateTime('last_qufa_time')->nullable()->comment('最后一次群发的时间');
            $table->string('wz_url',300)->default('')->comment('最近文章URL');
            $table->integer("group_id")->unsigned()->index()->default(0);
            $table->tinyInteger('status')->default(0);                        //状态0待审核,1已审核
        });
        Schema::create('scraper_news_info', function (Blueprint $table) {
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
            $table->string('content_url',1024)->default('')->comment('文章永久地址');
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
        Schema::drop('scraper_wechat_add_mp_list');
        Schema::drop('scraper_wechat_mp_info');
        Schema::drop('scraper_news_info');

    }
}
