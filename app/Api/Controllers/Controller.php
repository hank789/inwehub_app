<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/6 下午2:57
 * @email: wanghui@yonglibao.com
 */

use App\Models\Credit;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use App\Traits\CreateJsonResponseData;
use Zhuzhichao\IpLocationZh\Ip;
use Illuminate\Support\Facades\Mail;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, CreateJsonResponseData;

    /**
     * 修改用户积分
     * @param $user_id 用户id
     * @param $action  执行动作：提问、回答、发起文章
     * @param int $source_id 源：问题id、回答id、文章id等
     * @param string $source_subject 源主题：问题标题、文章标题等
     * @param int $coins      金币数/财富值
     * @param int $credits    经验值
     * @return bool           操作成功返回true 否则  false
     */
    protected function credit($user_id,$action,$coins = 0,$credits = 0,$source_id = 0 ,$source_subject = null)
    {
        DB::beginTransaction();
        try{
            /*用户登陆只添加一次积分*/
            if($action == 'login' && Credit::where('user_id','=',$user_id)->where('action','=',$action)->where('created_at','>',Carbon::today())->count()>0){
                return false;
            }
            /*记录详情数据*/
            Credit::create([
                'user_id' => $user_id,
                'action' => $action,
                'source_id' => $source_id,
                'source_subject' => $source_subject,
                'coins' => $coins,
                'credits' => $credits,
                'created_at' => Carbon::now()
            ]);

            /*修改用户账户信息*/
            UserData::find($user_id)->increment('coins',$coins);
            UserData::find($user_id)->increment('credits',$credits);
            DB::commit();
            return true;

        }catch (\Exception $e) {
            DB::rollBack();
            return false;
        }

    }

    protected function findIp($ip): array
    {
        return (array) Ip::find($ip);
    }

    /*邮件发送*/
    protected function sendEmail($email,$subject,$message){

        if(Setting()->get('mail_open') != 1){//关闭邮件发送
            return false;
        }

        $data = [
            'email' => $email,
            'subject' => $subject,
            'body' => $message,
        ];


        Mail::queue('emails.common', $data, function($message) use ($data)
        {
            $message->to($data['email'])->subject($data['subject']);
        });

    }

}