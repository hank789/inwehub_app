<?php namespace App\Logic;
use App\Events\Frontend\Wechat\Notice;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Pay\MoneyLog;
use App\Models\Question;
use App\Models\Readhub\Comment;
use App\Models\User;

/**
 * @author: wanghui
 * @date: 2017/6/29 下午2:23
 * @email: wanghui@yonglibao.com
 */

class WechatNotice {

    //新任务处理通知
    public static function newTaskNotice($toUserId,$content,$object_type,$object_id, $target_url =''){
        $url = config('app.mobile_url');
        $keyword3 = '';
        switch($object_type){
            case 'pay_question_invite_answer_confirming':
                $object = Question::find($object_id);
                $title = '您好，您有新的回答邀请';
                $keyword2 = '专业问答任务邀请';
                $remark = '请立即前往确认回答';
                $target_url = $url.'#/answer/'.$object->id;
                $template_id = 'bVUSORjeArW08YvwDIgYgEAnjo49GmBuLPN9CPzIYrc';
                if (config('app.env') != 'production') {
                    $template_id = 'EdchssuL5CWldA1eVfvtXHo737mqiH5dWLtUN7Ynwtg';
                }
                break;
            case 'free_question_invite_answer_confirming':
                $object = Question::find($object_id);
                $title = '您好，您有新的回答邀请';
                $keyword2 = '互动问答邀请';
                $remark = '请立即前往确认回答';
                $target_url = $url.'#/askCommunity/interaction/answers/'.$object->id;
                $template_id = 'bVUSORjeArW08YvwDIgYgEAnjo49GmBuLPN9CPzIYrc';
                if (config('app.env') != 'production') {
                    $template_id = 'EdchssuL5CWldA1eVfvtXHo737mqiH5dWLtUN7Ynwtg';
                }
                break;
            case 'question_answer_confirmed':
                $object = Answer::find($object_id);
                $title = '您好，已有专家响应了您的专业问答任务';
                $keyword2 = $object->user->name;
                $remark = '可点击详情查看处理进度';
                $target_url = $url.'#/ask/'.$object->question->id;
                $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
                if (config('app.env') != 'production') {
                    $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
                }
                break;
            case 'pay_question_answered':
                $object = Answer::find($object_id);
                $title = '您好，已有专家回答了您的专业问答任务';
                $keyword2 = $object->user->name;
                $remark = '可点击详情查看回答内容';
                $target_url = $url.'#/ask/'.$object->question->id;
                $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
                if (config('app.env') != 'production') {
                    $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
                }
                break;
            case 'free_question_answered':
                $object = Answer::find($object_id);
                $title = '您好，您的提问有新的回答';
                $keyword2 = $object->user->name;
                $remark = '可点击详情查看回答内容';
                $target_url = $url.'#/askCommunity/interaction/'.$object_id;
                $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
                if (config('app.env') != 'production') {
                    $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
                }
                break;
            case 'question_answer_promise_overtime';
                $object = Answer::find($object_id);
                $title = '您好，您有专业问答任务即将延期,请及时处理!';
                $keyword2 = date('Y-m-d H:i',strtotime($object->promise_time));
                $remark = '可点击详情立即前往回答';
                $target_url = $url.'#/answer/'.$object->question->id;
                $template_id = 'aOeQPpVu_aHC1xuJgMlZqkkI4j9mmqjLXn3SvX2b3hg';
                if (config('app.env') != 'production') {
                    $template_id = 'zvZO6wKROVb3fWCE8TGIOtQ3y3k4527wD_Lsk6dyNnM';
                }
                break;
            case 'pay_answer_new_comment':
                $comment = \App\Models\Comment::find($object_id);
                $answer = $comment->source;
                $title = '您好，有人回复了您的专业回答';
                $keyword2 = date('Y-m-d H:i',strtotime($answer->created_at));
                $keyword3 = $comment->content;
                $remark = '请点击查看详情！';
                $target_url = $url.'#/askCommunity/major/'.$answer->question_id;
                $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
                if (config('app.env') != 'production') {
                    $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
                }
                break;
            case 'free_answer_new_comment':
                $comment = \App\Models\Comment::find($object_id);
                $answer = $comment->source;
                $title = '您好，有人回复了您的互动回答';
                $keyword2 = date('Y-m-d H:i',strtotime($answer->created_at));
                $keyword3 = $comment->content;
                $remark = '请点击查看详情！';
                $target_url = $url.'#/askCommunity/interaction/'.$answer->id;
                $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
                if (config('app.env') != 'production') {
                    $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
                }
                break;
            case 'authentication':
                $object = Authentication::find($object_id);
                $title = '您的专家申请已处理';
                $keyword2 = date('Y-m-d H:i',strtotime($object->created_at));
                $target_url = $url.'#/my';
                $remark = '请点击查看详情！';
                switch ($object->status){
                    case 1:
                        $keyword3 = '恭喜你成为平台认证专家！';
                        $target_url = $url.'#/my';
                        break;
                    case 4:
                        $keyword3 = '很抱歉，您的专家认证未通过审核：'.$object->failed_reason;
                        $target_url = $url.'#/my/pilot';
                        $remark = '点击前往重新申请！';
                        break;
                }


                $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
                if (config('app.env') != 'production') {
                    $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
                }
                break;
            case 'company_auth':
                $object = Company::find($object_id);
                $title = '您的企业申请已处理';
                $keyword2 = date('Y-m-d H:i',strtotime($object->created_at));
                $remark = '请点击查看详情！';
                switch ($object->apply_status){
                    case Company::APPLY_STATUS_SUCCESS:
                        $keyword3 = '恭喜你成为平台认证企业！';
                        break;
                    case Company::APPLY_STATUS_REJECT:
                        $keyword3 = '很抱歉，您的企业认证未通过审核';
                        $remark = '点击前往重新申请！';
                        break;
                }

                $target_url = $url.'#/company/my';
                $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
                if (config('app.env') != 'production') {
                    $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
                }
                break;
            case 'notification_money_fee':
            case 'notification_money_settlement':
            case 'notification_pay_for_view_settlement':
                $object = MoneyLog::find($object_id);
                $title = '您的账户资金发生以下变动!';
                $keyword2 = ($object->io >= 1 ? '+' : '-').$object->change_money.'元';
                $keyword3 = date('Y-m-d H:i:s',strtotime($object->updated_at));
                $target_url = $url.'#/my/finance';
                $template_id = '5djK0UUvpHq9TjWFEYujXwqzf7qUR-O8_C_Wzl7W6lg';
                if (config('app.env') != 'production') {
                    $template_id = 'WOt-iIVBMYJUjazUVOZ3lbGFjyO_VbpBH1sEohbnBtA';
                }
                $remark = '请点击查看详情！';
                break;
            case 'readhub_comment_replied':
                $title = '您好，您的回复收到一条评论';
                $object = Comment::find($object_id);
                $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
                $keyword3 = $object->body;
                $remark = '请点击查看详情！';
                $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
                if (config('app.env') != 'production') {
                    $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
                }
                $user = User::find($toUserId);
                $target_url = config('app.readhub_url').'/h5?uuid='.$user->uuid.'&redirect_url='.$target_url;
                break;
            case 'readhub_submission_replied':
                $title = '您好，您的文章收到一条评论';
                $object = Comment::find($object_id);
                $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
                $keyword3 = $object->body;
                $remark = '请点击查看详情！';
                $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
                if (config('app.env') != 'production') {
                    $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
                }
                $user = User::find($toUserId);
                $target_url = config('app.readhub_url').'/h5?uuid='.$user->uuid.'&redirect_url='.$target_url;
                break;
            case 'readhub_username_mentioned':
                $title = '您好，'.$content.'在回复中提到了你';
                $object = Comment::find($object_id);
                $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
                $keyword3 = $object->body;
                $remark = '请点击查看详情！';
                $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
                if (config('app.env') != 'production') {
                    $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
                }
                $user = User::find($toUserId);
                $target_url = config('app.readhub_url').'/h5?uuid='.$user->uuid.'&redirect_url='.$target_url;
                break;
            case 'user_following':
                $title = '又有新用户关注了你';
                $object = Attention::find($object_id);
                $keyword2 = date('Y-m-d H:i:s',strtotime($object->created_at));
                $remark = '点击查看Ta的顾问名片';
                $template_id = '24x-vyoHM0SncChmtbRv_uoPCBnI8JXFrmTsWfqccQs';
                if (config('app.env') != 'production') {
                    $template_id = 'mCMHMPCPc1ceoQy66mWPee-krVmAxAB9g7kCQex6bUs';
                }
                $user = User::find($object->user_id);
                $content = $user->name;
                $target_url = $url.'#/share/resume/'.$user->uuid;
                break;
            default:
                return;
                break;
        }
        event(new Notice($toUserId,$title,$content,$keyword2,$keyword3,$remark,$template_id,$target_url));
    }

}