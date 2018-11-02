<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
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
        $ql = QueryList::getInstance();
        $ql2 = new QueryList();
        $cookie = '_ga=GA1.2.502552747.1537344894; gr_user_id=92ec759a-4af4-4baf-9109-efb8b7dcd108; MEIQIA_EXTRA_TRACK_ID=5e7b329c28eb11e7afd102fa39e25136; acw_tc=781bad0715403439658774326e436f2214a955dd9ae51c5e12d8ec75aa7876; Hm_lvt_1c587ad486cdb6b962e94fc2002edf89=1540343972';
        $auth = 'bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3d3dy5pdGp1emkuY29tL2FwaS9hdXRob3JpemF0aW9ucyIsImlhdCI6MTU0MTE0MjkzMSwiZXhwIjoxNTQxMTUwMTMxLCJuYmYiOjE1NDExNDI5MzEsImp0aSI6IkptNGVJeUxUS0JsMmh4WEIiLCJzdWIiOjYzOTQyNiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.M0PdEo9vsqkihpEv5x243TiL_PNBG_jFjUwkcOKmuug';
        $headers = [
            'Host'    => 'www.itjuzi.com',
            'Origin'  => 'https://www.itjuzi.com',
            'Referer' => 'http://www.itjuzi.com/investevent',
            'Connection' => 'keep-alive',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Cookie'    => $cookie,
            'Authorization' => $auth
        ];

        $page = 1;
        while (true) {
            $requestUrl = 'https://www.itjuzi.com/api/investevents';
            $content = $ql->post($requestUrl,[
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

            $data = json_decode($content, true);

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
                        $content = $ql->get('https://www.itjuzi.com/api/investevents/'.$item['id'],null,[
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
                        $img = saveImgToCdn($result['data']['invse_with_com']['logo']);
                        $company_url = $result2['data']['basic']['com_url'];
                        $item['custom_data']['company_slogan'] = $result2['data']['basic']['com_slogan'];
                        $type = 'link';
                        if (strlen($company_url) <= 7) {
                            $type = 'text';
                            $img = [$img];
                        }

                        $title = date('n月d日',$item['time']).'，「'.$item['name'].'」获得金额'.$item['money'].'的'.$item['round'].'融资，投资方'.implode('，',array_column($item['investor'],'invst_name')).'。';
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
                            'user_id'       => $group->user_id,
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
                var_dump($content);
                event(new ExceptionNotify('抓取IT橘子企业服务信息失败:'.$data['msg']));
                return;
            }
        }
    }
}