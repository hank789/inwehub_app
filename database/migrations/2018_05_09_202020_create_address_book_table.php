<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_book', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('address_book_id')->comment('通讯录id');
            $table->string('display_name')->comment('记录的通讯录姓名');
            $table->string('phone',30)->index()->comment('手机号');
            $table->json('detail');
            $table->tinyInteger('status')->nullable()->default(1)->index()->comment('状态 0-已失效 1-有用');
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
        Schema::dropIfExists('address_book');
    }
}
