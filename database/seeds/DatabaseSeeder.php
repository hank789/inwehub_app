<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * 数据库初始化
     *
     * @return void
     */
    public function run()
    {

        /*添加默认权限组*/
        DB::table('roles')->insert([
            ['id' => '1','name' => '后台管理员','slug' => 'admin','description' => '后台管理员，具有最高权限','level'=>1,'created_at'=>'2016-02-16 09:52:13','updated_at'=>'2016-02-16 09:52:13'],
            ['id' => '2','name' => '普通会员','slug' => 'member','description' => '普通会员，不可管理后台','level'=>1,'created_at'=>'2016-02-16 09:52:13','updated_at'=>'2016-02-16 09:52:13'],
        ]);



        DB::table('permissions')->insert([
            ['id' => '1','name' => '后台管理首页','slug' => 'admin.index.index','description' => '后台管理首页','created_at'=>'2016-02-16 17:57:51','updated_at'=>'2016-02-16 17:57:51'],
        ]);

        DB::table('permission_role')->insert([
            ['id' => '1','permission_id' => '1','role_id' => '1','created_at'=>'2016-02-16 17:37:51','updated_at'=>'2016-04-16 17:57:51'],
        ]);


        /*友情连接*/
        DB::table('friendship_links')->insert([
            ['id' => '1','name' => 'laravel中文网','slogan' => '国内最好PHP开源框架','url' => 'http://www.golaravel.com','sort' => '1','status' => '1','created_at' => '2016-05-10 18:25:54','updated_at' => '2016-05-10 18:28:05'],
        ]);


        /*系统默认配置*/
        DB::table('settings')->insert([
            ['name' => 'credits_expert_valid','value' => '200'],//专家认证
            ['name' => 'credits_invite_user','value' => '200'],//邀请好友
            ['name' => 'credits_ask_good','value' => '0'],//优质提问
            ['name' => 'credits_answer_good','value' => '0'],//优质回答
            ['name' => 'credits_answer_over_promise_time_max','value' => '-200'],//超出承诺时间回答每小时最多扣
            ['name' => 'credits_answer_over_promise_time_hourly','value' => '-20'],//超出承诺时间回答每小时扣
            ['name' => 'credits_answer','value' => '200'],//每次回答
            ['name' => 'credits_first_answer','value' => '500'],//首次提问
            ['name' => 'credits_ask','value' => '200'],//每次提问
            ['name' => 'credits_first_ask','value' => '500'],//首次提问
            ['name' => 'credits_login','value' => '0'],
            ['name' => 'credits_user_sign_daily','value' => '10'],//每日签到
            ['name' => 'credits_user_info_complete','value' => '500'],//简历完成
            ['name' => 'credits_upload_avatar','value' => '50'],//头像上传成功
            ['name' => 'credits_register','value' => '100'],//注册成功

            ['name' => 'coins_expert_valid','value' => '500'],//专家认证
            ['name' => 'coins_invite_user','value' => '100'],//邀请好友
            ['name' => 'coins_ask_good','value' => '100'],//优质提问
            ['name' => 'coins_answer_good','value' => '100'],//优质回答
            ['name' => 'coins_answer_over_promise_time_max','value' => '0'],//超出承诺时间回答每小时最多扣
            ['name' => 'coins_answer_over_promise_time_hourly','value' => '0'],//超出承诺时间回答每小时扣
            ['name' => 'coins_answer','value' => '200'],//每次回答
            ['name' => 'coins_first_answer','value' => '200'],//首次提问
            ['name' => 'coins_ask','value' => '200'],//每次提问
            ['name' => 'coins_first_ask','value' => '0'],//首次提问
            ['name' => 'coins_login','value' => '0'],
            ['name' => 'coins_user_sign_daily','value' => '0'],//每日签到
            ['name' => 'coins_user_info_complete','value' => '0'],//简历完成
            ['name' => 'coins_upload_avatar','value' => '0'],//头像上传成功
            ['name' => 'coins_register','value' => '0'],//注册成功

            ['name' => 'date_format','value' => 'Y-m-d'],
            ['name' => 'time_diff','value' => '0'],
            ['name' => 'time_format','value' => 'H:i'],
            ['name' => 'time_friendly','value' => '1'],
            ['name' => 'time_offset','value' => '8'],
            ['name' => 'website_admin_email','value' => 'hank.wang@intervapp.com'],
            ['name' => 'website_footer','value' => ''],
            ['name' => 'website_header','value' => ''],
            ['name' => 'website_icp','value' => ''],
            ['name' => 'website_cache_time','value' => '1'],
            ['name' => 'website_name','value' => '英淘官网'],
            ['name' => 'website_url','value' => ''],
            ['name' => 'register_title','value' => '欢迎加入英淘社区'],
            ['name' => 'website_slogan','value' => '做最好的咨询社区'],
            ['name' => 'website_welcome','value' => '欢迎加入英淘社区，一起成长'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'question_invite_unanswer_alert_minute', 'value' => '10'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],
            ['name' => 'is_inviter_must_expert', 'value' => '1'],

        ]);


        $registrar = new \App\Services\Registrar();
        $admin = $registrar->create([
            'name' => 'intervapp',
            'email' => 'hank.wang@inwehub.com',
            'mobile' => '15050368286',
            'password' => 'qwer1234',
            'status' => 1,
            'visit_ip' => '127.0.0.1',
        ]);
        $admin->attachRole(1);

        $admin2 = $registrar->create([
            'name' => 'laoguo',
            'email' => 'hongwei.guo@inwehub.com',
            'mobile' => '15801776680',
            'password' => 'qwer1234',
            'status' => 1,
            'visit_ip' => '127.0.0.1',
        ]);
        $admin2->attachRole(1);

        $admin3 = $registrar->create([
            'name' => 'cicely',
            'email' => 'cicely.cheng@inwehub.com',
            'mobile' => '13601874269',
            'password' => 'qwer1234',
            'status' => 1,
            'visit_ip' => '127.0.0.1',
        ]);
        $admin3->attachRole(1);



    }
}
