<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoPublishField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scraper_feeds', function (Blueprint $table) {
            $table->integer('is_auto_publish')->default(0)->after('user_id');
        });
        Schema::table('scraper_wechat_mp_info', function (Blueprint $table) {
            $table->integer('is_auto_publish')->default(0)->after('user_id');
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
