<?php namespace App\Api\Controllers;
use App\Models\RecommendQa;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller {
    public function home(){
        $recommend_qa = RecommendQa::where('status',1)->orderBy('sort','asc')->orderBy('updated_at','desc')->get()->take(2)->toArray();
        if(empty($recommend_qa)){
            //推荐问答
            $recommend_qa = [
                [
                    "user_name"=> "隔壁老王",//提问者名字
                    "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/11/user_origin_10.jpg",//头像地址
                    "description"=> "老郭的花生卖多少钱?成本价多少?毛利率多少?赚钱么?我可以加盟吗?加盟费多少钱?",//问题内容
                    "price"=> "188"
                ],
                [
                    "user_name"=> "隔壁老王",//提问者名字
                    "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg",//头像地址
                    "description"=> "老郭的花生卖多少钱?成本价多少?毛利率多少?赚钱么?我可以加盟吗?加盟费多少钱?",//问题内容
                    "price"=> "188"
                ]
            ];
        }

        $data = [
            'expert_number' => Setting()->get('operate_expert_number',127),//专家数量
            'average_answer_minute' => Setting()->get('operate_average_answer_minute',30),//平均应答分钟
            'industry_number' => Setting()->get('operate_industry_number',67),//行业数目
            'header_image_url' => Setting()->get('operate_header_image_url','http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/default/WechatIMG1.jpeg'),//资深专家推荐图片
            'recommend_qa' => $recommend_qa
        ];

        return self::createJsonData(true,$data);
    }
}