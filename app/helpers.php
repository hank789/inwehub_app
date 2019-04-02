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
            2 => '未通过',
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

if (! function_exists('trans_group_status')) {

    function trans_group_status($status){
        $map = [
            0 => '待审核',
            1 => '审核通过',
            2 => '未通过',
            3 => '系统圈子',
            4 => '已关闭',
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
            9 => '长期或入职'
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
            2 => '已结束',
            3 => '待抓取'
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
if (! function_exists('trans_article_status')) {

    function trans_article_status($status){
        $map = [
            1 => '待发布',
            2 => '已发布',
            3 => '已删除',
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


/*任务状态文字定义*/
if (! function_exists('trans_task_status')) {

    function trans_task_status($status){
        $map = [
            0 => '处理中',
            1 => '处理成功',
            2 => '已关闭'
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
            2 => '已分配待回答',
            3 => '已关闭',
            4 => '已确认待回答',
            5 => '已拒绝待分配',
            6 => '已回答待采纳',
            7 => '已点评',
            8 => '已采纳',
            9 => '已退款'
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

    function load_categories( $type = 'all' , $root = false , $last = false){
        return app('App\Models\Category')->loadFromCache($type,$root, $last);
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
        return $timestamp->diffInYears(\Carbon\Carbon::now()) >= 1 ? $timestamp->format($time_format_string) : $timestamp->diffForHumans();
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
        if(config('app.env') != 'production') return 6666;
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

if (!function_exists('judge_user_activity_level')){
    function judge_user_activity_level($level,$activity){
        switch ($activity){
            case 'activity_enroll':
                if ($level >= 2) {
                    return true;
                }
                break;
        }
        return false;
    }
}

if (!function_exists('feed')){
    function feed(){
        return new \App\Services\FeedLogger();
    }
}


if (!function_exists('string')){
    /**
     * @param string $string
     *
     * @return \App\Services\String\Str
     */
    function string($string = '')
    {
        return new \App\Services\String\Str($string);
    }
}

if (!function_exists('saveImgToCdn')){
    function saveImgToCdn($imgUrl,$dir = 'avatar', $isIco = false, $queue = true){
        $parse_url = parse_url($imgUrl);
        if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
            $imgType = 'png';
            if (strrchr($parse_url['path'],'.svg') == '.svg') {
                $imgType = 'svg';
            }elseif (strrchr($parse_url['path'],'.gif') == '.gif') {
                $imgType = 'gif';
            }elseif ($isIco || strrchr($parse_url['path'],'.ico') == '.ico') {
                $imgType = 'ico';
            }
            $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$imgType;
            $ql = \QL\QueryList::getInstance();
            $gfw_urls = \App\Services\RateLimiter::instance()->sMembers('gfw_urls');
            if ($parse_url['host'] == 'mmbiz.qpic.cn' || $parse_url['host'] == 'mmbiz.qlogo.cn') {
                $otherArgs = [
                    'headers' => [
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Host' => $parse_url['host'],
                        'cache-control' => 'no-cache',
                        'pragma' => 'no-cache',
                        'Upgrade-Insecure-Requests' => 1,
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'
                    ]
                ];
            } else {
                $otherArgs = [
                    'headers' => [
                        'Referer' => $parse_url['host'],
                        'Host' => $parse_url['host'],
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'
                    ]
                ];
            }
            try {
                if (in_array($parse_url['host'],[
                        'lh4.googleusercontent.com',
                        'lh3.googleusercontent.com'
                    ]) || str_contains($parse_url['host'],'googleusercontent.com') || str_contains($parse_url['host'],'medium.com') || in_array($parse_url['host'],$gfw_urls)) {
                    //判断是否需要翻墙
                    $otherArgs['proxy'] = 'socks5h://127.0.0.1:1080';
                }
                $content = $ql->get($imgUrl,null,$otherArgs)->getHtml();
                if ($queue) {
                    dispatch((new \App\Jobs\UploadFile($file_name,base64_encode($content))));
                } else {
                    Storage::disk('oss')->put($file_name,$content);
                }
                return Storage::url($file_name);
            } catch (Exception $e) {
                app('sentry')->captureException($e);
                return 'https://cdn.inwehub.com/system/group_18@3x.png';
            }

        }
        return $imgUrl;
    }
}


if (!function_exists('getUrlInfo')) {
    function getUrlInfo($url, $withImageUrl = false, $dir = 'submissions', $queue = true) {
        $img_url = Cache::get('url_img_'.$url,'');
        $title = Cache::get('url_title_'.$url, '');
        if ($title && $img_url) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        if ($title && !$withImageUrl) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        if ($img_url && $withImageUrl) {
            return ['title'=>$title,'img_url'=>$img_url];
        }
        $isIco = false;
        try {
            $temp = '';
            $useCache = false;
            $urlArr = parse_url($url);
            if (in_array($urlArr['host'],['web.ywhub.com','m.inwehub.com'])) {
                $params = explode('/', $urlArr['fragment']);
                if (isset($params[1]) && $params[1] == 'c') {
                    $slug = explode('?', $params[3]);
                    $submission = \App\Models\Submission::where('slug', $slug[0])->first();
                    $img = $submission->data['img'] ?? '';
                    if (is_array($img)) {
                        if ($img) {
                            $img = $img[0];
                        } else {
                            $img = '';
                        }
                    }
                    $title = strip_tags($submission->data['title'] ?? $submission->title);
                    $img_url = $img;
                } elseif (isset($params[1]) && $params[1] == 'ask') {
                    if ($params[3] == 'answers') {
                        $slug = explode('?', $params[4]);
                        $question = \App\Models\Question::find($slug[0]);
                    } else {
                        $slug = explode('?', $params[3]);
                        $answer = \App\Models\Answer::find($slug[0]);
                        $question = \App\Models\Question::find($answer->question_id);
                    }
                    $title = strip_tags($question->title);
                    $img_url = '';
                } elseif (isset($params[1]) && $params[1] == 'dianping') {
                    $slug = explode('?', $params[3]);
                    if ($params[2] == 'product') {
                        $tag = \App\Models\Tag::getTagByName(urldecode($slug[0]));
                        $title = $tag->name;
                        $img_url = $tag->logo;
                    } else {
                        $submission = \App\Models\Submission::where('slug', $slug[0])->first();
                        $tag = \App\Models\Tag::find($submission->category_id);
                        $title = strip_tags($submission->data['title'] ?? $submission->title);
                        $img_url = $tag->logo;
                    }
                }
                Cache::put('url_title_' . $url, $title, 60 * 24 * 7);
                Cache::put('url_img_' . $url, $img_url, 60 * 24 * 7);
                return ['title' => $title, 'img_url' => $img_url];
            } elseif ($urlArr['host']=='mp.weixin.qq.com') {
                $f = file_get_contents_curl($url);
                //微信的文章
                $pattern = '/var msg_cdn_url = "(.*?)";/s';
                preg_match_all($pattern,$f,$matches);
                if(array_key_exists(1, $matches) && !empty($matches[1][0])) {
                    $temp = $matches[1][0];
                    //将tp=webp为tp=jpg
                    $temp = str_replace('tp=webp','tp=jpg',$temp);
                }
                preg_match('/<h2 class="rich_media_title" id="activity-name">(?<h2>.*?)<\/h2>/si', $f, $title);
                if (isset($title['h2'])) {
                    $title = $title['h2'];
                } elseif (str_contains($f,'该公众号已迁移至新的帐号，原帐号已回收。')) {
                    //该微信文章已转移
                } else {
                    //该微信文章或已删除
                    $img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                }
            } else {
                $ql = \QL\QueryList::getInstance();
                $gfw_urls = \App\Services\RateLimiter::instance()->sMembers('gfw_urls');
                if (in_array($urlArr['host'],[
                    'www.bilibili.com'
                ])) {
                    $ql->use(\QL\Ext\PhantomJs::class,config('services.phantomjs.path'));
                    $ql->browser($url);

                } elseif (in_array($urlArr['host'],$gfw_urls) && config('app.env') == 'production') {
                    $html = curlShadowsocks($url);
                    $ql->setHtml($html);
                }
                else {
                    $ql->get($url,null,['timeout'=>15]);
                }
                $image = $ql->find('meta[property=og:image]')->content;
                if (!$image) {
                    $image = $ql->find('meta[name=image]')->content;
                }
                if ($urlArr['host'] == 'm.jiemian.com') {
                    $image = $ql->find('div.wechat_logo>img')->src;
                }
                if (!$image) {
                    $image = $ql->find('meta[itemprop=image]')->content;
                    if (!$image && false) {
                        $image = $ql->find('link[rel=icon]')->href;
                        if (!$image) {
                            $image = $ql->find('link[rel=shortcut icon]')->href;
                            if (!$image) {
                                $image = $ql->find('link[href*=.ico]')->href;
                                if (!$image) {
                                    if ($urlArr['host'] == 'www.iyiou.com') {
                                        $image = $ql->find('img.aligncenter')->src;
                                    } else {
                                        $image = $urlArr['scheme'].'://'.$urlArr['host'].'/favicon.ico';
                                        $isIco = true;
                                    }
                                } else {
                                    $isIco = true;
                                }
                            } else {
                                $isIco = true;
                            }
                        } else {
                            $isIco = true;
                        }
                    }
                }
                if (!$image) {
                    //$img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                    //event(new \App\Events\Frontend\System\ExceptionNotify('未取到网站:'.$url.'的图片'));
                }
                $title = $ql->find('title')->eq(0)->text();
                if (str_contains($image,'.ico')) {
                    $useCache = true;
                    $img_url = Cache::get('domain_url_img_'.domain($url),'');
                }

                if (stripos($image,'//') === 0) {
                    $temp = 'http:'.$image;
                } elseif ($image && stripos($image,'http') !== 0) {
                    $temp = $urlArr['scheme'].'://'.$urlArr['host'].$image;
                } else {
                    $temp = $image;
                }
            }
            $encode = mb_detect_encoding($title); //得到字符串编码
            $file_charset = iconv_get_encoding()['internal_encoding']; //当前文件编码
            $title = trim($title);
            if ( $encode != 'CP936' && $encode && $encode != $file_charset) {
                $title = iconv($encode, $file_charset, $title);
            }
            if (str_contains($url,'3g.163.com')) {
                $title = trim($title,'_&#x624B;&#x673A;&#x7F51;&#x6613;&#x7F51;');
            }
            $title = htmlspecialchars_decode($title);
            Cache::put('url_title_'.$url,$title,60 * 24 * 7);
            if ($temp && $withImageUrl && !$img_url) {
                try {
                    //保存图片
                    $img_url = saveImgToCdn($temp,$dir,$isIco,$queue);
                    //非微信文章
                    if ($useCache) {
                        Cache::put('domain_url_img_'.domain($url),$img_url,60 * 24 * 30);
                    }
                    Cache::put('url_img_'.$url,$img_url,60 * 24 * 7);
                } catch (Exception $e) {
                    $img_url = 'https://cdn.inwehub.com/system/group_18@3x.png';
                }
            }
            return ['title'=>$title,'img_url'=>$img_url];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            if (isset($urlArr)) {
                \App\Services\RateLimiter::instance()->sAdd('gfw_urls',$urlArr['host'],0);
            }
        } catch (Exception $e) {
            app('sentry')->captureException($e,['url'=>$url]);
            if (empty($img_url) && $urlArr['host'] =='www.linkedin.com') {
                $img_url = 'https://cdn.inwehub.com/system/favicon_linkedin.ico';
            }
            return ['title'=>$title,'img_url'=>$img_url];
        }
    }
}

if (!function_exists('domain')) {
    /**
     * Squeezes the domain address from a valid URL.
     *
     * @param string $url
     *
     * @return string
     */
    function domain($url)
    {
        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
    }
}

if (!function_exists('firstRate')) {
    /**
     * Calculates the rate for votable model (currently used for submissions and comments).
     *
     * @return float
     */
    function firstRate()
    {
        return date('Ymd').'0';
    }
}

if (!function_exists('getRequestIpAddress')) {
    /**
     * Returns the real IP address of the request even if the website is using Cloudflare.
     *
     * @return string
     */
    function getRequestIpAddress()
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (!function_exists('file_get_contents_curl')) {
    function file_get_contents_curl($url, $checkTitle = true)
    {
        $ch = curl_init();
        $headers = [];
        $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);
        if ($checkTitle) {
            preg_match('/<title>(?<title>.*?)<\/title>/si', $data, $title);
            if (empty($title)) {
                $ql = \QL\QueryList::getInstance();
                $ql->use(\QL\Ext\PhantomJs::class,config('services.phantomjs.path'));
                $data = $ql->browser($url)->getHtml();
            }
        }
        return $data;
    }
}

if (!function_exists('rateSubmission')) {
    /**
     * Calculates the rate for sorting by hot.
     *
     * @param int       $upvotes
     * @param int       $downvotes
     * @param timestamp $created
     *
     * @return float
     */
    function rateSubmission($upvotes, $downvotes, $created)
    {
        $startTime = 1473696439; // strtotime('2016-09-12 16:07:19')
        $created = strtotime($created);
        $timeDiff = $created - $startTime;

        $x = $upvotes - $downvotes;

        if ($x > 0) {
            $y = 1;
        } elseif ($x == 0) {
            $y = 0;
        } else {
            $y = -1;
        }

        if (abs($x) >= 1) {
            $z = abs($x);
        } else {
            $z = 1;
        }

        return (log10($z) * $y) + ($timeDiff / 45000);
    }
}

if (!function_exists('hotRate')) {
    /**
     * http://www.ruanyifeng.com/blog/2012/03/ranking_algorithm_stack_overflow.html
     * https://www.biaodianfu.com/stackoverflow-ranking-algorithm.html
     * http://meta.stackoverflow.com/questions/11602/what-formula-should-be-used-to-determine-hot-questions
     * @param $Qviews
     * @param $Qanswers
     * @param $Qscore
     * @param $Ascores
     * @param $date_ask
     * @param $date_active
     * @return float|int
     */
    function hotRate($Qviews, $Qanswers, $Qscore, $Ascores, $date_ask, $date_active)
    {
        $Qage = (time() - strtotime(gmdate("Y-m-d H:i:s",strtotime($date_ask)))) / 3600;
        $Qage = round($Qage, 1);

        $Qupdated = (time() - strtotime(gmdate("Y-m-d H:i:s",strtotime($date_active)))) / 3600;
        $Qupdated = round($Qupdated, 1);
        if ($Qanswers<=0 && $Qscore!=0) {
            $Qanswers = 1;
        }
        if ($Qanswers!=0 && $Qscore==0) {
            $Qscore = 1;
        }
        $dividend = (log10($Qviews)*4) + (($Qanswers * $Qscore)/2) + $Ascores;
        $divisor = pow((($Qage + 1) - ($Qage - $Qupdated)/2), 1.5);
        return bcdiv($dividend,$divisor,10);
    }
}

if (!function_exists('getDistanceByLatLng')) {
    function getDistanceByLatLng($lng1,$lat1,$lng2,$lat2){//根据经纬度计算距离 单位为米
        //将角度转为狐度
        $radLat1=deg2rad($lat1);
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;//两纬度之差,纬度<90
        $b=$radLng1-$radLng2;//两经度之差纬度<180
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        return $s;
    }
}

if (!function_exists('distanceFormat')) {
    function distanceFormat($distance) {
        if (floatval($distance) <= 0) {
            return '0.1m';
        }
        if ($distance < 1000) {
            return $distance.'m';
        } else {
            return ($distance/1000).'km';
        }
    }
}

if (!function_exists('salaryFormat')) {
    function salaryFormat($salary,$format='k') {
        if (floatval($salary) < 1000) {
            return $salary;
        }
        return ($salary/1000).$format;
    }
}

if (!function_exists('formatCdnUrl')) {
    function formatCdnUrl($url) {
        if (config('app.env') == 'production') {
            $cdn_url = str_replace('http://inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','https://cdn.inwehub.com',$url);
            $format_url = parse_url($cdn_url);
            if (isset($format_url['host']) && !in_array($format_url['host'],['cdn.inwehub.com'])) {
                return false;
            }
            return $cdn_url;
        } else {
            return $url;
        }
    }
}

if (!function_exists('formatSlackUser')) {
    function formatSlackUser($user){
        return $user->id.'['.$user->name.']';
    }
}

//获取每日签到奖励
if (!function_exists('getDailySignInfo')) {
    function getDailySignInfo($day) {
        switch ($day) {
            case 1:
                return ['credits'=>5,'coins'=>0,'coupon_type'=>0];
                break;
            case 2:
                return ['credits'=>10,'coins'=>0,'coupon_type'=>0];
                break;
            case 3:
                return ['credits'=>15,'coins'=>0,'coupon_type'=>0];
                break;
            case 4:
                return ['credits'=>20,'coins'=>0,'coupon_type'=>0];
                break;
            case 5:
                return ['credits'=>25,'coins'=>0,'coupon_type'=>0];
                break;
            case 6:
                return ['credits'=>30,'coins'=>0,'coupon_type'=>0];
                break;
            case 7:
                return ['credits'=>35,'coins'=>0,'coupon_type'=>3];
                break;
            default:
                return ['credits'=>0,'coins'=>0,'coupon_type'=>0];
                break;
        }
    }
}

if (!function_exists('getSystemUids')) {
    function getSystemUids() {
        if (config('app.env') == 'production') {
            return [
                1,//inwehub
                3,//cicely
                4,//武浩
                5,//hank
                6,//庞凡
                504,//智能小哈
                79,
                504,
                229,//何棠
                131,//张震
            ];
        } else {
            return [0];
        }

    }
}

if (!function_exists('getContentUrls')) {
    function getContentUrls($content){
        preg_match_all('/(http|https):[\/]{2}[A-Za-z0-9,:\\._\\?#%&+\\-=\/()]*/',strip_tags(strip_html_tags(['a'],$content,true)),$urls);
        return $urls[0];
    }
}

if (!function_exists('formatContentUrls')) {
    function formatContentUrls($content){
        $urls = getContentUrls($content);
        if ($urls) {
            foreach ($urls as $url) {
                $info = getUrlInfo($url);
                if (empty($info['title'])) continue;
                $formatUrl = '['.$info['title'].']('.$url.')';
                $content = str_replace($url,$formatUrl,$content);
            }
        }
        return $content;
    }
}

if (!function_exists('strip_html_tags')) {
    /**
     * 删除指定的标签和内容
     * @param array  $tags 需要删除的标签数组
     * @param string $str 数据源
     * @param boole  $content 是否删除标签内的内容 默认为false保留内容  true不保留内容
     * @return string
     */
    function strip_html_tags($tags,$str,$content=false){
        $html=array();
        foreach ($tags as $tag) {
            if($content){
                $html[]='/(<'.$tag.'.*?>[\s|\S]*?<\/'.$tag.'>)/';
            }else{
                $html[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
            }
        }
        $data=preg_replace($html, '', $str);
        return $data;
    }
}

if (!function_exists('formatAddressBookPhone')) {
    function formatAddressBookPhone($phone) {
        $phone = str_replace('+86','',$phone);
        $temp=array('1','2','3','4','5','6','7','8','9','0');
        $str = '';
        for($i=0;$i<strlen($phone);$i++) {
            if (in_array($phone[$i], $temp)) {
                $str .= $phone[$i];
            }
        }
        return $str;
    }
}

if (!function_exists('convertWechatLimitLinkToUnlimit')) {
    function convertWechatLimitLinkToUnlimit($link, $gzh_id) {
        $ch = curl_init();

        $url=urlencode($link);

        $account=urlencode($gzh_id);

        $url = "https://api.shenjian.io/?appid=46db4da70074ae0e7e08bc7ce90b8d50&url={$url}&account={$account}";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('convertWechatOvertimeLinkToUnlimit')) {
    function convertWechatOvertimeLinkToUnlimit($link) {
        $ch = curl_init();

        $url = "https://api.newrank.cn/api/async/task/sogou/advanced/towxurl";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=utf-8','Key:8ea425d1573648eabc57244a4'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,['url'=>urlencode($link)]);
        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('queryWechatOvertimeLinkToUnlimit')) {
    function queryWechatOvertimeLinkToUnlimit($taskId) {
        $ch = curl_init();

        $url = "https://api.newrank.cn/api/task/result";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=utf-8','Key:8ea425d1573648eabc57244a4'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,['taskId'=>$taskId]);
        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res,true);
    }
}

if (!function_exists('getWechatArticleInfo')) {
    function getWechatArticleInfo($link) {
        $ch = curl_init();

        $url=urlencode($link);

        $url = "https://api.shenjian.io/?appid=25d11b844873dba7c0e2e205add34a27&url={$url}";

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));

        curl_setopt($ch, CURLOPT_ENCODING, "gzip");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        curl_close($ch);
        return json_decode($res,true);
    }
}


if (!function_exists('getWechatUrlBodyText')) {
    function getWechatUrlBodyText($url,$strip_tags=true, $downloadImg = false) {
        $html = file_get_contents_curl($url);
        if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
            $html = curlShadowsocks($url);
        }
        $parse = parse_url($url);
        if ($parse['host'] == 'mp.weixin.qq.com') {
            preg_match_all("/id=\"js_content\">(.*)<script/iUs",$html,$content,PREG_PATTERN_ORDER);
            $html = isset($content[1][0])?($strip_tags?strip_tags($content[1][0]):$content[1][0]):'';
            if ($downloadImg) {
                $html = preg_replace_callback('/data-src="(.*?)"/', function($matches){
                    $imgUrl = saveImgToCdn($matches[1],'wechat_temp');
                    return 'src="'.$imgUrl.'"';
                }, $html);
            }
            //去除微信图片遮罩
            $html = str_replace('opacity: 0;','',$html);
        }
        return $html;
    }
}

if (!function_exists('getWechatUrlInfo')) {
    function getWechatUrlInfo($url,$strip_tags=true, $downloadImg = false) {
        $ql = \QL\QueryList::getInstance();
        $headers = [
            'Host'    => 'mp.weixin.qq.com',
            'Origin'  => 'https://www.itjuzi.com',
            'Referer' => 'http://www.itjuzi.com/investevent',
            'Connection' => 'keep-alive',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Upgrade-Insecure-Requests' => 1
        ];
        $content = $ql->get($url,null,['headers'=>$headers]);
        $html = $content->getHtml();
        if (str_contains($html,'访问过于频繁，请用微信扫描二维码进行访问')) {
            $html = curlShadowsocks($url,$headers);
            $content->setHtml($html);
        }
        $parse = parse_url($url);
        if ($parse['host'] == 'mp.weixin.qq.com') {
            $title = $content->find('h2#activity-name')->text();
            $author = $content->find('a#js_name')->text();
            $wxHao = $content->find('span.profile_meta_value')->eq(0)->text();
            $pattern = "/var\s+ct\s+=\s+([\s\S]*?);/is";
            preg_match($pattern, $html, $matchs);
            $date = trim($matchs[1],'"');
            $pattern = "/var\s+msg_cdn_url\s+=\s+([\s\S]*?);/is";
            preg_match($pattern, $html, $matchs);
            $cover_img = trim($matchs[1],'"');

            preg_match_all("/id=\"js_content\">(.*)<script/iUs",$html,$body,PREG_PATTERN_ORDER);
            $js_content = isset($body[1][0])?($strip_tags?strip_tags($body[1][0]):$body[1][0]):'';
            if ($downloadImg) {
                $js_content = preg_replace_callback('/data-src="(.*?)"/', function($matches1){
                    $imgUrl = saveImgToCdn($matches1[1],'wechat_temp');
                    return 'src="'.$imgUrl.'"';
                }, $js_content);
            }
            //去除微信图片遮罩
            $js_content = str_replace('opacity: 0;','',$js_content);
            return [
                'body' => $js_content,
                'title' => $title,
                'author' => $author,
                'wxHao' => $wxHao,
                'date' => $date,
                'cover_img' => $cover_img
            ];
        }
        return $html;
    }
}

if (!function_exists('formatKeyword')) {
    function formatKeyword($keyword) {
        $keyword = trim($keyword);
        $keyword = str_replace('，','',$keyword);
        $keyword = str_replace('、','',$keyword);
        $keyword = str_replace('"','',$keyword);
        $keyword = str_replace('。','',$keyword);
        return $keyword;
    }
}

if (!function_exists('validateProxyIps')) {
    function validateProxyIps($domain = 'jianyu360') {
        $ips = \App\Services\RateLimiter::instance()->sMembers('proxy_ips_'.$domain);
        $ql = new \QL\QueryList();
        foreach ($ips as $proxyIp) {
            $opts = [
                'proxy' => $proxyIp,
                //Set the timeout time in seconds
                'timeout' => 3,
            ];
            $i=3;
            while ($i--) {
                try {
                    $title = $ql->get('http://www.baidu.com',null,$opts)->find('title')->text();
                    break;
                } catch (Exception $e) {
                    $title = '';
                }
            }
            if (!strstr($title, '百度一下')) {
                deleteProxyIp($proxyIp,$domain);
            }
        }
    }
}

if (!function_exists('getProxyIps')) {
    function getProxyIps($min = 5, $domain = 'jianyu360') {
        $ips = \App\Services\RateLimiter::instance()->sMembers('proxy_ips_'.$domain);
        $ql = new \QL\QueryList();
        $i = 0;

        while (empty($ips)) {
            if ($i >= 5) break;
            $i++;
            //优先取自己的代理
            $scored_proxies = \App\Services\RateLimiter::instance()->zRevrangeByScore('validated:'.$domain,'+inf',7,false,'haipproxy:');
            $ttl_proxies = \App\Services\RateLimiter::instance()->zRevrangeByScore('ttl:'.$domain,'+inf',time() - 30 * 60,false,'haipproxy:');
            $speed_proxies = \App\Services\RateLimiter::instance()->zRangeByScore('speed:'.$domain,0,1000 * 10,false,'haipproxy:');
            $proxies = array_intersect($scored_proxies,$ttl_proxies,$speed_proxies);
            if (!$proxies || count($proxies) < 2*$min) {
                $proxies = array_merge(array_intersect($ttl_proxies, $speed_proxies),$scored_proxies);
            }

            if (!$proxies || count($proxies) < 2*$min)
                $proxies = array_merge($ttl_proxies,$scored_proxies);
            if (!$proxies || count($proxies) < 2*$min) {
                $proxies = array_merge($proxies,$speed_proxies);
            }

            if ($proxies) {
                foreach ($proxies as $proxyIp) {
                    $proxyIp = str_replace('http://','',$proxyIp);
                    if (\App\Services\RateLimiter::instance()->sIsMember('proxy_ips_deleted_'.$domain,$proxyIp)) {
                        deleteProxyIp($proxyIp,$domain);
                        continue;
                    }
                    $opts = [
                        'proxy' => $proxyIp,
                        //Set the timeout time in seconds
                        'timeout' => 3,
                    ];
                    $i=3;
                    while ($i--) {
                        try {
                            $title = $ql->get('http://www.baidu.com',null,$opts)->find('title')->text();
                            break;
                        } catch (Exception $e) {
                            $title = '';
                        }
                    }
                    if (strstr($title, '百度一下')) {
                        \App\Services\RateLimiter::instance()->sAdd('proxy_ips_'.$domain,$proxyIp, 0);
                        $ips[] = $proxyIp;
                    } else {
                        deleteProxyIp($proxyIp,$domain);
                    }
                    if (count($ips) >= 2*$min) return $ips;
                }
            }
            if (count($ips) >= 2*$min) return $ips;
            if (in_array($domain,['sogou'])) continue;
            $scraper_proxy_address = Setting()->get('scraper_proxy_address','');
            if (empty($scraper_proxy_address)) return [];
            $proxy = json_decode(file_get_contents(Setting()->get('scraper_proxy_address','')),true);
            if (!$proxy) {
                return [];
            }
            if ($proxy['code'] == 3001) {
                sleep(6);
            } elseif ($proxy['code'] != 0) {
                event(new \App\Events\Frontend\System\ExceptionNotify('代理返回失败：'.$proxy['msg']));
                return false;
            }
            $ipsNew = $proxy['msg'];
            foreach ($ipsNew as $key=>$ip) {
                $opts = [
                    'proxy' => $ip['ip'].':'.$ip['port'],
                    //Set the timeout time in seconds
                    'timeout' => 3,
                ];
                $i=3;
                while ($i--) {
                    try {
                        $title = $ql->get('http://www.baidu.com',null,$opts)->find('title')->text();
                        break;
                    } catch (Exception $e) {
                        $title = '';
                    }
                }
                if (strstr($title, '百度一下')) {
                    \App\Services\RateLimiter::instance()->sAdd('proxy_ips_'.$domain,$ip['ip'].':'.$ip['port'], 0);
                    $ips[] = $ip['ip'].':'.$ip['port'];
                } else {
                    deleteProxyIp($ip['ip'].':'.$ip['port'],$domain);
                }
            }
        }
        shuffle($ips);
        return $ips;
    }
}

if (!function_exists('deleteProxyIp')) {
    function deleteProxyIp($ip,$domain = 'jianyu360') {
        if (empty($ip)) return;
        $httpIp = 'http://'.$ip;
        \App\Services\RateLimiter::instance()->sRem('proxy_ips_'.$domain,$ip);
        \App\Services\RateLimiter::instance()->sAdd('proxy_ips_deleted_'.$domain,$ip, 0);
        \App\Services\RateLimiter::instance()->sRem('all',$ip,'haipproxy:');
        \App\Services\RateLimiter::instance()->zRem('validated:'.$domain,$httpIp,'haipproxy:');
        \App\Services\RateLimiter::instance()->zRem('ttl:'.$domain,$httpIp,'haipproxy:');
        \App\Services\RateLimiter::instance()->zRem('speed:'.$domain,$httpIp,'haipproxy:');
    }
}

if (!function_exists('curlShadowsocks')) {
    function curlShadowsocks($url,$headers = []) {
        $ch = curl_init($url);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.3 Safari/605.1.15');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        //通过代理访问需要额外添加的参数项
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
        curl_setopt($ch, CURLOPT_PROXYPORT, "1080");

        $result = curl_exec($ch);
        if($result === false){
            $error = curl_error($ch);
            var_dump($error);
            curl_close($ch);
            event(new \App\Events\Frontend\System\ExceptionNotify('curlShadowsocks error:'.$error));
            \App\Services\RateLimiter::instance()->setVale('curlShadowsocks','success',0,60*60*24);
            return false;
        }
        curl_close($ch);

        return $result;
    }
}


if (!function_exists('formatHtml')) {
    function formatHtml($html) {
        $html = str_replace('&#39;', '\'',$html);
        $html = str_replace('&amp;', '&',$html);
        $html = str_replace('&gt;', '>',$html);
        $html = str_replace('&lt;', '<',$html);
        $html = str_replace('&yen;', '¥',$html);
        $html = str_replace('amp;', '',$html);
        $html = str_replace('&lt;', '<',$html);
        $html = str_replace('&gt;', '>',$html);
        $html = str_replace('&nbsp;', ' ',$html);
        $html = str_replace('\\', '',$html);
        return $html;
    }
}

if (!function_exists('checkInvalidTagString')) {
    function checkInvalidTagString($str) {
        return preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_.\·\-\/ ]+$/u", $str);
    }
}

if (!function_exists('convertWechatTempLink')) {
    function convertWechatTempLink($tempUrl,$wxhao='') {
        $ch = curl_init();
        $headers = [];
        $headers[] = 'Host: mp.weixin.qq.com';
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $headers[] = 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15G77 MicroMessenger/6.7.2 NetType/WIFI Language/zh_CN';
        //$headers[] = 'Cookie: wxtokenkey=777; devicetype=iOS11.4.1; lang=zh_CN; pass_ticket=5QcZNPRW6B7DTn8HR24bymCHMIHv2bOcrkLOxw5CTEitpmT+Pi6dqxwGmJ4yB1O+; rewardsn=; version=16070228; wap_sid2=CJCoqZMHElxQdTVHQW5RYUQ5VmJXRjBLblVLeUpYbHRQN19VLTF1YkdvalhGSmxMUGVxNUczdDJFbmI5aHNhUEZpdnd3VVU5dzM2cVBtbm9EcTRoM0NlSjBBWjlHdEFEQUFBfjDkyLvdBTgNQAE=; wxuin=1919570960; pgv_pvid=4415209260; 3g_guest_id=-8754439858010685440; sd_cookie_crttime=1516967978602; sd_userid=83451516967978602';
        //$headers[] = 'Cookie: wxtokenkey=777; devicetype=iOS11.4.1; lang=zh_CN; pass_ticket=5QcZNPRW6B7DTn8HR24bymCHMIHv2bOcrkLOxw5CTEitpmT+Pi6dqxwGmJ4yB1O+; rewardsn=; version=16070228; wap_sid2=CJCoqZMHElxTSUp3TkpiYnZ6bC1xaDgxTnBWOHp5SXU3NkFSM3Z6ekY4elRZTFo4WElRbW5sZ2lRemZtS2JTNHVRVVBBa0hZeFU3SlJzS1VHbTd2N0lMSXU2VEZKOUFEQUFBfjDb0bvdBTgNQAE=; wxuin=1919570960; pgv_pvid=4415209260; 3g_guest_id=-8754439858010685440; sd_cookie_crttime=1516967978602; sd_userid=83451516967978602';
        $headers[] = 'Cookie: news_commid=oDOGxv7l8A-ljmv1uQO1hbBEUIok; pgv_pvid=4415209260; 3g_guest_id=-8754439858010685440; sd_cookie_crttime=1516967978602; sd_userid=83451516967978602';
        $headers[] = 'X-WECHAT-KEY: e681a4d9d0dfea6789de3d06eb7d4b50a62297923874a4474fe5f1e72ad6e4e723505828f3fc1827da6c2ea84f61f5972e758696f85da39793408c7ce229f9544c79ad6a48afb2638e8ad7079f9d89b9';
        $headers[] = 'X-WECHAT-UIN: MTkxOTU3MDk2MA%3D%3D';
        $headers[] = 'Accept-Language: zh-cn';
        $headers[] = 'Accept-Encoding: br, gzip, deflate';
        $headers[] = 'Connection: keep-alive';
        $detailUrl = $tempUrl.'&ascene=1&devicetype=iOS11.4.1&version=16070228&nettype=WIFI&abtest_cookie=BQABAAoACwANABIAEwAFACOXHgBZmR4AYpkeAG6ZHgB8mR4AAAA%3D&lang=zh_CN&fontScale=100&pass_ticket=5QcZNPRW6B7DTn8HR24bymCHMIHv2bOcrkLOxw5CTEitpmT%2BPi6dqxwGmJ4yB1O%2B&wx_header=1';
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $detailUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        var_dump($headers);
        $link = $headers['url'];
        return $link;
    }
}

if (!function_exists('convertWechatTempLinkToForever')) {
    function convertWechatTempLinkToForever($tempUrl) {
        return '';
        try {
            //www.cpopweixin.com
            $ql = \QL\QueryList::getInstance();
            $url = $ql->post('http://47.104.218.0:81/getA8Key?apiKey=cpopweixin_share',['tempUrl'=>$tempUrl])->getHtml();
            $ch = curl_init();
            $headers = [];
            $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            curl_exec($ch);
            $headers = curl_getinfo($ch);
            curl_close($ch);
            $link = $headers['url'];
            if (str_contains($link,'__biz=')) {
                return $link;
            } else {
                event(new \App\Events\Frontend\System\ExceptionNotify('convertWechatTempLinkToForever:'.$link));
                return '';
            }
        } catch (Exception $e) {
            app('sentry')->captureException($e);
            return '';
        }
    }
}


if (!function_exists('submitUrlsToSpider')) {
    function submitUrlsToSpider(array $urls) {
        $api = 'http://data.zz.baidu.com/urls?site=https://www.inwehub.com&token=0DLhRcKq3ET6EcQt';
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        return curl_exec($ch);
    }
}

if (!function_exists('formatElasticSearchTitle')) {
    function formatElasticSearchTitle($title) {
        $cs = ['+','-','=','&&','||','>','<','!','(',')','{','}','[',']','^','"','~','*','?',':','\\','/'];
        foreach ($cs as $c) {
            $title = str_replace($c, "\\".$c,$title);
        }
        $title = str_replace(' ', "*",$title);
        return strtolower($title);
    }
}


if (!function_exists('imdbRank')) {
    /**
     * @param $average_rating ; 该项的投票平均分
     * @param $votes_number ;该项的投票人数
     * @param $minimum_votes ;总的最低投票数
     * @param $correctly_votes_rate ;总的平均分
     * @return float|int
     */
    function imdbRank($average_rating, $votes_number, $minimum_votes, $correctly_votes_rate) {
        return ($votes_number / ($votes_number + $minimum_votes)) * $average_rating + ($minimum_votes / (
                    $votes_number + $minimum_votes)) * $correctly_votes_rate;
    }
}


if (!function_exists('fiveStarCovertToUpAndDown')) {
    function fiveStarCovertToUpAndDown($oneNum,$twoNum,$threeNum,$fourNum,$fiveNum) {
        $upvotes = $oneNum * 0 + $twoNum * 0.25 + $threeNum * 0.5 + $fourNum * 0.75 + $fiveNum * 1;
        $downvotes = $oneNum * 1 + $twoNum * 0.75 + $threeNum * 0.5 + $fourNum * 0.25 + $fiveNum * 0;
        return [
            'up' => $upvotes,
            'down' => $downvotes
        ];
    }
}



function varianceCalc($arr) {
    $length = count($arr);
    if ($length == 0) {
        return array(0,0);
    }
    $average = array_sum($arr)/$length;
    $count = 0;
    foreach ($arr as $v) {
        $count += pow($average-$v, 2);
    }
    $variance = $count/$length;
    return array('variance' => $variance, 'square' => sqrt($variance), 'average' => $average);
}

/**
 * 自动识别关键词方法
 * @param String $text 需要查询的文本
 * @param Array $keysStr 用来标记的关键词字符串
 * @param Int $similar 可以插入的关键词相似度 默认60%
 * @return Array
 */
function searchKeys($text,$keysArr_1D,$similar = ""){
//关键词相似度
    $similar = $similar == null ? 60 : $similar;
//组装特殊字符，并替换
    //$Exp = str_replace(array(":","。",'"',"/","-","_","=","~","`","(",")","*","&","^","%","$","#","@","!",":","：","、","“","．","”",";","】","【","[","]","|",'\/'," ","　","＇","＂","＜","＞","?","／","］","［","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","＝","－","／","＊","－","＋","．","｀","～","；","：","＇","＂","｜","＼"),"",strip_tags($text));
    $Exp = str_replace(array(":","。",'"',"_","=","~","`","*","&","^","%","$","#","@","!",":","：","、","“","．","”",";","】","【","[","]","|",'\/',"＇","＂","＜","＞","?","／","］","［","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","＝","－","／","＊","－","．","｀","～","；","：","＇","＂","｜","＼"),"",strip_tags($text));
    $Exps = str_replace("，",",",$Exp);

//将切割的文字组装成数组
    $textArr_1D = explode(",",$Exps);
//将内容转换成二维数组
    $textArr_2D = array();
    foreach($textArr_1D as $val){
        $textArr_2D[]['text'] = $val;
    }
    $data = [];
//切割关键词成一维数组
//转换成二维数组
    $keysArr_2D = array();
    foreach ($keysArr_1D as $val) {
        //长度小于4的过滤掉
        if (strlen($val) <= 4) continue;
        $keysArr_2D[]['keys'] = $val;
    }
//开始匹配关键词
    foreach ($textArr_2D as $t_k => $t_v) {
        foreach ($keysArr_2D as $k_k => $k_v) {
//判断关键词不为空
            if($k_v['keys'] != ""){
//根据文本相似度
                if(similar_text($t_v['text'],$k_v['keys'],$percent)){
//当相似度大于等于**时插入到数组
                    if($percent >= $similar){
                        $data[]['keys'] = $k_v['keys'].$percent."%";
                    }
                }
//不区分大小写寻找相同字符
                if(stristr($t_v['text'],$k_v['keys']) != false){
                    $data[]['keys'] = $k_v['keys'];
                }
//区分大小写寻找相同字符
                if(strpos($t_v['text'],$k_v['keys']) != false){
                    $data[]['keys'] = $k_v['keys'];
                }
            }
        }
    }
    return array_unique_fb($data);
}
/**
 * 数组去重方法
 */
function array_unique_fb($array2D){
    $temp = [];
    foreach ($array2D as $v){
        $v = join(",",$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[] = $v;
    }
    $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $temp[$k] = explode(",",$v); //再将拆开的数组重新组装
    }
    return $temp;
}

/*
 *
 * @desc URL安全形式的base64编码
 * @param string $str
 * @return string
 */
function urlsafe_base64_encode($str){
    $find = array("+","/");
    $replace = array("-", "_");
    return str_replace($find, $replace, base64_encode($str));
}


function weapp_qrcode_replace_logo($qrcodeUrl,$newLogoUrl,$circleQr = false) {
    $circleLogo = \App\Services\RateLimiter::instance()->hGet('weapp_dp_logo_circle',$newLogoUrl);
    if (!$circleLogo) {
        $circleLogo = $newLogoUrl.'?x-oss-process=image/resize,m_lfit,h_192,w_192,limit_0,image/circle,r_100/format,png';
        $file_name = 'product/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
        Storage::disk('oss')->put($file_name,file_get_contents($circleLogo));
        $circleLogo = Storage::disk('oss')->url($file_name);
        \App\Services\RateLimiter::instance()->hSet('weapp_dp_logo_circle',$newLogoUrl,$circleLogo);
    }
    $logoUrl = str_replace('https://cdn.inwehub.com/','',$circleLogo);

    $s = urlsafe_base64_encode($logoUrl);
    return $qrcodeUrl.'?x-oss-process=image/resize,w_430,h_430'.($circleQr?',image/circle,r_300/format,png':'').'/watermark,image_'.$s.',g_center';
}

function formatThirdLink($link) {
    if (str_contains($link,'?')) {
        $link = $link.'&from_source=inwehub';
    } else {
        $link = $link.'?from_source=inwehub';
    }
    return $link;
}

