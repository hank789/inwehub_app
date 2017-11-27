<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('公司名字');
            $table->string('logo')->comment('logo图片');
            $table->string('address_province')->comment('公司省市地址');
            $table->string('address_detail')->comment('公司详细地址');
            $table->string('longitude')->comment('经度');
            $table->string('latitude')->comment('纬度');
            $table->unique('name');
            $table->tinyInteger('audit_status')->nullable()->default(1)->index()->comment('审核状态 0-未审核 1-已审核 2-未通过');
            $table->timestamps();
        });
        Schema::create('company_data_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_data_id')->comment('公司id');
            $table->integer('user_id')->comment('用户id');
            $table->tinyInteger('status')->nullable()->default(1)->comment('状态 1-在职 2-项目 3-离职');
            $table->tinyInteger('audit_status')->nullable()->default(1)->index()->comment('审核状态 0-未审核 1-已审核 2-未通过');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_data');
        Schema::drop('company_data_user');
    }
}
