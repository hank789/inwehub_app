<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2018/9/10 下午8:25
 * @email:    hank.HuiWang@gmail.com
 */

use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\OperationNotify;
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
        $group_id = 0;
        $url1 = 'https://blogs.sap.com';
        $url2 = 'https://blogs.saphana.com/blog';
        $limitViews = 500;
        $limitDays = 7;
        $ql = QueryList::getInstance();
        $category = Category::where('slug','sap_blog')->first();

        if ($group_id) {
            $group = Group::find($group_id);
            if (!$group) {
                event(new ExceptionNotify('圈子['.$group_id.']不存在'));
                return;
            }
        }
        $count = 0;
        $totalViews = [];

        $this->info($url1);
        try {
            $page = 1;
            while (true) {
                $list = $ql->get($url1.'/page/'.$page.'/',[],['proxy' => 'socks5h://127.0.0.1:1080'])->rules([
                    'title' => ['div.dm-contentListItem__title>a','text'],
                    'link'  => ['div.dm-contentListItem__title>a','href'],
                    'author' => ['div.dm-user__heading>a','text'],
                    'dateTime' => ['span.dm-user__date','text'],
                    'description' => ['div.dm-content-list-item__text.dm-content-list-item__text--ellipsis','text'],
                    'image' => ['img.avatar.avatar-66.photo.avatar-default','src']
                ])->range('ul.dm-contentList>li')->query()->getData();
                $page++;
                $isBreak = false;
                if (count($list) <= 0 || empty($list)) {
                    if ($page <= 1) {
                        event(new ExceptionNotify('抓取'.$url1.'失败'));
                    }
                    break;
                }
                foreach ($list as $item) {
                    $exist_submission_id = Redis::connection()->hget('voten:submission:url', $item['link']);
                    if ($exist_submission_id) continue;
                    $dateTime = $item['dateTime'];
                    if ($dateTime) {
                        $dateTime = new DateTime($dateTime);
                        if ($dateTime->getTimestamp() <= strtotime('-'.$limitDays.' days')) {
                            $isBreak = true;
                            break;
                        }
                    } else {
                        event(new ExceptionNotify('未取到'.$url1.'发布日期'));
                        $isBreak = true;
                        break;
                    }

                    $count++;
                    $isBreak = false;
                    $views = $ql->get($item['link'],[],['proxy' => 'socks5h://127.0.0.1:1080'])->find('div.dm-contentHero__statistics>div.dm-contentHero__metadata>span.dm-contentHero__metadata--item')->eq(1)->text();
                    $views = trim(str_replace('Views','',$views));
                    $this->info($item['title'].';'.$views);
                    $totalViews[] = $views;
                    if ($views < $limitViews) continue;
                    sleep(1);
                    try {
                        if ($item['image']) {
                            //图片本地化
                            $item['image'] = saveImgToCdn($item['image'], 'submissions',false,false);
                        } else {
                            $item['image'] = 'https://cdn.inwehub.com/groups/2018/09/1537341872OLqcb91.png';
                        }
                        $item['title'] = formatHtml($item['title']);
                        $item['description'] = formatHtml(str_replace('Read More »','',$item['description']));

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
                            'sourceViews' => $views
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
                            'public' => $group_id?$group->public:1,
                            'rate' => firstRate(),
                            'status' => 1,
                            'user_id' => 2568,
                            'data' => $data,
                            'views' => 1,
                        ]);
                        Redis::connection()->hset('voten:submission:url', $item['link'], $submission->id);
                        Tag::multiAddByName('SAP', $submission, 1);
                        if ($dateTime) {
                            $submission->created_at = $dateTime;
                            $submission->save();
                        }
                        dispatch((new NewSubmissionJob($submission->id,true,'@pafa @conan_wuhao ')));
                    } catch (\Exception $e) {
                        app('sentry')->captureException($e, ['url' => $item['link'], 'title' => $item['title']]);
                        sleep(5);
                    }
                }
                if ($isBreak) break;
            }
            $fields = [];
            $fields[] = [
                'title'=>'阅读数',
                'value'=>implode(',',$totalViews)
            ];
            event(new OperationNotify('抓取['.$url1.']结束，总文章数:'.$count,$fields));
            $count = 0;
            $totalViews = [];
            $this->info($url2);
            $page = 1;
            while (true) {
                $list = $ql->get($url2.'/page/'.$page.'/',[],['proxy' => 'socks5h://127.0.0.1:1080'])->rules([
                    'title' => ['h2>a','text'],
                    'link'  => ['h2>a','href'],
                    'author' => ['p.posted>a','text'],
                    'dateTime' => ['p.posted','text'],
                    'description' => ['section>p','text'],
                    'image' => ['img.avatar.avatar-96.photo','src']
                ])->range('article.post-listing')->query()->getData();
                $page++;
                $isBreak = false;
                if (count($list) <= 0 || empty($list)) {
                    if ($page <= 1) {
                        event(new ExceptionNotify('抓取'.$url2.'失败'));
                    }
                    break;
                }
                foreach ($list as $item) {
                    $exist_submission_id = Redis::connection()->hget('voten:submission:url', $item['link']);
                    if ($exist_submission_id) continue;
                    $dateTime = str_replace('Posted by '.$item['author'].' on ','',$item['dateTime']);
                    $this->info($dateTime);
                    if ($dateTime) {
                        $dateTime = strtotime($dateTime);
                        if ($dateTime <= strtotime('-'.$limitDays.' days')) {
                            $isBreak = true;
                            break;
                        }
                    } else {
                        event(new ExceptionNotify('未取到'.$url2.'发布日期'));
                        $isBreak = true;
                        break;
                    }
                    $this->info($item['title']);
                    $count++;
                    $isBreak = false;
                    $views = $ql->get($item['link'],[],['proxy' => 'socks5h://127.0.0.1:1080'])->find('span.simple-pvc-views')->text();
                    $totalViews[] = $views;
                    if ($views < $limitViews) continue;
                    sleep(1);
                    try {
                        if ($item['image']) {
                            //图片本地化
                            $item['image'] = saveImgToCdn($item['image'], 'submissions',false,false);
                        }
                        if ($item['image'] == 'https://cdn.inwehub.com/system/group_18@3x.png') {
                            $item['image'] = '';
                        }
                        $item['title'] = formatHtml($item['title']);
                        $item['description'] = formatHtml($item['description']);
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
                            'public' => $group_id?$group->public:1,
                            'rate' => firstRate(),
                            'status' => 1,
                            'user_id' => 2568,
                            'data' => $data,
                            'views' => 1,
                        ]);
                        Redis::connection()->hset('voten:submission:url', $item['link'], $submission->id);
                        Tag::multiAddByName('SAP', $submission, 1);
                        if ($dateTime) {
                            $submission->created_at = date('Y-m-d H:i:s',$dateTime);
                            $submission->save();
                        }
                        dispatch((new NewSubmissionJob($submission->id,true,'@pafa @conan_wuhao ')));
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
        $fields = [];
        $fields[] = [
            'title'=>'阅读数',
            'value'=>implode(',',$totalViews)
        ];
        event(new OperationNotify('抓取['.$url2.']结束，总文章数:'.$count,$fields));
        //var_dump($count);
        //var_dump($totalViews);
    }
}