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
use QL\QueryList;
use DateTime;

class SapNews extends Command {
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:sap:news';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取sap news';
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
        $group_id = 51;
        $url1 = 'https://blogs.sap.com';
        $url2 = 'https://blogs.saphana.com/blog';
        $limitViews = 500;
        $ql = QueryList::getInstance();
        $category = Category::where('slug','sap_blog')->first();

        $group = Group::find($group_id);
        if (!$group) {
            event(new ExceptionNotify('圈子['.$group_id.']不存在'));
            return;
        }
        $count = 0;
        $totalViews = [];

        $this->info($url1);
        try {
            $page = 1;
            while (true) {
                $list = $ql->get($url1.'/page/'.$page.'/',[],['proxy' => 'socks5h://127.0.0.1:1080'])->rules([
                    'title' => ['h2.entry-title>a','text'],
                    'link'  => ['h2.entry-title>a','href'],
                    'author' => ['span.by-author.vcard.profile>a.url.fn.n','text'],
                    'dateTime' => ['time.entry-date','datetime'],
                    'description' => ['div.entry-summary','text'],
                    'image' => ['img.avatar.avatar-66.photo.avatar-default','src']
                ])->range('article.post.type-post.status-publish.format-standard.hentry')->query()->getData();
                $page++;
                $isBreak = false;
                if (count($list) <= 0 || empty($list)) break;
                foreach ($list as $item) {
                    $exist_submission_id = Redis::connection()->hget('voten:submission:url', $item['link']);
                    if ($exist_submission_id) continue;
                    $dateTime = $item['dateTime'];
                    if ($dateTime) {
                        $dateTime = new DateTime($dateTime);
                        if ($dateTime->getTimestamp() <= strtotime('-3 days')) {
                            $isBreak = true;
                            break;
                        }
                    }
                    $this->info($item['title']);
                    $count++;
                    $isBreak = false;
                    $views = $ql->get($item['link'],[],['proxy' => 'socks5h://127.0.0.1:1080'])->find('div.entry-title.single>span.blog-date-info')->eq(1)->text();
                    $views = trim(str_replace('Views','',$views));
                    if ($views < $limitViews) continue;
                    $totalViews[] = $views;
                    sleep(1);
                    try {
                        if ($item['image']) {
                            //图片本地化
                            $item['image'] = saveImgToCdn($item['image'], 'submissions');
                        } else {
                            $item['image'] = 'https://cdn.inwehub.com/groups/2018/09/1537341872OLqcb91.png';
                        }
                        $data = [
                            'url' => $item['link'],
                            'title' => $item['title'],
                            'description' => null,
                            'type' => 'link',
                            'embed' => null,
                            'img' => $item['image'],
                            'thumbnail' => null,
                            'providerName' => $item['author'],
                            'publishedTime' => $dateTime->getTimestamp(),
                            'domain' => domain($item['link']),
                        ];

                        $data['current_address_name'] = '';
                        $data['current_address_longitude'] = '';
                        $data['current_address_latitude'] = '';
                        $data['mentions'] = [];
                        $submission = Submission::create([
                            'title' => $item['description'],
                            'slug' => $this->slug($item['title']),
                            'type' => 'link',
                            'category_name' => $category->name,
                            'category_id' => $category->id,
                            'group_id' => $group_id,
                            'public' => $group->public,
                            'rate' => firstRate(),
                            'status' => 1,
                            'user_id' => $group->user_id,
                            'data' => $data,
                            'views' => 1,
                        ]);
                        Redis::connection()->hset('voten:submission:url', $item['link'], $submission->id);
                        Tag::multiAddByName('SAP', $submission, 1);
                        if ($dateTime) {
                            $submission->created_at = $dateTime;
                            $submission->save();
                        }
                        dispatch((new NewSubmissionJob($submission->id,true,'@pafa ')));
                    } catch (\Exception $e) {
                        app('sentry')->captureException($e, ['url' => $item['link'], 'title' => $item['title']]);
                        sleep(5);
                    }
                }
                if ($isBreak) break;
            }
            $this->info($url2);
            $page = 1;
            while (true) {
                $list = $ql->get($url2.'/page/'.$page.'/',[],['proxy' => 'socks5h://127.0.0.1:1080'])->rules([
                    'title' => ['h2>a','text'],
                    'link'  => ['h2>a','href'],
                    'author' => ['p.posted>a','text'],
                    'dateTime' => ['time','text'],
                    'description' => ['section>p','text'],
                    'image' => ['img.avatar.avatar-96.photo','src']
                ])->range('article.post-listing')->query()->getData();
                $page++;
                $isBreak = false;
                if (count($list) <= 0 || empty($list)) break;
                foreach ($list as $item) {
                    $exist_submission_id = Redis::connection()->hget('voten:submission:url', $item['link']);
                    if ($exist_submission_id) continue;
                    $dateTime = $item['dateTime'];
                    if ($dateTime) {
                        $dateTime = strtotime($dateTime);
                        if ($dateTime <= strtotime('-3 days')) {
                            $isBreak = true;
                            break;
                        }
                    }
                    $this->info($item['title']);
                    $count++;
                    $isBreak = false;
                    $views = $ql->get($item['link'],[],['proxy' => 'socks5h://127.0.0.1:1080'])->find('span.simple-pvc-views')->text();
                    if ($views < $limitViews) continue;
                    $totalViews[] = $views;
                    sleep(1);
                    try {
                        if ($item['image']) {
                            //图片本地化
                            $item['image'] = saveImgToCdn($item['image'], 'submissions');
                        }
                        $data = [
                            'url' => $item['link'],
                            'title' => $item['title'],
                            'description' => null,
                            'type' => 'link',
                            'embed' => null,
                            'img' => $item['image'] ?? '',
                            'thumbnail' => null,
                            'providerName' => $item['author'],
                            'publishedTime' => $dateTime,
                            'domain' => domain($item['link']),
                        ];

                        $data['current_address_name'] = '';
                        $data['current_address_longitude'] = '';
                        $data['current_address_latitude'] = '';
                        $data['mentions'] = [];
                        $submission = Submission::create([
                            'title' => $item['description'],
                            'slug' => $this->slug($item['title']),
                            'type' => 'link',
                            'category_name' => $category->name,
                            'category_id' => $category->id,
                            'group_id' => $group_id,
                            'public' => $group->public,
                            'rate' => firstRate(),
                            'status' => 1,
                            'user_id' => $group->user_id,
                            'data' => $data,
                            'views' => 1,
                        ]);
                        Redis::connection()->hset('voten:submission:url', $item['link'], $submission->id);
                        Tag::multiAddByName('SAP', $submission, 1);
                        if ($dateTime) {
                            $submission->created_at = date('Y-m-d H:i:s',$dateTime);
                            $submission->save();
                        }
                        dispatch((new NewSubmissionJob($submission->id,true,'@pafa ')));
                    } catch (\Exception $e) {
                        app('sentry')->captureException($e, ['url' => $item['link'], 'title' => $item['title']]);
                        sleep(5);
                    }
                }
                if ($isBreak) break;
            }

        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            sleep(5);
        }
        //var_dump($count);
        //var_dump($totalViews);
    }
}