<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //项目经历
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string("project_name")->nullable()->comment('项目名称');
            $table->decimal('project_amount')->nullable()->comment('项目预算');
            $table->string('province',12)->nullable();       //省份
            $table->string('city',12)->nullable();           //城市
            $table->string('company_name')->nullable()->comment('公司名称');
            $table->text('description')->nullable()->comment('描述');
            $table->integer('status')->comment('状态:0,待发布,1已发布')->default('1');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('user_data', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_company')->default(0); //公司认证属性
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
    }
}
