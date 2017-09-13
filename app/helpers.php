<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 15/10/27
 * Time: 下午7:11
 */

/*商品类型字段定义*/
if (! function_exists('trans_goods_post_type')) {

    function trans_goods_post_type($post_type){
        $map = [
            0 => '不需要',
            1 => '需要',
        ];

        if($post_type==='all'){
            return $map;
        }


        if(isset($map[$post_type])){
            return $map[$post_type];
        }

        return '';

    }

}

if (! function_exists('trans_gender_name')) {

    function trans_gender_name($post_type){
        $map = [
            0 => '保密',
            1 => '男',
            2 => '女',
        ];

        if($post_type==='all'){
            return $map;
        }


        if(isset($map[$post_type])){
            return $map[$post_type];
        }

        return '';

    }

}


/*行家认证状态文字定义*/
if (! function_exists('trans_authentication_status')) {

    function trans_authentication_status($status){
        $map = [
            0 => '待审核',
            1 => '审核通过',
            4 => '审核失败',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }

}

/*企业认证状态文字定义*/
if (! function_exists('trans_company_apply_status')) {

    function trans_company_apply_status($status){
        $map = [
            0 => '草稿',
            1 => '待审核',
            2 => '审核通过',
            3 => '审核失败',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

/*企业规模*/
if (! function_exists('trans_company_workers')) {

    function trans_company_workers($key){
        $map = [
            1 => '1-10人',
            2 => '10-20人',
            3 => '20-100人',
            4 => '100-1000人',
            5 => '1000人以上'
        ];

        if($key==='all'){
            return $map;
        }


        if(isset($map[$key])){
            return $map[$key];
        }

        return '';

    }
}

if (! function_exists('trans_company_auth_mode')) {

    function trans_company_auth_mode($mode){
        $map = [
            1 => '协议认证',
            2 => '打款认证',
        ];

        if($mode==='all'){
            return $map;
        }


        if(isset($map[$mode])){
            return $map[$mode];
        }

        return '';

    }
}

// 项目阶段
if (! function_exists('trans_project_stage')) {

    function trans_project_stage($mode){
        $map = [
            1 => '只有个想法',
            2 => '项目已立项',
            3 => '项目进行中'
        ];

        if($mode==='all'){
            return $map;
        }

        if(isset($map[$mode])){
            return $map[$mode];
        }

        return '';
    }
}


// 项目类型
if (! function_exists('trans_project_type')) {

    function trans_project_type($mode){
        $map = [
            1 => '一次性',
            2 => '持续',
        ];

        if($mode==='all'){
            return $map;
        }

        if(isset($map[$mode])){
            return $map[$mode];
        }

        return '';
    }
}

// 项目顾问数量
if (! function_exists('trans_project_worker_num')) {

    function trans_project_worker_num($mode){
        $map = [
            1 => '一个',
            2 => '2个',
            3 => '3~5个',
            4 => '5~8个',
            5 => '8个以上',
            6 => '其它',
            7 => '不确定'
        ];

        if($mode==='all'){
            return $map;
        }

        if(isset($map[$mode])){
            return $map[$mode];
        }

        return '';
    }
}

// 项目顾问级别
if (! function_exists('trans_project_worker_level')) {
    function trans_project_worker_level($mode){
        $map = [
            1 => '熟练',
            2 => '精通',
            3 => '资深'
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}

// 项目计费模式
if (! function_exists('trans_project_billing_mode')) {
    function trans_project_billing_mode($mode){
        $map = [
            1 => '按人计算',
            2 => '整体打包',
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}

// 项目计费模式
if (! function_exists('trans_project_billing_mode')) {
    function trans_project_billing_mode($mode){
        $map = [
            1 => '按人计算',
            2 => '整体打包',
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}

// 项目周期
if (! function_exists('trans_project_project_cycle')) {
    function trans_project_project_cycle($mode){
        $map = [
            1 => '小于1周',
            2 => '1~2周',
            3 => '2~4周',
            4 => '1~2月',
            5 => '2~4月',
            6 => '4~6月',
            7 => '半年以上',
            8 => '不确定',
            9 => '其它'
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}

// 项目工作密度
if (! function_exists('trans_project_work_intensity')) {
    function trans_project_work_intensity($mode){
        $map = [
            1 => '2H/W',
            2 => '4H/W',
            3 => '8H/W',
            4 => '16H/W',
            5 => '24H/W',
            6 => '32H/W',
            7 => '40H/W',
            8 => '其它',
            9 => '不确定'
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}

// 项目差旅费用
if (! function_exists('trans_project_travel_expense')) {
    function trans_project_travel_expense($mode){
        $map = [
            1 => '包含在项目内',
            2 => '单独结算',
        ];
        if($mode==='all'){
            return $map;
        }
        if(isset($map[$mode])){
            return $map[$mode];
        }
        return '';
    }
}



/*公告状态文字定义*/
if (! function_exists('trans_exchange_status')) {

    function trans_exchange_status($status){
        $map = [
            0 => '未处理',
            1 => '已处理',
            4 => '兑换失败',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }

}

/*公告状态文字定义*/
if (! function_exists('trans_common_status')) {

    function trans_common_status($status){
        $map = [
            0 => '待审核',
            1 => '已审核',
           -1 => '已禁止',
            2 => '已结束'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

if (! function_exists('trans_app_version_status')) {

    function trans_app_version_status($status){
        $map = [
            0 => 'IOS审核中',
            1 => '已审核',
            -1 => '已禁止'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

if (! function_exists('trans_rgcode_status')) {

    function trans_rgcode_status($status){
        $map = [
            0 => '待审核',
            1 => '已审核',
            2 => '已使用'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

if (! function_exists('trans_withdraw_status')) {

    function trans_withdraw_status($status){
        $map = [
            0 => '待处理',
            1 => '处理中',
            2 => '处理成功',
            3 => '处理失败',
            4 => '暂停处理'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }
}

if (!function_exists('trans_recommend_submission_status')){
    function trans_recommend_submission_status($status){
        $map = [
            0 => '未推荐',
            1 => '待审核',
            2 => '已推荐',
            3 => '已下线'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }
}

if (! function_exists('trans_coupon_status')) {

    function trans_coupon_status($status){
        $map = [
            1 => '待使用',
            2 => '已使用',
            3 => '已过期',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }
}

if (! function_exists('trans_push_notice_status')) {

    function trans_push_notice_status($status){
        $map = [
            0 => '待测试',
            1 => '已测试',
            2 => '已发送',
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }
}

if (! function_exists('trans_push_notice_notification_type')) {

    function trans_push_notice_notification_type($status){
        $map = [
            1 => '阅读发现',
            2 => '公告文章',
            3 => 'APP内页'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }
}

if (! function_exists('trans_article_collect_status')) {

    function trans_article_collect_status($status){
        $map = [
            1 => '待审核',
            2 => '审核通过',
            3 => '需要重新报名',
            4 => '已拒绝'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }
}

/*回答状态文字定义*/
if (! function_exists('trans_answer_status')) {

    function trans_answer_status($status){
        $map = [
            0 => '待审核',
            1 => '已审核已发布',
            2 => '拒绝回答',
            3 => '待回答',
            -1 => '已禁言'
        ];

        if($status==='all'){
            return $map;
        }


        if(isset($map[$status])){
            return $map[$status];
        }

        return '';

    }

}


/*问题状态文本描述定义*/
if (! function_exists('trans_question_status')) {

    function trans_question_status($status){
        $map = [
            0 => '待审核',
            1 => '待分配',
            2 => '已分配待确认',
            3 => '已关闭',
            4 => '已确认待回答',
            5 => '已拒绝待分配',
            6 => '已回答待点评',
            7 => '已点评'
        ];

        if($status==='all'){
            return $map;
        }

        if(isset($map[$status])){
            return $map[$status];
        }

        return '';
    }

}



/*数据库setting表操作*/
if (! function_exists('Setting')) {

    function Setting(){
        return app('App\Models\Setting');
    }

}


/*数据库Category表操作*/
if (! function_exists('load_categories')) {

    function load_categories( $type = 'all' , $root = false ){
        return app('App\Models\Category')->loadFromCache($type,$root);
    }

}


/*数据库area地区表操作*/
if (! function_exists('Area')) {

    function Area(){
        return app('App\Models\Area');
    }

}

if (! function_exists('get_province_name')) {

    function get_province_name($province_code){
        return \App\Services\City\CityData::getProvinceName($province_code);
    }

}

if (! function_exists('get_city_name')) {

    function get_city_name($province_code, $city_code){
        return \App\Services\City\CityData::getCityName($province_code, $city_code);
    }

}



/**
 * 将正整数转换为带+,例如 10 装换为 +10
 * 用户积分显示
 */
if( ! function_exists('integer_string')){
    function integer_string($value){
        if($value>=0){
            return '+'.$value;
        }

        return $value;
    }
}

if( ! function_exists('get_credit_message')){
    function get_credit_message($credits,$coins){
        $messages = [];
        if( $credits != 0 ){
            $messages[] = '成长值 '.integer_string($credits);
        }
        if( $coins != 0 ){
            $messages[] = '贡献值 '.integer_string($coins);
        }
        return implode("，",$messages);
    }
}





if(! function_exists('timestamp_format')){
    function timestamp_format($date_time){
        $timestamp = \Carbon\Carbon::instance(new DateTime($date_time));
        $time_format_string = Setting()->get('date_format').' '.Setting()->get('time_format');
        if(Setting()->get('time_friendly')==1){
            return $timestamp->diffInWeeks(\Carbon\Carbon::now()) >= 1 ? $timestamp->format($time_format_string) : $timestamp->diffForHumans();
        }
        return $timestamp->format($time_format_string);

    }
}


if( ! function_exists('parse_seo_template')){
    function parse_seo_template($type,$source){
        $seo_template = Setting()->get($type);
        $seo_template = str_replace("{wzmc}",Setting()->get('website_name'),$seo_template);
        $seo_template = str_replace("{wzkh}",Setting()->get('website_slogan'),$seo_template);

        if(str_contains($type,['question','article'])){
            if($source->tags){
                $tagList = array_pluck($source->tags->toArray(),'name');
                $seo_template = str_replace("{htlb}",implode(",",$tagList),$seo_template);
            }
        }

        if(str_contains($type,'question')) {
            $seo_template = str_replace("{wtbt}", strip_tags($source->title), $seo_template);
            $seo_template = str_replace("{wtms}", str_limit(strip_tags($source->description),200), $seo_template);
        }else if(str_contains($type,'article')){
            $seo_template = str_replace("{wzbt}",strip_tags($source->title),$seo_template);
            $seo_template = str_replace("{wzzy}",str_limit($source->summary,200),$seo_template);
        }else if(str_contains($type,'topic')){
            $seo_template = str_replace("{htmc}",$source->name,$seo_template);
            $seo_template = str_replace("{htjj}",str_limit($source->summary,200),$seo_template);
        }

        return $seo_template;
    }
}

/*生成头像图片地址*/
if(! function_exists('get_user_avatar')){
    function get_user_avatar($user_id,$size='middle',$extension='jpg'){
        return route('website.image.avatar',['avatar_name'=>$user_id.'_'.$size.'.'.$extension]);
    }
}


/*常见的正则判断*/

/*邮箱判断*/
if( !function_exists('is_email') ){
    function is_email($email){
        $reg = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
        if( preg_match($reg,$email) ){
            return true;
        }
        return false;
    }
}

/*手机号码判断*/
if( !function_exists('is_mobile') ){
    function is_mobile($mobile){
        $reg = "/^1[34578]\d{9}$/";
        if( !preg_match($reg,$mobile) ){
            return false;
        }
        return true;
    }
}

if (!function_exists('secret_mobile')) {
    function secret_mobile($mobile) {
        return substr($mobile, 0, 5).'****'.substr($mobile, 9);
    }
}

//生成验证码
if( !function_exists('makeVerifyCode') ){
    function makeVerifyCode(int $min = 1000, int $max = 9999)
    {
        //if(config('app.env') != 'production') return 6666;
        $min = min($min, $max);
        $max = max($min, $max);

        if (function_exists('mt_rand')) {
            return mt_rand($min, $max);
        }

        return rand($min, $max);
    }
}

//时间格式优化
if( !function_exists('promise_time_format') ){
    function promise_time_format(string $datetime)
    {
        $timestamp = new \Carbon\Carbon($datetime);
        $diff_minutes_origin = $timestamp->diffInMinutes(null,false);
        $diff_minutes = abs($diff_minutes_origin);
        $diff_hours = floor($diff_minutes/60);
        $diff_minutes -= $diff_hours*60;
        $diff = ($diff_hours?$diff_hours.'小时':'').($diff_minutes>0?$diff_minutes.'分钟':'');
        if($diff_minutes_origin > 0){
            //小于当前时间
            $desc = '已超时'.$diff;
        }elseif($diff_minutes == 0){
            $desc = '已到承诺时间';
        }else{
            $desc = '距您承诺时间还有'.$diff;
        }
        return ['desc'=>$desc,'diff'=>$diff];
    }
}

if (!function_exists('cal_account_info_finish')) {
    function cal_account_info_finish(array $data){
        $expert_fields = ['id','site_notifications','password','is_expert','tags','status','created_at','updated_at','remember_token','email_notifications'];
        $unfilled = [];
        $score = 0;
        $info = $data['info'];
        foreach($info as $field=>$item){
            if(in_array($field,$expert_fields)) continue;

            foreach($item as $key=>$value){
                if($field=='avatar_url' && $value==config('image.user_default_avatar')) {
                    $unfilled[] = $field;
                    continue;
                }
                if(!empty($value) || $value === "0") {
                    $score += $key;
                }else {
                    $unfilled[] = $field;
                }
            }
        }
        unset($data['info']);
        $career_extra_count = 0;
        foreach($data as $field=>$item){
            if(in_array($field,$expert_fields)) continue;
            foreach($item as $key=>$value){
                if(count($value)>=1) {
                    $score += $key;
                    if($field != 'trains'){
                        $career_extra_count += (count($value)-1);
                    }
                }
            }
        }
        if($career_extra_count >=4){
            $score += 4;
        }else{
            $score += $career_extra_count;
        }

        return ['unfilled'=>$unfilled, 'score'=>$score];
    }
}

if (!function_exists('gen_order_number')) {
    function gen_order_number($type='Order'){
        $time = date('YmdHis');
        /**
         * @var \Redis
         */
        $redis = Illuminate\Support\Facades\Redis::connection();
        $key = $type.$time;
        $count = $redis->incr($key);
        $redis->expire($key, 60);
        return $time.$count;
    }
}

if (!function_exists('get_pay_config')){
    function get_pay_config(){
        return [
            "withdraw_suspend"=> Setting()->get('withdraw_suspend',0),//是否暂停提现,0否,1暂停提现
            "pay_method_weixin"=> Setting()->get('pay_method_weixin',1),//是否开启微信支付,1开启
            "pay_method_ali"=> Setting()->get('pay_method_ali',0),//是否开启阿里支付,0未开启
            "pay_method_iap"=> Setting()->get('pay_method_iap',0),//是否开启iap支付,0未开启
            "withdraw_day_limit"=> Setting()->get('withdraw_day_limit',1),//用户每天最大提现次数
            "withdraw_per_min_money"=> Setting()->get('withdraw_per_min_money',10),//用户单次最低提现金额
            "withdraw_per_max_money"=> Setting()->get('withdraw_per_max_money',2000),//用户单次最高提现金额
            "pay_settlement_cycle"=> Setting()->get('pay_settlement_cycle',5),//支付结算周期
        ];
    }
}
if (!function_exists('get_app_object_url')) {
    function get_app_object_url($object_type,$object_id){
        $url = config('app.mobile_url');
        switch($object_type){
            case 'question':
                $url .= '#/ask/'.$object_id;
                break;
            case 'answer':
                $url .= '#/answer/'.$object_id;
                break;
        }
        return $url;
    }
}

if (!function_exists('get_wechat_notice_template_id')){
    function get_wechat_notice_template_id($object_type){
        $template_id = '';
        switch($object_type){
            case 'question':
                $template_id = config('wechat.notice_template.question');
                break;
            case 'answer':
                $template_id = config('wechat.notice_template.answer');
                break;
        }
        return $template_id;
    }
}

if (!function_exists('gen_user_uuid')){
    function gen_user_uuid(){
        $uuid1 = \Ramsey\Uuid\Uuid::uuid1();
        return $uuid1->getHex();
    }
}

if (!function_exists('get_user_avatar_url_by_id')){
    function get_user_avatar_url_by_id($uid){
        $user = \App\Models\User::find($uid);
        return $user->getAvatarUrl();
    }
}


if (!function_exists('format_json_string')){
    function format_json_string($json,$field=''){
        $arr = json_decode($json,true);
        if($arr) {
            if($field){
                return implode(',',array_column($arr,$field));
            } else {
                return implode(',',array_values($arr));
            }
        }
        return '';
    }
}