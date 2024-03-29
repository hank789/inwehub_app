<?php namespace App\Events\Frontend\Wechat;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: hank.huiwang@gmail.com
 */

use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered.
 */
class Notice
{

    use SerializesModels;


    /**
     * @var
     */
    public $user_id;

    public $title;

    public $keyword1;

    public $keyword2;

    public $keyword3;

    public $keyword4;

    public $remark;

    public $template_id;

    public $target_url;

    public function __construct($user_id, $title, $keyword1, $keyword2,$keyword3='',$keyword4='', $remark, $template_id, $target_url)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->keyword1 = $keyword1;
        $this->keyword2 = $keyword2;
        $this->keyword3 = $keyword3;
        $this->keyword4 = $keyword4;
        $this->remark = $remark;
        $this->template_id = $template_id;
        $this->target_url = $target_url;
    }


}