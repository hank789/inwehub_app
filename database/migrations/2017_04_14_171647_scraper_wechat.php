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
        Schema::connection('inwehub_read')->create('wechat_add_mp_list', function (Blueprint $table) {
            $table->increments('_id');
            $table->string('name', 50)->default('')->comment('要添加的公众号名称');                  // 名称
            $table->string('wx_hao',50)->default('')->comment('公众号的微信号');                     // 描述
            $table->timestamps();
        });
        Schema::connection('inwehub_read')->create('wechat_mp_info', function (Blueprint $table) {
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
            $table->tinyInteger('status')->default(0);                        //状态0待审核,1已审核

        });

        Schema::connection('inwehub_read')->create('wechat_wenzhang_statistics', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('wz_id')->default(0)->comment('对应的文章ID');
            $table->dateTime('create_time')->nullable()->comment('统计时间');
            $table->integer('read_count')->default(0)->comment('新增阅读数');
            $table->integer('like_count')->default(0)->comment('新增点攒数');
            $table->integer('comment_count')->default(0)->comment('新增评论数');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('inwehub_read')->drop('wechat_add_mp_list');
        Schema::connection('inwehub_read')->drop('wechat_mp_info');
        Schema::connection('inwehub_read')->drop('wechat_wenzhang_statistics');

    }
}
