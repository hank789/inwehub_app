<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/19 下午4:27
 * @email:    hank.HuiWang@gmail.com
 */

class ItJuZi extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:itjuzi:news';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取IT橘子信息';

    protected $ql;

    protected $itjuzi_auth;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $group = Group::find(56);
        $category = Category::where('slug','company_invest')->first();
        $this->ql = QueryList::getInstance();
        $this->getItJuziAuth();

        $ql2 = new QueryList();
        $headers = [
            'Host'    => 'www.itjuzi.com',
            'Origin'  => 'https://www.itjuzi.com',
            'Referer' => 'http://www.itjuzi.com/investevent',
            'Connection' => 'keep-alive',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Authorization' => $this->itjuzi_auth
        ];

        $page = 1;
        while (true) {
            try {
                $data = $this->getListData($page,$headers);
            } catch (\Exception $e) {
                if ($e->getCode() == 400 || $e->getCode() == 445) {
                    RateLimiter::instance()->setVale('itjuzi','token','',60*60*24*5);
                    $this->getItJuziAuth();
                    $headers['Authorization'] = $this->itjuzi_auth;
                    $data = $this->getListData($page,$headers);
                } else {
                    app('sentry')->captureException($e);
                    return;
                }
            }

            if ($data['status'] == 'success') {
                $pageInfo = $data['data'];
                $pageTotal = $pageInfo['page']['total'];
                $page++;
                foreach ($pageInfo['data'] as $item) {
                    //7天前的数据不抓取，由于是按照时间倒序，所以只要出现一个小于7天的，下面的都是小于7天的
                    if (date('Y-m-d',$item['time']) < date('Y-m-d',strtotime('-7 days'))) return;
                    $guid = 'company_invest_'.$item['id'];
                    $company = Submission::where('slug',$guid)->withTrashed()->first();
                    if (!$company) {
                        $content = $this->ql->get('https://www.itjuzi.com/api/investevents/'.$item['id'],null,[
                            'timeout' => 10,
                            'headers' => $headers
                        ])->getHtml();
                        $result = json_decode($content,true);
                        if ($result['status'] != 'success') {
                            var_dump($item['id']);
                            event(new ExceptionNotify('抓取IT橘子企业详情失败:'.$item['id']));
                            continue;
                        }
                        $company_description = $result['data']['des'];
                        $item['custom_data']['company_summary'] = $result['data']['invse_with_com']['des'];
                        $content2 = $ql2->get('https://www.itjuzi.com/api/companies/'.$result['data']['invse_with_com']['com_id'].'?type=basic')->getHtml();
                        $result2 = json_decode($content2,true);
                        if ($result2['status'] != 'success') {
                            var_dump($item['id']);
                            event(new ExceptionNotify('抓取IT橘子企业详情失败:'.$item['id']));
                            continue;
                        }
                        $img = saveImgToCdn($result['data']['invse_with_com']['logo'],'submissions',false,false);
                        $company_url = $result2['data']['basic']['com_url'];
                        $item['custom_data']['company_slogan'] = $result2['data']['basic']['com_slogan'];
                        $type = 'link';
                        if (strlen($company_url) <= 7) {
                            $type = 'text';
                            $img = [$img];
                        }

                        $title = date('n月d日',$item['time']).'，「'.$item['name'].'」获得金额'.$item['money'].'的'.$item['round'].'融资，投资方'.implode('，',array_column($item['investor'],'name')).'。';
                        $this->info($title);
                        $data = [
                            'url'           => $company_url,
                            'title'         => $item['name'].'-'.$item['custom_data']['company_slogan'],
                            'description'   => formatHtml($company_description),
                            'type'          => $type,
                            'embed'         => null,
                            'img'           => $img,
                            'thumbnail'     => null,
                            'providerName'  => 'itjuzi.com',
                            'publishedTime' => date('Y-m-d',$item['time']),
                            'domain'        => domain($company_url),
                            'origin_data'   => $item
                        ];

                        $data['current_address_name'] = '';
                        $data['current_address_longitude'] = '';
                        $data['current_address_latitude'] = '';
                        $data['mentions'] = [];
                        $submission = Submission::create([
                            'title'         => $title.'<br><br>'.$company_description,
                            'slug'          => $guid,
                            'type'          => $type,
                            'category_name' => $category->name,
                            'category_id'   => $category->id,
                            'group_id'      => $group->id,
                            'public'        => $group->public,
                            'rate'          => firstRate(),
                            'status'        => 1,
                            'user_id'       => 2571,
                            'data'          => $data,
                            'views'         => 1,
                        ]);
                        Tag::multiAddByName('企业服务',$submission,1);
                        dispatch((new NewSubmissionJob($submission->id,true)));
                    }
                }
                if ($page >= 4) return;
                sleep(5);
            } else {
                var_dump($data);
                event(new ExceptionNotify('抓取IT橘子企业服务信息失败:'.$data['msg']));
                return;
            }
        }
    }

    protected function getListData($page, $headers) {
        $requestUrl = 'https://www.itjuzi.com/api/investevents';
        $content = $this->ql->post($requestUrl,[
            'city' => '',
            'equity_ratio' => '',
            'ipo_platform' => '',
            'page' => $page,
            'per_page' => 20,
            'prov' => '',
            'round' => '',
            'scope' => '企业服务',
            'selected' => '',
            'status' => '',
            'sub_scope' => '',
            'time' => '',
            'total'=>0,
            'type'=>1,
            'valuation'=>'',
            'valuations' =>''
        ],[
            'timeout' => 10,
            'headers' => $headers
        ])->getHtml();

        return json_decode($content, true);
    }

    protected function getItJuziAuth() {
        $itjuzi_auth = RateLimiter::instance()->getValue('itjuzi','token');
        if (!$itjuzi_auth) {
            $result = $this->ql->post('https://www.itjuzi.com/api/authorizations',[
                'account' => "wanghui198831@126.com",
                'password' => "Wanghui8831"
            ])->getHtml();
            $resultArr = json_decode($result,true);
            $itjuzi_auth = $resultArr['data']['token'];
            RateLimiter::instance()->setVale('itjuzi','token',$itjuzi_auth,60*60*24*5);
        }
        $this->itjuzi_auth = $itjuzi_auth;
    }
}