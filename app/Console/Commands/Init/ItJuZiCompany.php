<?php namespace App\Console\Commands\Init;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/19 下午4:27
 * @email:    hank.HuiWang@gmail.com
 */

class ItJuZiCompany extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:scraper:itjuzi:company';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取IT橘子公司信息';

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
        $this->ql = QueryList::getInstance();
        $this->getItJuziAuth();
        $categories = [
            '人力资源' => 88,
            'IT基础设施'=>95,
            'CRM'=> 47,
            '企业安全' => 113,
            '财务税务' => 59,
            '行业信息化及解决方案' => 99,
            '办公OA' => 79,
            '销售营销' => 45,
            '法律服务' => 109,
            '综合企业服务' => 115,
            '其他企业服务' => 115,
            '数据服务' => 109,
            '客户服务' => 52,
            'B2D开发者服务' => 100,
            '黑科技及前沿技术' => 115
        ];

        $rounds = [
            "B轮",
            "B+轮",
            "C轮",
            "C+轮",
            "D轮",
            "D+轮",
            "E轮",
            "F轮-上市前",
            "已上市",
            "新三板"
        ];
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

        foreach ($rounds as $round) {
            $this->info($round);
            $page = 1;
            while (true) {
                try {
                    $data = $this->getListData($page,$headers,$round);
                } catch (\Exception $e) {
                    if ($e->getCode() == 400) {
                        $this->getItJuziAuth();
                        $data = $this->getListData($page,$headers,$round);
                    } else {
                        app('sentry')->captureException($e);
                        return;
                    }
                }

                if ($data['status'] == 'success') {
                    $pageInfo = $data['data'];
                    $pageTotal = ceil($pageInfo['page']['total']/20);
                    $page++;
                    foreach ($pageInfo['data'] as $item) {
                        $this->info($item['name']);
                        $tag = Tag::where('name',$item['name'])->first();
                        $category_id = isset($categories[$item['sub_scope']])?$categories[$item['sub_scope']]:115;
                        if(!$tag) {
                            $tag = Tag::create([
                                'name' => $item['name'],
                                'category_id' => $category_id,
                                'logo' => saveImgToCdn($item['logo'],'tags'),
                                'summary' => $item['des'],
                                'description' => $item['slogan'],
                                'parent_id' => 0,
                                'reviews' => 0
                            ]);
                        }

                        $tagRel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category_id)->first();
                        if (!$tagRel) {
                            TagCategoryRel::create([
                                'tag_id' => $tag->id,
                                'category_id' => $category_id,
                                'review_average_rate' => 0,
                                'review_rate_sum' => 0,
                                'reviews' => 0,
                                'type' => TagCategoryRel::TYPE_REVIEW
                            ]);
                        }
                    }
                    $this->info($page);
                    if ($page > $pageTotal) break;
                    sleep(5);
                } else {
                    var_dump($data);
                    event(new ExceptionNotify('抓取IT橘子企业服务信息失败:'.$data['msg']));
                    return;
                }
            }
        }
    }

    protected function getListData($page, $headers, $round) {
        $requestUrl = 'https://www.itjuzi.com/api/companys';
        $content = $this->ql->post($requestUrl,[
            'city' => '',
            'page' => $page,
            'per_page' => 20,
            'prov' => '',
            'round' => $round,
            'scope' => '企业服务',
            'selected' => '',
            'sort' => '',
            'status' => '',
            'sub_scope' => '',
            'total'=>0,
        ],[
            'timeout' => 10,
            'headers' => $headers,
            'proxy' => 'socks5h://127.0.0.1:1080'
        ])->getHtml();

        return json_decode($content, true);
    }

    protected function getItJuziAuth() {
        $itjuzi_auth = RateLimiter::instance()->getValue('itjuzi','token');
        if (!$itjuzi_auth) {
            $result = $this->ql->post('https://www.itjuzi.com/api/authorizations',[
                'account' => "wanghui198831@126.com",
                'password' => "Wanghui8831"
            ],['proxy' => 'socks5h://127.0.0.1:1080'])->getHtml();
            $resultArr = json_decode($result,true);
            $itjuzi_auth = $resultArr['data']['token'];
        }
        $this->itjuzi_auth = $itjuzi_auth;
    }
}