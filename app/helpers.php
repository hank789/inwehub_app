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
    function saveImgToCdn($imgUrl){
        $parse_url = parse_url($imgUrl);
        if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
            $file_name = 'avatar/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
            Storage::disk('oss')->put($file_name,file_get_contents($imgUrl));
            $cdn_url = Storage::disk('oss')->url($file_name);
            return $cdn_url;
        }
        return $imgUrl;
    }
}

if (!function_exists('getUrlImg')) {
    function getUrlImg($url, $dir = 'submissions') {
        $f = file_get_contents_curl($url);
        $temp = '';
        $img_url = '';
        $useCache = false;
        if (str_contains($url,'mp.weixin.qq.com')) {
            //微信的文章
            $pattern = '/var msg_cdn_url = "(.*?)";/s';
            preg_match_all($pattern,$f,$matches);
            if(array_key_exists(1, $matches) && !empty($matches[1][0])) {
                $temp = $matches[1][0];
            }
        } else {
            preg_match_all('/<[\s]*meta[\s]*(name|property)="?(og:image)"?[\s]*content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $f, $match);
            if (isset($match[3][0]) && false) {
                $temp = $match[3][0];
            } else {
                $temp = Cache::get('domain_url_img_'.domain($url),'');
                if (empty($temp) || true) {
                    $pattern='/<link.+href="?(\S+\.ico)\??"?.+>/i';
                    preg_match_all($pattern,$f,$matchContent);
                    if(isset($matchContent[1][0])){
                        $temp=$matchContent[1][0];
                        if (stripos($temp,'//') === 0) {
                            $temp = 'http:'.$temp;
                        } elseif (stripos($temp,'http') !== 0) {
                            $urls = parse_url($url);
                            $temp = $urls['scheme'].'://'.$urls['host'].$temp;
                        }
                        $useCache = true;
                    }
                } else {
                    return $temp;
                }
            }
        }
        if ($temp) {
            //保存图片
            $img_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
            dispatch((new \App\Jobs\UploadFile($img_name,base64_encode(file_get_contents($temp)))));
            $img_url = Storage::url($img_name);
            //非微信文章
            if ($useCache) {
                Cache::put('domain_url_img_'.domain($url),$img_url,60 * 24 * 30);
            }
        }
        return $img_url;
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
        $startTime = 1473696439;
        $created = time();
        $timeDiff = $created - $startTime;

        return $timeDiff / 45000;
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

if (!function_exists('getUrlTitle')) {
    function getUrlTitle($url) {
        try {
            $f = file_get_contents_curl($url);
            if (str_contains($url,'mp.weixin.qq.com')) {
                preg_match('/<h2 class="rich_media_title" id="activity-name">(?<h2>.*?)<\/h2>/si', $f, $title);
                $title['title'] = $title['h2'];
            } else {
                preg_match('/<title>(?<title>.*?)<\/title>/si', $f, $title);
            }

            $encode = mb_detect_encoding($title['title'], array('GB2312','GBK','UTF-8', 'CP936', 'ASCII')); //得到字符串编码
            $file_charset = iconv_get_encoding()['internal_encoding']; //当前文件编码
            $title['title'] = trim($title['title']);
            if ( $encode != 'CP936' && $encode != $file_charset) {
                return iconv($encode, $file_charset, $title['title']);
            }
            if (str_contains($url,'3g.163.com')) {
                $title['title'] = trim($title['title'],'_&#x624B;&#x673A;&#x7F51;&#x6613;&#x7F51;');
            }
            return $title['title'];
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('file_get_contents_curl')) {
    function file_get_contents_curl($url, $timeout = '10')
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
        preg_match_all('/(http|https):[\/]{2}[A-Za-z0-9,:\\._\\?#%&+\\-=\/]*/',strip_tags(strip_html_tags(['a'],$content,true)),$urls);
        return $urls[0];
    }
}

if (!function_exists('formatContentUrls')) {
    function formatContentUrls($content){
        $urls = getContentUrls($content);
        if ($urls) {
            foreach ($urls as $url) {
                $title = getUrlTitle($url);
                if (empty($title)) continue;
                $formatUrl = '['.$title.']('.$url.')';
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

        $url = "http://api.shenjian.io/?appid=ade2cec5bc6305681b67ee08f351e93f&url={$url}&account={$account}";

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 执行HTTP请求

        curl_setopt($ch , CURLOPT_URL , $url);

        $res = curl_exec($ch);

        return json_decode($res,true);
    }
}