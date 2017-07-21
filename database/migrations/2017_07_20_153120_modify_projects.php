<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('projects');
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->string("project_name",1024)->comment('项目名称');
            $table->tinyInteger("project_type")->comment('项目类型');
            $table->tinyInteger("project_stage")->comment('项目阶段');
            $table->string('project_description',3072)->comment('项目简介');
            $table->integer('status')->comment('状态:0,待发布,1已发布,2被拒绝')->default('0');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('project_detail', function (Blueprint $table) {
            $table->integer('project_id')->unsigned()->primary();
            $table->integer('user_id')->unsigned();
            $table->tinyInteger("worker_num")->comment('顾问数量');
            $table->tinyInteger("worker_level")->comment('顾问级别');
            $table->string('project_amount',10)->comment('项目预算,单位万');
            $table->tinyInteger("billing_mode")->comment('计费模式');
            $table->string('project_begin_time',12)->comment('项目开始时间');
            $table->tinyInteger("project_cycle")->comment('项目周期');
            $table->tinyInteger("work_intensity")->comment('工作密度');
            $table->tinyInteger("remote_work")->comment('是否接受远程工作');
            $table->tinyInteger("travel_expense")->comment('差旅费用模式');
            $table->string('work_address',2048)->comment('工作地点');

            $table->string('company_name',1024)->comment('企业名称');
            $table->string('company_description',3072)->comment('企业简介');
            $table->tinyInteger('company_represent_person_is_self')->comment('对接人是否本人');
            $table->string('company_represent_person_name',64)->comment('对接人姓名');
            $table->string('company_represent_person_title',64)->comment('对接人职位');
            $table->string('company_represent_person_phone',32)->comment('对接人手机');
            $table->string('company_represent_person_email',68)->comment('对接人邮箱');
            $table->string('company_billing_title',68)->comment('发票抬头信息');
            $table->string('company_billing_bank',68)->comment('开户银行');
            $table->string('company_billing_account',68)->comment('开户账户');
            $table->string('company_billing_taxes',68)->comment('纳税识别号');
            $table->string('qualification_requirements',3072)->comment('认证资质');
            $table->string('other_requirements',3072)->comment('其它资质');
            $table->tinyInteger('is_view_resume')->comment('是否需要查看顾问简历');
            $table->tinyInteger('is_apply_request')->comment('是否需要顾问投递申请');
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
        Schema::drop('projects');
        Schema::drop('project_detail');
    }
}
