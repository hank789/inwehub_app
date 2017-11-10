<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserInviteCodeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer("rc_uid")->after('uuid')->nullable();
            $table->string("rc_code",8)->after('uuid')->unique()->nullable();
        });
        Schema::table('user_money', function (Blueprint $table) {
            $table->decimal("reward_money")->after('settlement_money')->default(0)->comment('分红收入');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rc_uid']);
            $table->dropColumn(['rc_code']);
        });

        Schema::table('user_money', function (Blueprint $table) {
            $table->dropColumn(['reward_money']);
        });
    }
}
