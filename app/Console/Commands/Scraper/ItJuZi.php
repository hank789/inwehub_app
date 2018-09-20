<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use QL\Ext\PhantomJs;
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
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $cookie = '_ga=GA1.2.502552747.1537344894; _gid=GA1.2.209525726.1537344894; gr_user_id=92ec759a-4af4-4baf-9109-efb8b7dcd108; Hm_lvt_1c587ad486cdb6b962e94fc2002edf89=1537344894; acw_tc=781bad2315373449752386213e3da25a5aa0d609ed41ec04989654db6a835d; identity=hank.wang%40inwehub.com; remember_code=%2F2sadyUZtH; unique_token=639426; Hm_lvt_80ec13defd46fe15d2c2dcf90450d14b=1537345185; MEIQIA_EXTRA_TRACK_ID=5e7b329c28eb11e7afd102fa39e25136; session=e38827c47a6682e8f8f091f4d514ade9318a3dee; user-radar.itjuzi.com=%7B%22n%22%3A%22%5Cu6854%5Cu53cb913f8e96bfe431%22%2C%22v%22%3A2%7D; Hm_lpvt_80ec13defd46fe15d2c2dcf90450d14b=1537411689; MEIQIA_VISIT_ID=1ASEdpEbDmRuEYcguA1WLfSf4ae; Hm_lpvt_1c587ad486cdb6b962e94fc2002edf89=1537411703; gr_session_id_eee5a46c52000d401f969f4535bdaa78=0837d6b3-e50a-43d8-9872-fb63088f8a3a; gr_cs1_0837d6b3-e50a-43d8-9872-fb63088f8a3a=user_id%3A639426; gr_session_id_eee5a46c52000d401f969f4535bdaa78_0837d6b3-e50a-43d8-9872-fb63088f8a3a=true';
        $headers = [
            'Host'    => 'radar.itjuzi.com',
            'Referer' => 'http://radar.itjuzi.com/investevent',
            'Connection' => 'keep-alive',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'X-Requested-With' => 'XMLHttpRequest',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Cookie'    => $cookie
        ];

        $page = 1;
        while (true) {
            $requestUrl = 'http://radar.itjuzi.com/investevent/info?location=in&orderby=def&page='.$page.'&scope=126';
            $content = $ql->get($requestUrl,null,[
                'timeout' => 10,
                'headers' => $headers
            ])->getHtml();

            $data = json_decode($content, true);
            if ($data['status'] == 1) {
                $pageInfo = $data['data'];
                $pageTotal = $pageInfo['page_total'];
                $page++;
                foreach ($pageInfo['rows'] as $item) {
                    //7天前的数据不抓取，由于是按照时间倒序，所以只要出现一个小于7天的，下面的都是小于7天的
                    if ($item['date'] < date('Y-m-d',strtotime('-7 days'))) return;
                    $guid = 'company_invest_'.$item['com_id'].'_'.$item['invse_id'];
                    $company = Submission::where('slug',$guid)->first();
                    if (!$company) {
                        $content = $ql->browser('https://www.itjuzi.com/company/'.$item['com_id']);
                        $company_url = $content->find('div.link-line>a')->eq(2)->href;
                        $item['custom_data']['company_slogan'] = $content->find('h2.seo-slogan')->html();
                        $item['custom_data']['company_summary'] = $content->find('span.scope.c-gray-aset')->html();
                        $company_description = $content->find('div.block>div.summary')->eq(1)->html();

                        $title = '「'.$item['com_name'].'」于'.date('Y年n月d日',strtotime($item['date'])).'获得投资方'.implode(',',array_column($item['invsest_with'],'invst_name')).$item['money'].$item['currency'].'的'.$item['round'].'融资。';
                        $this->info($title);
                        $data = [
                            'url'           => $company_url,
                            'title'         => $item['com_name'].'-'.$item['custom_data']['company_slogan'],
                            'description'   => $company_description,
                            'type'          => 'link',
                            'embed'         => null,
                            'img'           => saveImgToCdn($item['com_logo']),
                            'thumbnail'     => null,
                            'providerName'  => 'itjuzi.com',
                            'publishedTime' => $item['date'],
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
                            'type'          => 'link',
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
                        Tag::multiAddByName('企业服务',$submission);
                        dispatch((new NewSubmissionJob($submission->id)));
                    }
                }
                if ($page > $pageTotal) return;
                sleep(5);
            } else {
                var_dump($content);
                event(new SystemNotify('抓取IT橘子企业服务信息失败:'.$data['msg']));
                return;
            }
        }
    }
}