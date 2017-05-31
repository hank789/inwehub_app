<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\ConfirmOvertime;
use App\Logic\QuestionLogic;
use App\Models\Question;
use App\Models\QuestionInvitation;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuestionInvitationObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  QuestionInvitation  $invitation
     * @return void
     */
    public function created(QuestionInvitation $invitation)
    {
        QuestionLogic::slackMsg($invitation->question)
            ->send('问题'.($invitation->send_to == 'auto'?'自动':'').'分配给了用户['.$invitation->user->name.']');
        //延时处理是否需要告警专家
        dispatch((new ConfirmOvertime($invitation->question_id,$invitation->id))->delay(Carbon::now()->addMinutes(Setting()->get('alert_minute_expert_unconfirm_question',10))));
    }



}