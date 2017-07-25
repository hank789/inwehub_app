<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyAppVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_version', function (Blueprint $table) {
            $table->renameColumn('is_force','is_ios_force');
            $table->tinyInteger('is_android_force')->default(0)->after('is_ios_force')->comment('是否android强更:0非强更,1强更');

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
