<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Scraper\Jobs;
use App\Models\Submission;
use App\Models\Tag;
use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/19 下午4:27
 * @email:    hank.HuiWang@gmail.com
 */

class Indeed extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:indeed:jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取Indeed招聘信息';
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
        $keywords = [
            63 => 'SAP',
            64 => '管理咨询',
            65 => 'CIO'
        ];
        $baseUrl = 'https://cn.indeed.com';
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $limit = 50;
        $allowDate = ['最新发布','今日发布','1天前','2天前','3天前','4天前','5天前'];
        for ($i=1;$i<=24;$i++) {
            $allowDate[] = $i.'小时前';
        }
        $count = 0;
        foreach ($keywords as $groupId=>$keyword) {
            $this->info($keyword);
            $offset = 0;
            while (true) {
                $this->info($offset);
                urlencode()
                $requestUrl = $baseUrl.'/jobs?q=title%3A'.urlencode($keyword).'&jt=fulltime&sort=date&limit='.$limit.'&sr=directhire&radius=0&start='.$offset;
                $isBreak = false;
                $content = $ql->browser($requestUrl)->rules([
                    'title' => ['h2.jobtitle>a','text'],
                    'uuid'  => ['h2.jobtitle','id'],
                    'link'  => ['h2.jobtitle>a','href'],
                    'company' => ['span.company','text'],
                    'city' => ['span.location','text'],
                    'dateTime' => ['span.date','text'],
                    'summary' => ['span.summary','text']
                ])->range('div.row.result.clickcard')->query()->getData();
                if (count($content) <= 0 || empty($content)) break;
                foreach ($content as $item) {
                    $this->info($item['dateTime'].';'.$item['title']);
                    if (!str_contains($item['title'],$keyword) && !str_contains($item['title'],strtolower($keyword))) continue;
                    if (!in_array($item['dateTime'],$allowDate)) {
                        $isBreak = true;
                        break;
                    }
                    $queryParams = parse_query(parse_url($baseUrl.$item['link'])['query']);
                    if (!isset($queryParams['jk'])) continue;
                    $uuid = trim($item['uuid'],'jl_');
                    if (Jobs::where('guid',$uuid)->first()) continue;
                    $detailUrl = $baseUrl.'/rc/clk?jk='.$uuid.'&from=vj&pos=bottom';
                    sleep(1);
                    $count ++;
                    $ch = curl_init();
                    $headers = [];
                    $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6';
                    $headers[] = 'Cache-Control: no-cache';
                    $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
                    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_URL, $detailUrl);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

                    //通过代理访问需要额外添加的参数项
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
                    curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
                    curl_setopt($ch, CURLOPT_PROXYPORT, "1080");

                    curl_exec($ch);
                    $headers = curl_getinfo($ch);
                    curl_close($ch);
                    $link = $headers['url'];
                    Jobs::create([
                        'guid'  => $uuid,
                        'title' => $item['title'],
                        'city'  => $item['city'],
                        'source_url' => $link,
                        'tags' => $keyword,
                        'company' => $item['company'],
                        'summary' => $item['summary'],
                        'group_id' => $groupId,
                        'status' => 1
                    ]);
                }
                $offset += $limit;
                if ($isBreak) break;
                if ($offset >= 3000) break;
            }
        }
        $total = Jobs::where('status',1)->count();
        event(new SystemNotify('新抓取'.$count.'条(共'.$total.'条)招聘信息，请及时去后台处理',[]));
    }
}