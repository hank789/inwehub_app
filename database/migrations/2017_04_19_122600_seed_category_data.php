<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedCategoryData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_name_unique');
            $table->unique(['name','category_id']);

        });
        /*插入默认分类*/
        DB::table('categories')->insert([
            //问题分类
            ['id'=>2,'name' => '问题分类','slug'=>'question','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>3,'name' => '系统功能','slug'=>'question_system_func','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>4,'name' => '日常操作','slug'=>'question_daily_operation','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>5,'name' => '业务知识','slug'=>'question_business_knowledge','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>6,'name' => '功能开发','slug'=>'question_function_dev','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>7,'name' => '其他','slug'=>'question_other','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            //拒绝回答
            ['id'=>8,'name' => '拒绝回答','slug'=>'answer_reject','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answer,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            //行业
            ['id'=>9,'name' => ' 行业','slug'=>'industry','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);
        DB::table('tags')->insert([
            //问题分类tag
            ['name' => 'SD','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'PP','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'MM','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'FI','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'CO','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'SRM','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'CRM','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'EWM','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'BI','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'BW','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'HR','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'MES','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'APO','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'Fiori','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'HANA','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'3','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            //拒绝回答分类
            ['name' => '价格太低','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '行业无关','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '提问不精确','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '时间太忙','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '无法回答','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'8','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            //行业分类tag
            ['name' => '零售行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '消费品行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '批发分销行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '机械制造行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '高科技行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '汽车行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '航空与国防','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '公共部门','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '化工行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '石油天然气行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '采矿业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '公用事业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '轧制品行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '电信行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '金融与银行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '保险行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '工程建筑与运营','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '旅游与运输行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '国防与安全','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '医疗卫生行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '生命科学行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '媒体行业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '专业服务','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '高等教育与研究','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '体育与休闲娱乐业','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '互联网及信息技术','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'9','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);

        Schema::table('doings', function (Blueprint $table) {
            $table->string('action',64)->change();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dateTime('promise_time')->nullable()->after('adopted_at')->index()->comment('承诺响应时间');
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
