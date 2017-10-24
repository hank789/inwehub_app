<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午2:16
 * @email: wanghui@yonglibao.com
 */

use App\Models\Attention;
use App\Models\Authentication;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthenticationObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;


    public function creating(Authentication $authentication)
    {
        $this->slackMsg($authentication)
            ->send('用户'.$authentication->user->id.'['.$authentication->user->name.']申请专家认证');
    }

    public function deleting(Authentication $authentication){
        //删除关注
        Attention::where('source_type','App\Models\User')->where('source_id',$authentication->user_id)->delete();
    }


    protected function slackMsg(Authentication $authentication){
        $url = route('admin.authentication.edit',['user_id'=>$authentication->user_id]);
        $user = User::find($authentication->user_id);
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => '申请专家认证',
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext']
                ]
            );
    }

}