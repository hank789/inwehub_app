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

        Schema::table('user_data', function (Blueprint $table) {
            $table->integer("user_level")->after('user_id')->default(1);;
        });

        DB::table('roles')->insert([
            ['id' => '3','name' => '客服','slug' => 'customerservice','description' => '客服人员','level'=>1,'created_at'=>'2016-02-16 09:52:13','updated_at'=>'2016-02-16 09:52:13'],
        ]);
        DB::table('permissions')->insert([
            ['id' => '2','name' => '活动报名回复','slug' => 'activity.enroll.comment','description' => '对用户活动报名进行回复','created_at'=>'2016-02-16 17:57:51','updated_at'=>'2016-02-16 17:57:51'],
        ]);

        DB::table('permission_role')->insert([
            ['id' => '2','permission_id' => '2','role_id' => '3','created_at'=>'2016-02-16 17:37:51','updated_at'=>'2016-04-16 17:57:51'],
        ]);
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
