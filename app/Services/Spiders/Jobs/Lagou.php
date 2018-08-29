<?php namespace App\Services\Spiders\Jobs;
/**
 * @author: wanghui
 * @date: 2018/8/28 下午2:10
 * @email: wanghui@yonglibao.com
 */

class Lagou {

    private $cookie;    //网站cookie
    private $retJson;	//返回的数据
    private $html;

    //分页列表页ajax请求接口
    private static $ajaxDataAPI = 'http://www.lagou.com/jobs/positionAjax.json?city=%E5%8C%97%E4%BA%AC';
    //拉勾网首页
    private static $indexPage   = 'http://www.lagou.com';
    //请求头
    private static $header = array(
        'User-Agent: Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: zh-CN,zh;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Cookie: user_trace_token=20171202100105-a80125c3-d704-11e7-9b87-5254005c3644; LGUID=20171202100105-a8012ec6-d704-11e7-9b87-5254005c3644; mds_login_authToken="HuQadT2xOmgTtzyrjEXWbCZBqF90Sm+utXdB10g7nB4ZV3kEiPRkZK4jCKBxlexgiXeToX0T5oUzcG3WaATPbPE/U3nkgSaGSaq/lk+spWiya6BTxgLlp3gGmyem8RmDsksemP9DQjIp7bhxxWM36FQp6QyihUj1Ix6/8BPa5d14rucJXOpldXhUiavxhcCELWDotJ+bmNVwmAvQCptcy5e7czUcjiQC32Lco44BMYXrQ+AIOfEccJKHpj0vJ+ngq/27aqj1hWq8tEPFFjdnxMSfKgAnjbIEAX3F9CIW8BSiMHYmPBt7FDDY0CCVFICHr2dp5gQVGvhfbqg7VzvNsw=="; X_HTTP_TOKEN=cc3f27fa5889092c3bc56e2c7e7bad7c; JSESSIONID=ABAAABAAAFCAAEG4234FBDA9A8263A9366071F51723D691; PRE_UTM=; PRE_HOST=; PRE_SITE=https%3A%2F%2Fwww.lagou.com%2Fzhaopin%2FPHP%2F7%2F%3FfilterOption%3D2; PRE_LAND=https%3A%2F%2Fwww.lagou.com%2Fjobs%2Flist_%3Fcity%3D%25E5%2585%25A8%25E5%259B%25BD%26cl%3Dfalse%26fromSearch%3Dtrue%26labelWords%3D%26suginput%3D; TG-TRACK-CODE=search_code; _putrc=C7768FD2791AD7E5; login=true; unick=%E5%88%98%E9%9D%9E%E5%87%A1; showExpriedIndex=1; showExpriedCompanyHome=1; showExpriedMyPublish=1; hasDeliver=0; SEARCH_ID=d4ca3a89328c49e38d9ea83b139c913e; index_location_city=%E5%85%A8%E5%9B%BD; _gat=1; _ga=GA1.2.272928687.1512180066; _gid=GA1.2.1427659055.1512180066; LGSID=20171202155045-81314b77-d735-11e7-bbd8-525400f775ce; LGRID=20171202160558-a13d7b61-d737-11e7-9b90-5254005c3644; Hm_lvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1512180066,1512181406,1512182491; Hm_lpvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1512201959',
        'Host: www.lagou.com',
        'Pragma: no-cache',
        'Upgrade-Insecure-Requests: 1',
    );
    private static $optionsCommon = array(
        CURLOPT_AUTOREFERER => 1, 		//当根据Location:重定向时，自动设置header中的Referer:信息
        CURLOPT_FOLLOWLOCATION => 1, 	//将服务器服务器返回的"Location: "放在header中递归的返回给服务器
        // CURLOPT_HEADER => 1, 			//将头文件的信息作为数据流输出
        CURLOPT_RETURNTRANSFER => 1     //将curl_exec() 获取的信息以文件流的形式返回，而不是直接输出。
    );
    public function getData(){
        $this->get(self::$indexPage, null);	//请求首页
        $this->getCookie($this->html);	    //请求cookie
        $retData = $this->post(self::$ajaxDataAPI,array('first'=>'true','pn'=>1,'kd'=>'php'));
        $retData = json_decode($retData);
        var_dump($retData);
        $totalPage = $retData->content->totalPageCount;
        if(empty($totalPage)) echo "没有相关数据";
        if(isset($retData->success) && ( $totalPage > 0)){
            for($i = 1; $i <= $totalPage; $i++){
                $tempData = $this->post(self::$ajaxDataAPI,array('first'=>'true','pn'=>$i,'kd'=>'php'));
                $tempData = json_decode($tempData);
                if(empty($tempData)){
                    echo "发生错误";
                    continue;
                }
                foreach ($tempData->content->result as $key => $value) {
                    $sql = "INSERT INTO company (company_id,company_name,finance_state,company_short_name,company_logo,position_name,city,industry_field,leader_name,positon_advantage,company_size,job_nature,salary,education,position_id,score,create_time) values(";
                    $sql .= "$value->companyId,'$value->companyName','$value->financeStage','$value->companyShortName','$value->companyLogo','$value->positionName','$value->city','$value->industryField','$value->leaderName','$value->positionAdvantage','$value->companySize','$value->jobNature','$value->salary','$value->education',$value->positionId,$value->score,'$value->createTime')";
                    echo $sql . "-------$i\n";
                    unset($sql);
                }
                unset($tempData);
            }
        }
        echo "采集完毕!";
    }
    //登录提交表单
    public function post($url, $field = array()){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$header);
        curl_setopt_array($ch, self::$optionsCommon);
        curl_setopt($ch, CURLOPT_POST, 1);	//设置为post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($field));
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');	//指定cookie文件的路径
        $this->html = curl_exec($ch);
        curl_close($ch); //关闭资源
        if ($this->html === false) {
            exit("POST失败");
        }
        return $this->html;
    }
    //获取登录页内容
    public function get($url, $cookie){
        $optionsCommon = array(
            CURLOPT_AUTOREFERER => 1, 		//当根据Location:重定向时，自动设置header中的Referer:信息
            CURLOPT_FOLLOWLOCATION => 1, 	//将服务器服务器返回的"Location: "放在header中递归的返回给服务器
            CURLOPT_HEADER => 1, 			//将头文件的信息作为数据流输出
            CURLOPT_RETURNTRANSFER => 1     //将curl_exec() 获取的信息以文件流的形式返回，而不是直接输出。
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$header); //设置请求头
        curl_setopt_array($ch, $optionsCommon);		 //设置参数
        if ($cookie !== null) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);		 //设置cookie
        }
        $this->html = curl_exec($ch);
        curl_close($ch);	//关闭资源
        if($this->html === false){
            exit("GET请求失败!\n");
        }
        return $this->html;
    }
    //正则取cookie
    private function getCookie($html){
        if (preg_match_all("/set\-cookie:([^\r\n]*)/i", $html, $matches)) {
            foreach ($matches[1] as $value) {
                $this->cookie .= $value;
            }
            file_put_contents("cookie.txt", $this->cookie);
        }
        return $this->cookie;
    }
}