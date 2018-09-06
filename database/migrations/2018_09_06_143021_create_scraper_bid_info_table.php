<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScraperBidInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraper_bid_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid',255)->unique()->comment('唯一id');
            $table->string('source_url',1024)->index()->comment('原文地址');
            $table->string('title', 1024)->default('')->comment('标题');
            $table->string('projectname', 1024)->default('')->comment('项目名称');
            $table->string('projectcode')->default('')->comment('项目编号');
            $table->string('buyer')->default('')->comment('发布招标信息者');
            $table->string('toptype')->default('')->comment('招标大类');
            $table->string('subtype')->default('')->comment('招标小类');
            $table->string('area')->default('')->comment('招标区域');
            $table->string('budget')->default('')->comment('预算金额');
            $table->string('bidamount')->default('')->comment('中标金额');
            $table->dateTime('bidopentime')->nullable()->comment('投标截止时间');
            $table->string('industry')->default('')->comment('所属行业');
            $table->string('s_subscopeclass')->default('')->comment('行业细分');
            $table->string('winner')->default('')->comment('中标者');
            $table->text('detail')->default('')->comment('招标详情');
            $table->dateTime('publishtime')->nullable()->comment('发布时间');
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
        Schema::dropIfExists('scraper_bid_info');
    }
}
