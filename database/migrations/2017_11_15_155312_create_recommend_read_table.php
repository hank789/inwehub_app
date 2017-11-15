<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecommendReadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommend_read', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->tinyInteger('read_type')->default(0)->index()->comment('分类');
            $table->unsignedInteger("source_id");
            $table->string("source_type");
            $table->text('data');
            $table->tinyInteger('sort');                 //排序字段 ASC
            $table->tinyInteger('audit_status')->nullable()->default(1)->index()->comment('审核状态 0-未审核 1-已审核 2-未通过');
            $table->timestamps();
            $table->unique(["source_id", "source_type"], 'recommend_read_source_id_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recommend_read');

    }
}
