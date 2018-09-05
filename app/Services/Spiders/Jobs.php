<?php namespace App\Services\Spiders;
/**
 * @author: wanghui
 * @date: 2018/8/27 下午7:39
 * @email: hank.huiwang@gmail.com
 */

class Jobs {

    private $pageNo;
    private $keywords;



    /*
     * 拉钩网信息
     */
    private function lagou(){
        $url = "https://m.lagou.com/search.json?city=昆明&positionName={$this->keywords}&pageNo={$this->pageNo}&pageSize=15";
        $res = HttpGet($url);
        $data = json_decode($res, true)['content']['data']['page']['result'];
        foreach ($data as $k => $v){
            $data[$k]['url'] = "https://m.lagou.com/jobs/{$v['positionId']}.html";
        }
        return [
            'msg' => 'success',
            'code' => 1,
            'domain'=>'https://m.lagou.com',
            'usepageno'=>'use',
            'data' => $data
        ];
    }
    /*
     * 前程无忧信息
     */
    private function wuyou(){
        $url = "http://m.51job.com/search/joblist.php?jobarea=250200&keyword={$this->keywords}&keywordtype=2&pageno=".$this->pageNo;
        $res = HttpGet($url);
        \phpQuery::newDocumentHTML($res);
        $lists = pq('.items a');
        $data = array();
        $i = 0;
        foreach ($lists as $list){
            $jquery = pq($list);
            $data[$i]['positionName'] = $jquery->find('h3 span')->text();
            $data[$i]['city'] = $jquery->find('i')->text();
            $data[$i]['salary'] = $jquery->find('em')->text();
            $data[$i]['companyFullName'] = $jquery->find('aside')->text();
            $data[$i]['url'] = $jquery->attr('href');
            $i++;
        }
        return [
            'msg' => 'success',
            'code' => 1,
            'domain'=>'https://m.51job.com',
            'usepageno'=>'use',
            'data' => $data
        ];
    }
    /*
     * 智联招聘
     */
    private function zhilian(){
        $url = "https://m.zhaopin.com/kunming-831/?keyword={$this->keywords}&order=0&maprange=3&ishome=0&pageindex=".$this->pageNo;
        $res = HttpGet($url);
        \phpQuery::newDocumentHTML($res);
        $lists = pq('.positiolist section');
        $data = array();
        $i = 0;
        foreach ($lists as $list){
            $jquery = pq($list);
            $data[$i]['positionName'] = $jquery->find('.job-name')->text();
            $data[$i]['city'] = $jquery->find('.ads')->text();
            $data[$i]['salary'] = $jquery->find('.job-sal .fl')->text();
            $data[$i]['companyFullName'] = $jquery->find('.comp-name')->text();
            $data[$i]['createTime'] = $jquery->find('.time')->text();
            $data[$i]['url'] = 'https://m.zhaopin.com'.$jquery->find('a')->attr('data-link');
            $i++;
        }
        return [
            'msg' => 'success',
            'code' => 1,
            'domain'=>'https://m.zhaopin.com',
            'usepageno'=>'use',
            'data' => $data
        ];
    }
    /*
     * 猎聘网
     */
    private function liepin(){
        $url = "https://m.liepin.com/zhaopin/.".($this->pageNo <= 1?'':('pn'.($this->pageNo-1)).'/').".?keyword={$this->keywords}&industrys=000&dqs=310020";
        $res = HttpGet($url);
        \phpQuery::newDocumentHTML($res);
        $lists = pq('.job-card-wrap .job-card');
        $data = array();
        $i = 0;
        foreach ($lists as $list){
            $jquery = pq($list);
            $data[$i]['positionName'] = $jquery->find('.job-name .name-text')->text();
            $data[$i]['city'] = $jquery->find('ul li:last-child a')->text();
            $data[$i]['salary'] = $jquery->find('.flexbox .text-warning')->text();
            $data[$i]['companyFullName'] = $jquery->find('.company-name')->text();
            $data[$i]['createTime'] = $jquery->find('time')->text();
            $data[$i]['url'] = $jquery->find('.job-name')->attr('href');
            $i++;
        }
        return [
            'msg' => 'success',
            'code' => 1,
            'domain'=>'https://m.liepin.com',
            'usepageno'=>'disabled',
            'data' => $data
        ];
    }
    /*
     * boss直聘
     */
    private function boss(){
        $url = "https://m.zhipin.com/job_detail/?city=101290100&source=10&query={$this->keywords}";
        $res = HttpGet($url);
        \phpQuery::newDocumentHTML($res);
        $lists = pq('.job-list ul .item');
        $data = array();
        $i = 0;
        foreach ($lists as $list){
            $jquery = pq($list);
            $data[$i]['positionName'] = $jquery->find('.title h4')->text();
            $data[$i]['city'] = $jquery->find('.msg em:first-child')->text();
            $data[$i]['salary'] = $jquery->find('.salary')->text();
            $data[$i]['companyFullName'] = $jquery->find('.name')->text();
            $data[$i]['createTime'] = '';
            $data[$i]['url'] = 'https://m.zhipin.com'.$jquery->find('a')->attr('href');
            $i++;
        }
        return [
            'msg' => 'success',
            'code' => 1,
            'domain'=>'https://m.zhipin.com',
            'usepageno'=>'disabled',
            'data' => $data
        ];
    }
}