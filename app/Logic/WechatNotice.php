<?php namespace App\Logic;
use App\Events\Frontend\Wechat\Notice;
use App\Models\Answer;

/**
 * @author: wanghui
 * @date: 2017/6/29 下午2:23
 * @email: wanghui@yonglibao.com
 */

class WechatNotice {

    //新任务处理通知
    public static function newTaskNotice($toUser,$content,$object_type,$object){
        $url = config('app.mobile_url');
        switch($object_type){
            case 'question_invite_answer_confirming':
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
                $title = '您好，已有专家回答了您的专业问答任务';
                $keyword2 = $object->user->name;
                $remark = '可点击详情查看回答内容';
                $target_url = $url.'#/ask/'.$object->question->id;
                $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
                if (config('app.env') != 'production') {
                    $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
                }
                break;
            default:
                return;
                break;
        }
        event(new Notice($toUser,$title,$content,$keyword2,$remark,$template_id,$target_url));
    }

}