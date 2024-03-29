<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDoingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doings', function (Blueprint $table) {
            $table->integer("is_hide")->after('action')->default(0);
            $table->index('action');
            $table->index('user_id');
        });
        Schema::table('supports', function (Blueprint $table) {
            $table->dropColumn("session_id");
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
