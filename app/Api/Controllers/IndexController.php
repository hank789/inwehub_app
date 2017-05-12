<?php namespace App\Api\Controllers;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller {
    public function home(){
        //推荐问答
        $recommend_qa = [
            [
                "user_name"=> "隔壁老王",//提问者名字
                "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/11/user_origin_10.jpg",//头像地址
                "description"=> "怎样成为专家?",//问题内容
                "price"=> "188"
            ],
            [
                "user_name"=> "隔壁老王",//提问者名字
                "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg",//头像地址
                "description"=> "怎样成为专家?",//问题内容
                "price"=> "188"
            ]
        ];
        $data = [
            'expert_number' => 127,//专家数量
            'average_answer_minute' => 30,//平均应答分钟
            'industry_number' => 67,//行业数目
            'header_image_url' => 'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/default/WechatIMG1.jpeg',//资深专家推荐图片
            'recommend_qa' => $recommend_qa
        ];

        return self::createJsonData(true,$data);
    }
}