<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2018/9/10 下午8:25
 * @email:    hank.HuiWang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Submission;
use App\Traits\SubmitSubmission;
use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use QL\QueryList;

class WallstreetcnNews extends Command {
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wallstreetcn:news';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取wallstreetcn news';

    protected $ql;

    protected $apiBase = 'https://api-prod.wallstreetcn.com/apiv1/content/themes/stream/';

    protected $category;

    protected $articleCount;
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
        //https://wallstreetcn.com/themes/1004444
        $urls = [
            'group' => 37,
            'author'=> 2566,
            'url'   => [
                '1004444',
                '1005709',
                '1005712',
                '1005711',
                '1005919',
                '1005710',
                '1005688',
            ]
        ];
        $this->ql = QueryList::getInstance();
        $this->category = Category::where('slug','wallstreetcn')->first();
        foreach ($urls['url'] as $url) {
            $nextCursor = $this->doScraper($this->apiBase.$url.'?limit=20&cursor=&type=newest',$urls['group'],$urls['author']);
            while ($nextCursor) {
                $nextCursor = $this->doScraper($this->apiBase.$url.'?limit=20&cursor='.$nextCursor.'&type=newest',$urls['group'],$urls['author']);
            }
        }
        $url2 = [
            'group' => 38,
            'author'=> 2565,
            'url'   => [
                '1007034',
                '1004553',
                '1004667',
                '1007111'
            ]
        ];
        foreach ($url2['url'] as $url) {
            $nextCursor = $this->doScraper($this->apiBase.$url.'?limit=20&cursor=&type=newest',$url2['group'],$url2['author']);
            while ($nextCursor) {
                $nextCursor = $this->doScraper($this->apiBase.$url.'?limit=20&cursor='.$nextCursor.'&type=newest',$url2['group'],$url2['author']);
            }
        }
        event(new OperationNotify('抓取了华尔街见闻'.$this->articleCount.'篇文章'));
    }

    public function doScraper($url,$group_id,$author) {
        $data = $this->ql->get($url)->getHtml();
        $data = json_decode($data,true);
        $nextCursor = $data['data']['next_cursor'];
        foreach ($data['data']['items'] as $item) {
            if ($item['resource_type'] != 'article') continue;
            if ($item['resource']['is_priced']) continue;
            if (!$item['resource']['source_uri']) continue;
            if (str_contains($item['resource']['source_uri'],'https://wallstreetcn.com')) continue;
            if ($item['resource']['display_time'] <= strtotime('-1 day')) {
                $nextCursor = 0;
                break;
            };
            $exist_submission_id = Redis::connection()->hget('voten:submission:url', $item['resource']['source_uri']);
            if ($exist_submission_id) continue;
            $source_uri = $item['resource']['source_uri'];
            $arr = parse_url($source_uri);
            $url_query = parse_query($arr['query']);
            if (isset($url_query['target_uri']) && $url_query['target_uri']) {
                $source_uri = $url_query['target_uri'];
            }

            $this->info($item['resource']['title']);
            if ($item['resource']['image_uri']) {
                //图片本地化
                $item['resource']['image'] = saveImgToCdn($item['resource']['image_uri'], 'submissions',false,false);
            }
            if ($item['resource']['image'] == 'https://cdn.inwehub.com/system/group_18@3x.png') {
                $item['resource']['image'] = '';
            }
            $data = [
                'url' => $source_uri,
                'title' => $item['resource']['title'],
                'description' => null,
                'type' => 'link',
                'embed' => null,
                'img' => $item['resource']['image'],
                'thumbnail' => null,
                'providerName' => $item['resource']['source_name'],
                'publishedTime' => $item['resource']['display_time'],
                'domain' => domain($item['resource']['source_uri']),
            ];

            $data['current_address_name'] = '';
            $data['current_address_longitude'] = '';
            $data['current_address_latitude'] = '';
            $data['mentions'] = [];
            $submission = Submission::create([
                'title' => strlen($item['resource']['content_short'])<=3?$item['resource']['title']:$item['resource']['content_short'],
                'slug' => $this->slug($item['resource']['title']),
                'type' => 'link',
                'category_name' => $this->category->name,
                'category_id' => $this->category->id,
                'group_id' => 0,
                'public' => 1,
                'rate' => firstRate(),
                'status' => 1,
                'user_id' => $author,
                'data' => $data,
                'views' => 1,
            ]);
            $this->articleCount++;
            Redis::connection()->hset('voten:submission:url', $source_uri, $submission->id);
            dispatch((new NewSubmissionJob($submission->id,true,'华尔街见闻:')));
        }
        return $nextCursor;
    }
}