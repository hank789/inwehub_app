<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLongLatField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('login_records', function (Blueprint $table) {
            $table->string('longitude')->nullable()->default('')->after('address')->comment('经度');
            $table->string('latitude')->nullable()->default('')->after('address')->comment('纬度');
            $table->string('address_detail')->nullable()->default('')->after('address')->comment('登录设备详细地理位置');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->json('data')->after('title');
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
