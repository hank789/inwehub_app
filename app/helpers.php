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

if (! function_exists('trans_withdraw_status')) {

    function trans_withdraw_status($status){
        $map = [
            0 => '待处理',
            1 => '处理中',
            2 => '处理成功',
            3 => '处理失败'
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

    function load_categories( $type = 'all' ){
        return app('App\Models\Category')->loadFromCache($type);
    }

}


/*数据库area地区表操作*/
if (! function_exists('Area')) {

    function Area(){
        return app('App\Models\Area');
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
            $messages[] = '经验 '.integer_string($credits);
        }
        if( $coins != 0 ){
            $messages[] = '金币 '.integer_string($coins);
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

//生成验证码
if( !function_exists('makeVerifyCode') ){
    function makeVerifyCode(int $min = 1000, int $max = 9999)
    {
        if(env('APP_ENV') != 'production') return 6666;
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
        $total = 0;
        $filled = 0;
        $info = $data['info'];
        foreach($info as $field=>$value){
            if(in_array($field,$expert_fields)) continue;
            $total++;
            if($filled=='avatar_url' && $value==config('image.user_default_avatar')) continue;
            if(!empty($value) || $value === "0") $filled++;
        }
        unset($data['info']);
        foreach($data as $field=>$value){
            if(in_array($field,$expert_fields)) continue;
            $total++;
            if(!empty($value)) $filled++;
        }
        return ['total'=>$total,'filled'=>$filled];
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


