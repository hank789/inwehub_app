<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecommendQa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operate_recommend_qa', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');                   //主题
            $table->string('user_name');                       //名字
            $table->string('user_avatar_url');                      //头像地址
            $table->integer('price'); //金额
            $table->tinyInteger('type')->default(1);   //类型,1提问,2回答
            $table->tinyInteger('sort');                 //排序字段 ASC
            $table->tinyInteger('status')->default(1);   //状态
            $table->timestamps();
        });
        DB::table('operate_recommend_qa')->insert([
            ['id'=>1,'subject' => '在启用物料帐后，如果月初已经发生了物料移动，还有什么办法可以发布标准成本吗？以及如果有，会有什么后遗症或注意事项吗？','user_name'=>'张峰','user_avatar_url' =>'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg','price'=>'188','type' =>'1','sort'=>'1','status'=>'1','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>2,'subject' => '有联产品的生产订单结算和一般生产订单结算在配置、主数据、前台操作上有哪些需要特别注意的地方？','user_name'=>'匿名','user_avatar_url' =>'https://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/18/user_origin_9.jpg','price'=>'100','type' =>'1','sort'=>'2','status'=>'1','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('operate_recommend_qa');
    }
}
