<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdFeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scraper_feeds', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index()->default(0)->after('group_id');
        });
        Schema::table('scraper_wechat_mp_info', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index()->default(0)->after('group_id');
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
