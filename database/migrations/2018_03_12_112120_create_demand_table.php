<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->index()->unsigned();
            $table->string('title')->comment('标题');
            $table->string("address")->comment('地点');
            $table->float('salary')->comment('薪资');
            $table->string("industry")->comment('行业');
            $table->tinyInteger("project_cycle")->comment('项目周期');
            $table->string('project_begin_time',12)->comment('项目开始时间');
            $table->string('description',3072)->comment('需求描述');
            $table->timestamp('expired_at')->comment('过期时间');
            $table->integer('views')->unsigned()->comment('查看次数')->default(0);
            $table->integer('status')->comment('状态:0,待发布,1已发布,2被拒绝,3已关闭，4已过期')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('demand_user_rel', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_oauth_id')->unsigned();
            $table->integer('demand_id')->unsigned();
            $table->unique(['user_oauth_id','demand_id']);
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
        Schema::dropIfExists('demand');
        Schema::dropIfExists('demand_user_rel');
    }
}
