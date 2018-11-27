<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2018/9/10 下午8:25
 * @email:    hank.HuiWang@gmail.com
 */

use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
use App\Traits\SubmitSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use QL\Ext\PhantomJs;
use QL\QueryList;

class GoogleNews extends Command {
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:google:news';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取google news';
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
        $urls = [
            51 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ3WmpSc0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'SAP'],//SAP global news
            50 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRFZ1YW5jU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Oracle'],//Oracle global news
            //49 => ['url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRFJ6ZGpRU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Microsoft'],//Microsoft global news
            48 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRE56WXpnU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'IBM'],//IBM global news
            47 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ5Y0RKakVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Accenture'],//Accenture global news
            46 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRGRpZEhJMUVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Salesforce'],//Salesforce global news
            45 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRE4yYkdzd0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Capgemini'],//Capgemini global news
            44 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREo2ZERreUVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'McKinsey'],//McKinsey global news
            43 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRE4zTURCd0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'BCG'],//BCG global news
            42 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNR3N5WjNRU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'KPMG'],//KPMG global news
            41 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREp6Y0daa0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Deloitte'],//Deloitte global news
        ];
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $category = Category::where('slug','channel_xwdt')->first();
        foreach ($urls as $group_id => $info) {
            $group = Group::find($group_id);
            if (!$group) {
                event(new ExceptionNotify('圈子['.$group_id.']不存在'));
                continue;
            }
            if ($group->audit_status != Group::AUDIT_STATUS_SUCCESS) {
                continue;
            }
            $this->info($info['url']);
            try {
                $list = $ql->browser($info['url'],false,[
                    '--proxy' => '127.0.0.1:1080',
                    '--proxy-type' => 'socks5'
                ])->rules([
                    'title' => ['a.ipQwMb.Q7tWef>span','text'],
                    'link'  => ['a.ipQwMb.Q7tWef','href'],
                    'author' => ['.KbnJ8','text'],
                    'dateTime' => ['time.WW6dff','datetime'],
                    'description' => ['p.HO8did.Baotjf','text'],
                    'image' => ['img.tvs3Id.dIH98c','src']
                ])->range('div.NiLAwe.R7GTQ.keNKEd.j7vNaf')->query()->getData();
                if (count($list) <= 0 || empty($list)) {
                    event(new ExceptionNotify('抓取'.$info['url'].'失败'));
                    break;
                }
                foreach ($list as &$item) {
                    $exist_submission_id = Redis::connection()->hget('voten:submission:url',$item['link']);
                    if ($exist_submission_id) continue;
                    $dateTime = trim(str_replace('seconds:','',trim($item['dateTime']??'')));
                    if ($dateTime) {
                        $dateTime = substr($dateTime,0,10);
                        if ($dateTime <= strtotime('-3 days')) continue;
                    }
                    sleep(1);
                    $this->info($item['title']);
                    try {
                        $urlHtml = curlShadowsocks('https://news.google.com/'.$item['link']);
                        $item['href'] = $ql->setHtml($urlHtml)->find('div.m2L3rb.eLNT1d')->children('a')->attr('href');
                        if ($item['image']) {
                            //图片本地化
                            $item['image'] = saveImgToCdn($item['image'],'submissions');
                        } else {
                            $item['image'] = 'https://cdn.inwehub.com/system/group_18@3x.png';
                        }
                        $item['title'] = formatHtml($item['title']);
                        $item['description'] = formatHtml($item['description']);
                        $data = [
                            'url'           => $item['href'],
                            'title'         => $item['title'],
                            'description'   => null,
                            'type'          => 'link',
                            'embed'         => null,
                            'img'           => $item['image']??'',
                            'thumbnail'     => null,
                            'providerName'  => $item['author'],
                            'publishedTime' => $dateTime,
                            'domain'        => domain($item['href']),
                        ];

                        $data['current_address_name'] = '';
                        $data['current_address_longitude'] = '';
                        $data['current_address_latitude'] = '';
                        $data['mentions'] = [];
                        $submission = Submission::create([
                            'title'         => $item['description'],
                            'slug'          => $this->slug($item['title']),
                            'type'          => 'link',
                            'category_name' => $category->name,
                            'category_id'   => $category->id,
                            'group_id'      => $group_id,
                            'public'        => $group->public,
                            'rate'          => firstRate(),
                            'status'        => 1,
                            'user_id'       => $info['author_id'],
                            'data'          => $data,
                            'views'         => 1,
                        ]);
                        Redis::connection()->hset('voten:submission:url',$item['link'], $submission->id);
                        Tag::multiAddByName($info['tags'],$submission,1);
                        if ($dateTime) {
                            $submission->created_at = date('Y-m-d H:i:s',$dateTime);
                            $submission->save();
                        }
                        dispatch((new NewSubmissionJob($submission->id,true)));
                    } catch (\Exception $e) {
                        app('sentry')->captureException($e,['url'=>'https://news.google.com/'.$item['link'],'title'=>$item['title']]);
                        sleep(5);
                    }
                }
            } catch (\Exception $e) {
                app('sentry')->captureException($e,['url'=>$info['url']]);
                sleep(5);
            }

        }
    }
}