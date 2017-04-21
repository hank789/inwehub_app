<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午2:16
 * @email: wanghui@yonglibao.com
 */

use App\Models\Authentication;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthenticationObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function creating(Authentication $authentication)
    {
        $this->slackMsg($authentication)
            ->send('用户['.$authentication->user->name.']申请专家认证');
    }


    protected function slackMsg(Authentication $authentication){
        $url = route('admin.authentication.edit',['user_id'=>$authentication->user_id]);
        return \Slack::to('#app_ask_activity')
            ->disableMarkdown()
            ->attach(
                [
                    'text' => '申请专家认证',
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $authentication->user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext']
                ]
            );
    }

}