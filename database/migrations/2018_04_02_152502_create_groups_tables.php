<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('name')->unique()->index();
            $table->text('description');
            $table->string('logo');
            $table->boolean('public')->default(1);
            $table->integer('audit_status')->default(0)->comment('审核状态:0待审核，1审核通过，2审核不通过');
            $table->integer('subscribers')->default(1)->comment('订阅人数');
            $table->integer('articles')->default(0)->comment('贴子数');
            $table->string('failed_reason')->default('');;
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('group_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('group_id')->unsigned()->index();
            $table->tinyInteger('audit_status')->unsigned()->default(1)->comment('审核状态，0待审核，1审核通过，2审核不通过');
            $table->timestamps();
            $table->unique(['user_id', 'group_id']);
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer("group_id")->after('user_id')->unsigned()->index()->default(0);
            $table->integer('views')->after('user_id')->unsigned()->default(0);
            $table->boolean('public')->after('user_id')->index()->default(1);
            $table->integer("is_recommend")->after('user_id')->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_members');
    }
}
