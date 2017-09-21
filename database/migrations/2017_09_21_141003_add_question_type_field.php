<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer("question_type")->after('user_id')->default(1);
            $table->integer("is_hot")->after('price')->default(0);
            $table->integer("is_recommend")->after('price')->default(0);

        });

        Schema::table('answers', function (Blueprint $table) {
            $table->integer('views')->unsigned()->after('comments')->default(0);                 //查看数
        });

        DB::table('questions')->update(['views'=>0,'comments'=>0]);
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
