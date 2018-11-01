<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['category_name',['resubmit_id']]);
            $table->tinyInteger('hide')->after('top')->default(0);
            $table->tinyInteger('rate_star')->after('rate')->default(0);
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->integer('reviews')->after('followers')->default(0);
        });
        Schema::table('tag_category_rel', function (Blueprint $table) {
            $table->integer('type')->after('category_id')->index()->default(0);
            $table->integer('reviews')->after('category_id')->default(0);
            $table->integer('reviews_rate_sum')->after('category_id')->default(0);
            $table->float('review_average_rate')->after('category_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
