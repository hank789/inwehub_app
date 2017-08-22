<?php namespace App\Logic;
use App\Events\Frontend\Wechat\Notice;
use App\Models\Answer;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Question;

/**
 * @author: wanghui
 * @date: 2017/6/29 下午2:23
 * @email: wanghui@yonglibao.com
 */

class WechatNotice {

    //新任务处理通知
    public static function newTaskNotice($toUserId,$content,$object_type,$object_id){
        $url = config('app.mobile_url');
        $keyword3 = '';
        switch($object_type){
            case 'question_invite_answer_confirming':
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
            case 'question_answered':
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
            case 'authentication':
                $object = Authentication::find($object_id);
                $title = '您的专家申请已处理';
                $keyword2 = date('Y-m-d H:i',strtotime($object->created_at));
                switch ($object->status){
                    case 1:
                        $keyword3 = '恭喜你成为平台认证专家！';
                        break;
                    case 4:
                        $keyword3 = '很抱歉，您的专家认证未通过审核：'.$object->failed_reason;;
                        break;
                }
                $remark = '可点击详情立即前往回答';
                $target_url = $url.'#/my';
                $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
                if (config('app.env') != 'production') {
                    $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
                }
                break;
            case 'company_auth':
                $object = Company::find($object_id);
                $title = '您的企业申请已处理';
                $keyword2 = date('Y-m-d H:i',strtotime($object->created_at));
                switch ($object->apply_status){
                    case Company::APPLY_STATUS_SUCCESS:
                        $keyword3 = '恭喜你成为平台认证企业！';
                        break;
                    case Company::APPLY_STATUS_REJECT:
                        $keyword3 = '很抱歉，您的企业认证未通过审核';
                        break;
                }
                $remark = '可点击详情立即前往回答';
                $target_url = $url.'#/company/my';
                $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
                if (config('app.env') != 'production') {
                    $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
                }
                break;
            default:
                return;
                break;
        }
        event(new Notice($toUserId,$title,$content,$keyword2,$keyword3,$remark,$template_id,$target_url));
    }

}