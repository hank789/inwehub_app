<?php namespace App\Events\Frontend\Expert;

/**
 * @author: wanghui
 * @date: 2017/5/10 下午9:51
 * @email: wanghui@yonglibao.com
 */
class Recommend
{

    /**
     * @var
     */
    public $user_id;

    public $name;

    public $gender;

    public $industry_tags;

    public $work_years;

    public $mobile;

    public $description;

    public $head_img_url;

    public function __construct($user_id,$name,$gender,$industry_tags,$work_years,$mobile,$description,$head_img_url)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->gender = $gender;
        $this->industry_tags = $industry_tags;
        $this->work_years = $work_years;
        $this->mobile = $mobile;
        $this->description = $description;
        $this->head_img_url = $head_img_url;
    }


}