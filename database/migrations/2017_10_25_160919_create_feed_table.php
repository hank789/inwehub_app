<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attentions', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('collections', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::create('feeds', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->tinyInteger('feed_type')->default(0)->index()->comment('分类');
            $table->tinyInteger('is_anonymous')->default(0)->comment('是否匿名');
            $table->morphs('source');
            $table->text('data');
            $table->tinyInteger('audit_status')->nullable()->default(1)->index()->comment('审核状态 0-未审核 1-已审核 2-未通过');
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
        Schema::table('attentions', function (Blueprint $table) {
            $table->dropIndex('attentions_user_id_index');
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->dropIndex('collections_user_id_index');
        });

        Schema::drop('feeds');
    }
}
