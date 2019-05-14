<?php namespace App\Services\Hmac;

/**
 * Created by PhpStorm.
 * User: wanghui
 * Date: 15/11/6
 * Time: 上午11:48
 */
class Example
{


    /**
     * 客户端请求
     * @return array
     */
    public function request()
    {
        $data = array('name' => 'hank');
        $client = new Client('qinghua','dfdjskfjdjf3344444');
        return $client->request('http://hfq.yonglibao.com/user/list',$data);
    }

    /**
     * 服务端验证
     */
    public function response()
    {
        $server = new Server('qinghua','dfdjskfjdjf3344444');
        $result = $server->validate();
        switch($result['code']){
            case 200:
                echo '成功';
                break;
        }
    }
}