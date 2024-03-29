<?php namespace App\Logic;
use App\Exceptions\ApiException;
use App\Models\Pay\Withdraw;
use App\Models\UserOauth;
use App\Services\RateLimiter;
use Payment\Client\Transfer;
use Payment\Config;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午4:21
 * @email: hank.huiwang@gmail.com
 */


class WithdrawLogic {

    public static function withdrawRequest(Withdraw $withdraw)
    {
        if($withdraw->status != Withdraw::WITHDRAW_STATUS_PROCESS){
            return false;
        }

        switch($withdraw->withdraw_channel){
            case Withdraw::WITHDRAW_CHANNEL_WX:
                $user_oauth = UserOauth::where('user_id',$withdraw->user_id)->where('auth_type',UserOauth::AUTH_TYPE_WEIXIN)->where('status',1)->orderBy('updated_at','desc')->first();
                if(!$user_oauth){
                    $withdraw->response_msg = '未绑定微信';
                    $withdraw->status = Withdraw::WITHDRAW_STATUS_FAIL;
                    $withdraw->save();
                    return false;
                }
                $config = config('payment')['wechat'];
                $channel = Config::WX_TRANSFER;
                break;
            case Withdraw::WITHDRAW_CHANNEL_WX_PUB:
                $user_oauth = UserOauth::where('user_id',$withdraw->user_id)->where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)->where('status',1)->orderBy('updated_at','desc')->first();
                if(!$user_oauth){
                    $withdraw->response_msg = '未绑定微信';
                    $withdraw->status = Withdraw::WITHDRAW_STATUS_FAIL;
                    $withdraw->save();
                    return false;
                }
                $config = config('payment')['wechat_pub'];
                $channel = Config::WX_TRANSFER;
                break;
            case Withdraw::WITHDRAW_CHANNEL_ALIPAY:
                $config = config('payment')['alipay'];
                $channel = Config::ALI_TRANSFER;
                break;
            default:
                return false;
                break;
        }
        $data = [
            'trans_no' => $withdraw->order_no,
            'openid' => $user_oauth->openid,
            'check_name' => 'NO_CHECK',// NO_CHECK：不校验真实姓名  FORCE_CHECK：强校验真实姓名   OPTION_CHECK：针对已实名认证的用户才校验真实姓名
            'payer_real_name' => '',
            'amount' => $withdraw->amount,
            'device_info' => 'WEB',
            'desc' => '提现',
            'spbill_create_ip' => $withdraw->client_ip,
        ];
        try {
            $ret = Transfer::run($channel, $config, $data);
        } catch (\Exception $e) {
            $withdraw->response_msg = $e->getMessage();
            $withdraw->status = Withdraw::WITHDRAW_STATUS_FAIL;
            $withdraw->save();
            return false;
        }
        if($ret['is_success'] =='F'){
            $withdraw->response_msg = $ret['error'];
            $withdraw->status = Withdraw::WITHDRAW_STATUS_FAIL;
            $withdraw->save();
            return false;
        }else {
            $response = $ret['response'];
            $withdraw->response_msg = $ret['is_success'];
            $withdraw->status = Withdraw::WITHDRAW_STATUS_SUCCESS;
            $withdraw->transaction_id = $response['transaction_id'];
            $withdraw->finish_time = date('Y-m-d H:i:s');
            $withdraw->response_data = json_encode($response);
            $withdraw->save();
            return true;
        }

    }

    public static function getWithdrawChannelLimit($channel){
        $limit = 10;
        switch($channel){
            case 'wx_transfer':
                $limit = 10;
                break;
            case 'ali_transfer':
                $limit = 10;
                break;
        }
        $system_limit = Setting()->get('withdraw_day_limit',0);
        if($system_limit) $limit = $system_limit;
        return $limit;
    }

    public static function getWithdrawChannelAmount($channel){
        $limit = 20000;
        switch($channel){
            case 'wx_transfer':
                $limit = 20000;
                break;
            case 'ali_transfer':
                $limit = 50000;
                break;
        }
        return $limit;
    }



    public static function incUserWithdrawCount($user_id,$channel){
        $limit = self::getWithdrawChannelLimit($channel);
        return RateLimiter::instance()->increase('withdraw_count_'.date('Ymd').'_'.$channel,$user_id,86400,$limit);
    }

    public static function incUserWithdrawAmount($user_id,$channel,$amount)
    {
        return RateLimiter::instance()->increaseBy('withdraw_amount_'.date('Ymd').'_'.$channel,$user_id,intval($amount),86400);
    }

    public static function getUserWithdrawCount($user_id,$channel){
        return RateLimiter::instance()->getValue('withdraw_count_'.date('Ymd').'_'.$channel,$user_id);
    }

    public static function getUserWithdrawAmount($user_id,$channel){
        return RateLimiter::instance()->getValue('withdraw_amount_'.date('Ymd').'_'.$channel,$user_id);
    }

    public static function checkUserWithdrawLimit($user_id, $amount){
        //是否系统暂停了提现
        if(Setting()->get('withdraw_suspend',0)){
            throw new ApiException(ApiException::WITHDRAW_SYSTEM_SUSPEND);
        }

        $channel = 'wx_transfer';
        $count = self::getUserWithdrawCount($user_id, $channel);
        $limit = self::getWithdrawChannelLimit($channel);
        if($count >= $limit){
            throw new ApiException(ApiException::WITHDRAW_DAY_COUNT_LIMIT);
        }
        $current_amount = self::getUserWithdrawAmount($user_id, $channel);
        $total_amount = self::getWithdrawChannelAmount($channel);
        if($amount+$current_amount > $total_amount){
            throw new ApiException(ApiException::WITHDRAW_DAY_AMOUNT_LIMIT);
        }
        //todo 商户微信单日转账总额是100w
    }

}