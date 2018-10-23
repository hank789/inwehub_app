<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('title',6000);
            $table->string('type');
            $table->json('data');
            $table->string('category_name')->index();
            $table->string('rate',12)->index()->default(0);

            // Used for resubmit feature.
            $table->integer('resubmit_id')->unsigned()->index()->nullable();

            $table->integer('user_id')->unsigned()->index();
            $table->integer('category_id')->unsigned();

            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->integer('comments_number')->default(0);
            $table->integer('collections')->unsigned()->default(0);              //支持数

            // approved by moderators so it can't be reported
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned()->index()->default(0)->after('to_user_id');
            $table->integer('level')->default(0)->after('to_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('is_expert')->unsigned()->default(0)->after('uuid');
            $table->integer('submission_karma')->default(0)->after('uuid'); // used for backup (in case redis get's flushed)
            $table->integer('comment_karma')->default(0)->after('uuid'); // used for backup (in case redis get's flushed)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('submissions');
    }
}
