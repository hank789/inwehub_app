<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("company",255)->after('city')->default('');
            $table->string("address_detail",255)->after('city')->default('');
            $table->string('hometown_province',12)->after('city')->nullable();       //居住省份
            $table->string('hometown_city',12)->after('hometown_province')->nullable();           //居住城市
        });

        Schema::table('user_tags', function (Blueprint $table) {
            $table->integer('industries')->unsigned()->default(0);
        });

        //工作经历
        Schema::create('user_job_info', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string("company",64)->nullable()->comment('公司');
            $table->string('title',32)->nullable()->comment('职位');
            $table->string('begin_time',10)->nullable()->comment('开始时间,格式:Y-m');
            $table->string('end_time',10)->default('')->comment('结束时间,格式:Y-m');
            $table->text('description')->nullable()->comment('经历描述');
            $table->softDeletes();
            $table->timestamps();
        });

        //项目经历
        Schema::create('user_project_info', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string("project_name",64)->nullable()->comment('项目名称');
            $table->string('title',32)->nullable()->comment('项目职位');
            $table->string("customer_name",64)->nullable()->comment('客户名称');
            $table->string('begin_time',10)->nullable()->comment('开始时间,格式:Y-m');
            $table->string('end_time',10)->default('')->comment('结束时间,格式:Y-m');
            $table->text('description')->nullable()->comment('描述');
            $table->softDeletes();
            $table->timestamps();
        });

        //教育经历
        Schema::create('user_edu_info', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string("school",64)->nullable()->comment('学校');
            $table->string('major',32)->nullable()->comment('专业');
            $table->string('degree',32)->nullable()->comment('学历');
            $table->string('begin_time',10)->nullable()->comment('开始时间,格式:Y-m');
            $table->string('end_time',10)->default('')->comment('结束时间,格式:Y-m');
            $table->text('description')->nullable()->comment('描述');
            $table->softDeletes();
            $table->timestamps();
        });

        //培训经历
        Schema::create('user_train_info', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string("certificate",64)->nullable()->comment('证书,认证名称');
            $table->string('agency',32)->nullable()->comment('机构名');
            $table->string('get_time',10)->nullable()->comment('获取日期');
            $table->text('description')->nullable()->comment('描述');
            $table->softDeletes();
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
        Schema::drop('user_job_info');
        Schema::drop('user_project_info');
        Schema::drop('user_edu_info');
        Schema::drop('user_train_info');

    }
}
