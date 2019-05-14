<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerOauthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_oauth', function (Blueprint $table) {
            $table->increments('id');
            $table->string('app_id',32);
            $table->string('app_secret');
            $table->integer('product_id')->unsigned();
            $table->string('description');
            $table->string('api_url');
            $table->integer('status')->unsigned()->default(1);
            $table->timestamps();
            $table->unique('app_id','partner_oauth_app_id');
            $table->unique('product_id','partner_oauth_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_oauth');
    }
}
