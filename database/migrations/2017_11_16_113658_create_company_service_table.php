<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_service', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('img_url_slide');                       // 幻灯片图片链接地址
            $table->string('img_url_list');                       // 列表页面图片链接地址
            $table->tinyInteger('sort');                 //排序字段 ASC
            $table->tinyInteger('audit_status')->nullable()->default(1)->index()->comment('审核状态 0-未审核 1-已审核 2-未通过');
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
        Schema::drop('company_service');
    }
}
