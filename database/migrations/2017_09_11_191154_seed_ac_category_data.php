<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAcCategoryData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*插入默认分类*/
        DB::table('categories')->insert([
            //问题分类
            ['id'=>30,'name' => '活动报名','slug'=>'activity_enroll','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'tags,articles','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>31,'name' => '项目机遇','slug'=>'project_enroll','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'tags,articles','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);

        Schema::table('articles', function (Blueprint $table) {
            $table->string('deadline','20')->nullable()->after('device');
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('subject');
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->integer('status')->default(1)->after('source_type');
            $table->string('subject',1024);
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
