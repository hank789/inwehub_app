<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->primary();              //用户UID
            $table->string('company_name',256)->comment('公司名字');
            $table->string('company_workers')->default(0)->comment('公司人数');
            $table->string('company_credit_code')->default(0)->comment('统一社会信用代码');
            $table->string('company_bank')->default(0)->comment('开户银行');
            $table->string('company_bank_account')->default(0)->comment('开户账户');
            $table->string('company_address')->default(0)->comment('公司地址');
            $table->string('company_work_phone')->default(0)->comment('公司电话');
            $table->tinyInteger('company_represent_person_type')->default(0)->comment('公司对接人类型,0为其他人,1为当前用户');

            $table->string('company_represent_person_name')->nullable()->comment('公司对接人姓名');
            $table->string('company_represent_person_title')->nullable()->comment('公司对接人职位');
            $table->string('company_represent_person_phone')->nullable()->comment('公司对接人手机号');
            $table->string('company_represent_person_email')->nullable()->comment('公司对接人邮箱');

            $table->tinyInteger('company_auth_mode')->default(1)->comment('公司认证模式,1为协议验证,2为打款验证');
            $table->tinyInteger('apply_status')->default(0)->comment('认证状态:1待认证,2认证成功,3认证失败');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company');
    }
}
