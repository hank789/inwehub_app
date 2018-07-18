<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScraperFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scraper_feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->unique();                  // 名称
            $table->string('source_type')->comment('文章来源格式,1:rss,2:atom');
            $table->string('source_link');                          // 文章来源链接
            $table->integer("group_id")->unsigned()->index()->default(0);
            $table->tinyInteger('status')->default(0);                        //状态0待审核,1已审核
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
        Schema::dropIfExists('scraper_feeds');
    }
}
