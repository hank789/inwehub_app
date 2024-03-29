<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOauthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_oauth', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->char("auth_type",64)->index();
            $table->char("nickname",64);
            $table->char("avatar",255);
            $table->integer('user_id')->index()->default(0);              //用户UID
            $table->string("openid",128)->index();
            $table->string("unionid",128)->index()->nullable();
            $table->string("access_token",64);
            $table->string("refresh_token",64)->nullable();
            $table->string("scope",64)->nullable();
            $table->string("full_info",2048)->nullable();
            $table->integer("expires_in");
            $table->tinyInteger('status')->default(1)->comment('状态:0未生效,1已生效');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_oauth');
    }
}
