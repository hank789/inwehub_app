<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductUserRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_user_rel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->default(0);
            $table->integer('tag_id')->unsigned()->index()->default(0);
            $table->tinyInteger('status')->index()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_user_rel');
    }
}
