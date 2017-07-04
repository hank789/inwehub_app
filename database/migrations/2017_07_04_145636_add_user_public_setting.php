<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserPublicSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("uuid",64)->after('id')->unique()->nullable();
        });
        Schema::table('user_data', function (Blueprint $table) {
            $table->tinyInteger("job_public")->after('is_company')->default(0);
            $table->tinyInteger("project_public")->after('is_company')->default(0);
            $table->tinyInteger("edu_public")->after('is_company')->default(0);
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
