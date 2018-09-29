<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScraperJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraper_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid',255)->unique()->comment('唯一id');
            $table->string('source_url',1024)->index()->comment('原文地址');
            $table->string('title', 1024)->default('')->comment('职位');
            $table->string('company')->default('')->comment('招聘公司');
            $table->string('city')->default('')->comment('城市');
            $table->string('summary',1024)->default('')->comment('概述');
            $table->string('tags')->default('')->comment('标签');
            $table->integer('topic_id')->unsigned()->default(0)->index();                  //所属话题
            $table->integer('group_id')->unsigned()->default(0);                  //所属圈子
            $table->tinyInteger('status')->default(1)->index();                        //状态1待审核,2已审核,3已删除
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
        Schema::dropIfExists('scraper_jobs');
    }
}
