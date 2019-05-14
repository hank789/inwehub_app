<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_collection', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('content_type')->unsigned()->index()->default(0);
            $table->integer('sort')->unsigned()->index()->default(0);
            $table->integer('source_id')->unsigned()->index()->default(0);
            $table->json('content');
            $table->tinyInteger('status')->unsigned()->index()->default(1);
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
        Schema::dropIfExists('content_collection');
    }
}
