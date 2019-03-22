<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductInfoCache;
use App\Logic\TagsLogic;
use App\Logic\WilsonScoreNorm;
use App\Mail\DailySubscribe;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Company\CompanyData;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Scraper\BidInfo;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Services\Spiders\Wechat\MpAutoLogin;
use App\Services\Spiders\Wechat\MpSpider;
use App\Services\Spiders\Wechat\WechatSogouSpider;
use App\Services\Translate;
use App\Services\BosonNLPService;
use App\Services\MixpanelService;
use App\Services\QcloudService;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\WechatSpider;
use App\Traits\SubmitSubmission;
use GuzzleHttp\Exception\ConnectException;
use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PHPHtmlParser\Dom;
use QL\Ext\PhantomJs;
use QL\QueryList;
use QL\Services\HttpService;
use Stichoza\GoogleTranslate\TranslateClient;
use Excel;

class Test extends Command
{
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //更新产品信息缓存
        $ids = RateLimiter::instance()->hGetAll('product_pending_update_cache');
        if ($ids) {
            foreach ($ids as $key=>$val) {
                dispatch_now(new UpdateProductInfoCache($key));
                RateLimiter::instance()->hDel('product_pending_update_cache',$key);
            }
        }
        return;
        $spider2 = new WechatSogouSpider();
        $data = $spider2->getGzhInfo('irootech');
        var_dump($data);
        return;
        $ql = QueryList::getInstance();
        $url = 'https://mp.weixin.qq.com/s?src=11&timestamp=1551340802&ver=1455&signature=FS*1hUMfKPQ6rt9Tvwy65ouB60hOFt9QmIX5XQzjPXIEiFK8hCMfNSyT5plc2h8sWCZC0eVwYi39GfWnivjs1w6wTYAWTNep3ljGtJcOBGbeoo6d8vJ6aBr-0KxeAYmN&new=1';
        $s = getWechatUrlInfo($url, false,true);
        var_dump($s);
        return;
        $html = $ql->get($url);
        $aTitle = $html->find('h2#activity-name')->text();
        $aBody = $html->find('div#js_content')->html();
        $aAuthor = $html->find('a#js_name')->text();
        $wxHao = $html->find('span.profile_meta_value')->eq(0)->text();
        var_dump($wxHao);
        $body = $html->getHtml();
        $pattern = "/var\s+msg_cdn_url\s+=\s+([\s\S]*?);/is";
        preg_match($pattern, $body, $matchs);
        var_dump(trim($matchs[1],'"'));
        //Storage::disk('local')->put('attachments/test5.html',$html->getHtml());
        return;
        $groups = Group::where('audit_status',4)->get();
        foreach ($groups as $group) {
            GroupMember::where('group_id',$group->id)->delete();
            Submission::where('group_id',$group->id)->update(['group_id'=>1]);
            Feed::where('group_id',$group->id)->update(['group_id'=>1]);
            WechatMpInfo::where('group_id',$group->id)->update(['group_id'=>1]);
            Feeds::where('group_id',$group->id)->update(['group_id'=>1]);
        }
        return;
        $product_c = Category::where('slug','product_album')->first();
        $categories = [
            [
                'name' => '分析与商业智能_1',
                'slug' => 'product_album_business_intelligence_analytics_1',
                'type' => 'product_album'
            ],
            [
                'name' => 'CRM_1',
                'slug' => 'product_album_crm_1',
                'type' => 'product_album'
            ],
            [
                'name' => 'OA与协同_1',
                'slug' => 'product_album_oa_1',
                'type' => 'product_album'
            ],
            [
                'name' => '基础架构服务（laaS）_1',
                'slug' => 'product_album_laas_1',
                'type' => 'product_album'
            ],
            [
                'name' => 'PLM_1',
                'slug' => 'product_album_plm_1',
                'type' => 'product_album'
            ],
            [
                'name' => 'ERP软件_1',
                'slug' => 'product_album_erp_1',
                'type' => 'product_album'
            ],
            [
                'name' => 'HRMS_1',
                'slug' => 'product_album_hrms_1',
                'type' => 'product_album'
            ],
            [
                'name' => '表单调查_1',
                'slug' => 'product_album_form_1',
                'type' => 'product_album'
            ],
            [
                'name' => '舆情监控工具_1',
                'slug' => 'product_album_public_sentiment_monitoring_1',
                'type' => 'product_album'
            ]
        ];
        foreach ($categories as $category) {
            Category::create([
                'parent_id' => $product_c->id,
                'grade'     => 0,
                'name'      => $category['name'],
                'icon'      => 'https://cdn.inwehub.com/system/group_18@3x.png',
                'slug'      => $category['slug'],
                'type'      => $category['type'],
                'sort'      => 0,
                'status'    => 1
            ]);
        }
        return;
        $date = '2018-12-19';
        $begin = date('Y-m-d 00:00:00',strtotime($date));
        $end = date('Y-m-d 23:59:59',strtotime($date));
        $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->orderBy('rate','desc')->take(10)->get();
        $list = [];
        foreach ($recommends as $recommend) {
            $item = Submission::find($recommend->source_id);
            $domain = $item->data['domain']??'';
            $link_url = config('app.url').'/trackEmail/1/'.$recommend->id.'/';

            $img = $item->data['img']??'';
            if (is_array($img)) {
                if ($img) {
                    $img = $img[0];
                } else {
                    $img = '';
                }
            }
            $list[] = [
                'id'    => $item->id,
                'title' => strip_tags($item->data['title']??$item->title),
                'type'  => $item->type,
                'domain'    => $domain,
                'img'   => $img,
                'slug'      => $item->slug,
                'category_id' => $item->category_id,
                'is_upvoted'     => 0,
                'link_url'  => $link_url,
                'rate'  => (int)(substr($item->rate,8)?:0),
                'comment_number' => $item->comments_number,
                'support_number' => $item->upvotes,
                'share_number' => $item->share_number,
                'tags' => [],
                'created_at'=> (string)$item->created_at
            ];
        }
        Mail::to('hank.wang@inwehub.com')->send(new DailySubscribe('2019-01-01',1,$list));
        return;
        $tt = [
            'Googlebot1',
            'Baiduspider'
        ];
        $r = searchKeys('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',$tt,100);
        var_dump($r);
        return;
        $service = new MpAutoLogin();
        $service->setToken('');
        $service->init([
            'account' => 'fan.pang@inwehub.com',
            'password' => 'HW(CP8LJU/',
            'key' => 'wechatmp'
        ]);

        $spider = new MpSpider();
        $mpInfo = WechatMpInfo::where('status',1)->first();
        $wz_list = $spider->getGzhArticles($mpInfo);
        var_dump($wz_list);
        return;
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $url = 'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRE56WXpnU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US:en';
        $list = $ql->browser($url,false,[
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
        var_dump($list);
        return;
        Mail::to(['hank.wang@inwehub.com','wanghui198831@126.com'])->send(new DailySubscribe('你好'));
        return;
        $spider = new WechatSogouSpider();
        $spider->getGzhInfo('topitnews');
        return;
        // Get the QueryList instance
        $ql = QueryList::getInstance();
// Get the login form
        $form = $ql->get('https://github.com/login')->find('form');

// Fill in the GitHub username and password
        $form->find('input[name=login]')->val('hank789');
        $form->find('input[name=password]')->val('wanghui8831');

// Serialize the form data
        $fromData = $form->serializeArray();
        $postData = [];
        foreach ($fromData as $item) {
            $postData[$item['name']] = $item['value'];
        }

// Submit the login form
        $actionUrl = 'https://github.com'.$form->attr('action');
        $rs = $ql->post($actionUrl,$postData);
        //var_dump($rs->getHtml());
// To determine whether the login is successful
// echo $ql->getHtml();

        $userName = $ql->get('https://github.com/',null)->find('span.text-bold')->text();
        //Storage::disk('local')->put('attachments/test4.html',$userName);
        var_dump($userName);
        if($userName)
        {
            echo 'Login successful ! Welcome:'.$userName;
        }else{
            echo 'Login failed !';
        }
        return;
        for ($id=80683;$id<=80693;$id++) {
            $submission = Submission::find($id);
            $info = getUrlInfo($submission->data['url'],true);
            $data = $submission->data;
            $data['img'] = $info['img_url'];
            $submission->data = $data;
            $submission->save();
        }
        return;
        $urls = [
            51 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ3WmpSc0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'SAP'],//SAP global news
            50 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRFZ1YW5jU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Oracle'],//Oracle global news
            //49 => ['url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRFJ6ZGpRU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Microsoft'],//Microsoft global news
            48 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNRE56WXpnU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'IBM,企业服务'],//IBM global news
            47 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ5Y0RKakVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Accenture,咨询行业'],//Accenture global news
            46 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRGRpZEhJMUVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Salesforce,企业服务'],//Salesforce global news
            45 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRE4yYkdzd0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Capgemini,咨询行业'],//Capgemini global news
            44 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREo2ZERreUVnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'McKinsey,咨询行业'],//McKinsey global news
            43 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNRE4zTURCd0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'BCG,咨询行业'],//BCG global news
            42 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIQgKIhtDQkFTRGdvSUwyMHZNR3N5WjNRU0FtVnVLQUFQAQ?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'KPMG,咨询行业'],//KPMG global news
            41 => ['author_id'=>2568,'url'=>'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREp6Y0daa0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen','tags'=>'Deloitte,咨询行业'],//Deloitte global news
        ];
        foreach ($urls as $group_id => $info) {
            $submissions = Submission::where('group_id',$group_id)->where('status',1)->where('type','link')->get();
            $tt = explode(',',$info['tags']);
            foreach ($submissions as $submission) {
                $tags = $submission->tags->pluck('name')->toArray();
                foreach ($tt as $t) {
                    if (in_array($t,$tags)) {
                        $submission->group_id = 0;
                    } elseif ($submission->group_id == 0) {
                        Tag::multiAddByName($t,$submission,37);
                    }
                }
                if ($submission->group_id == 0) {
                    $submission->public = 1;
                    $submission->save();
                    $this->info($submission->id);
                }
            }
        }
        return;
        $mps = WechatMpInfo::where('status',1)->where('group_id',0)->get();
        foreach ($mps as $mp) {
            $regionTags = $mp->tags->pluck('id')->toArray();
            if ($regionTags) {
                $articles = WechatWenzhangInfo::where('mp_id',$mp->id)->where('source_type',1)->where('topic_id','>',0)->get();
                foreach ($articles as $article) {
                    $submission = Submission::find($article->topic_id);
                    if ($submission) {
                        $this->info($submission->id);
                        $submission->group_id = 0;
                        $submission->public = 1;
                        $submission->save();
                        Tag::multiAddByIds($regionTags,$submission);
                    }
                }
            }
        }
        $feeds = Feeds::where('status',1)->where('group_id',0)->get();
        foreach ($feeds as $feed) {
            $regionTags = $feed->tags->pluck('id')->toArray();
            if ($regionTags) {
                $articles = WechatWenzhangInfo::where('mp_id',$feed->id)->where('source_type',2)->where('topic_id','>',0)->get();
                foreach ($articles as $article) {
                    $submission = Submission::find($article->topic_id);
                    if ($submission) {
                        $this->info($submission->id);
                        $submission->group_id = 0;
                        $submission->public = 1;
                        $submission->save();
                        Tag::multiAddByIds($regionTags,$submission);
                    }
                }
            }
        }
        return;
        $content = '用在公司app“Inwehub”上，用于根据用户输入的公司名字取得公司的经纬度和位置信息，并显示用户附近的企业';
        $res = TagsLogic::getRegionTags($content);
        var_dump($res);
        return;
        $this->ql = QueryList::getInstance();
        $headers = [
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Referer' => 'https://www.newrank.cn/public/info/detail.html?account=fesco-bj',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'upgrade-insecure-requests' => 1,
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'cookie' => 'tt_token=true; rmbuser=true; name=15050368286; useLoginAccount=true; token=5BD61E8AE52C44858E3F9A847A5418C9; __root_domain_v=.newrank.cn;'
        ];
        $result = $this->ql->get('https://www.newrank.cn/public/info/detail.html',[
            'account' => 'xinhuashefabu1'
        ],[
            'timeout' => 10,
            'headers' => $headers
        ])->getHtml();
        $pattern = "/var\s+fgkcdg\s+=\s+(\{[\s\S]*?\});/is";
        preg_match($pattern, $result, $matchs);
        $matchs[1] = formatHtml($matchs[1]);
        var_dump($matchs[1]);
        $data = json_decode($matchs[1],true);
        var_dump($data);
        //Storage::disk('local')->put('attachments/test5.html',$result);
        return;
        $url = 'https://cdn.inwehub.com/demand/qrcode/2018/09/153733792816zoTjw.png';
        $logo = 'https://cdn.inwehub.com/tags/2018/11/QCwQdgZz5bfe458c5e535.png';
        $fUrl = weapp_qrcode_replace_logo($url,$logo);
        var_dump($fUrl);
        return;
        $keys = Redis::connection()->keys('inwehub:group-daily-hot-*');
        if ($keys) Redis::connection()->del($keys);
        return;
        $keys = RateLimiter::instance()->hGetAll('tag_pending_translate');
        foreach ($keys as $id=>$v) {
            $this->info($id);
            try {
                $tag = Tag::find($id);
                $tag->summary = Translate::instance()->translate($tag->description);
                $tag->save();
                RateLimiter::instance()->hDel('tag_pending_translate',$id);
            } catch (\Exception $e) {
                $m = $e->getMessage();
                $this->error($m);
                if (!str_contains($m,'413 Request Entity Too Large')) {
                    return;
                }
            }
        }
        $this->info('finish');
        return;
        $companies = CompanyData::get();
        foreach ($companies as $company) {
            if (empty($company->logo)) {
                $this->info($company->name);
                $company->logo = 'https://cdn.inwehub.com/system/company_default.png';
                $company->save();
            }
        }
        return;
        $items = RateLimiter::instance()->sMembers('default_product_logo');
        $data = file_get_contents('https://cdn.inwehub.com/tags/2018/11/15423122375Wtb3YC.png');
        $data1 = file_get_contents('https://cdn.inwehub.com/tags/2018/11/15412595109Mjhz0q.png');
        $data2 = file_get_contents('https://cdn.inwehub.com/tags/2018/11/1542308337clNpDMS.png');
        $list = [$data,$data1,$data2];
        $page = 1;
        $query = TagCategoryRel::where('type',1)->orderBy('id','desc');
        $tags = $query->simplePaginate(100,['*'],'page',$page);
        while ($tags->count() > 0) {
            foreach ($tags as $tag) {
                $item = Tag::find($tag->tag_id);
                if ($item->logo == 'https://cdn.inwehub.com/system/product_default.png') continue;
                try {
                    $logo = file_get_contents($item->logo);
                    if (in_array($logo,$list)) {
                        $this->info($item->id);
                        RateLimiter::instance()->sAdd('default_product_logo',$item->id,60*60*24);
                    }
                } catch (\Exception $e) {
                    $this->info($item->id.';'.$e->getMessage());
                    RateLimiter::instance()->sAdd('default_product_logo',$item->id,60*60*24);
                }

            }
            $page ++;
            $tags = $query->simplePaginate(100,['*'],'page',$page);
        }
        return;
        $arr = [3,3,3];
        $arr1 = [4,2,3];
        $t = varianceCalc($arr);
        $t1 = varianceCalc($arr1);
        var_dump(WilsonScoreNorm::instance($t['average'],count($arr))->score());
        var_dump(WilsonScoreNorm::instance($t1['average'],count($arr1))->score());

        return;
        $t = array_unique($s);
        foreach ($t as $item) {
            $c = Category::where('name',$item)->first();
            if (!$c) {
                $this->info($item);
            }
        }
        return;
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $url = 'https://www.g2crowd.com/categories/sales';
        $html = $this->ql->browser($url);
        $data = $html->rules([
            'name' => ['a','text'],
            'link' => ['a','href']
        ])->range('div.paper.mb-2>div')->query()->getData();
        var_dump($data);
        return;
        //抓取g2所有产品分类
        $url = 'https://www.g2crowd.com/categories?category_type=service';
        $html = $this->ql->browser($url);
        $data = $html->rules([
            'name' => ['h4.color-secondary','text'],
            'list' => ['h4~div.ml-2','html']
        ])->range('div.newspaper-columns__list-item.pb-1')->query()->getData(function ($item) {
            if (!isset($item['list'])) return $item;
            $item['list'] = $this->ql->html($item['list'])->rules([
                'name' => ['a.text-medium','text'],
                'link' => ['a.text-medium','href']
            ])->range('')->query()->getData();
            return $item;
        });
        var_dump($data);
        Storage::disk('local')->put('attachments/test5.html',json_encode($data));
        return;
        $page = 1;
        Submission::where('id','>=',1)->searchable();
        $submissions = Submission::where('type','review')->simplePaginate(100,['*'],'page',$page);
        while ($submissions->count() > 0) {
            foreach ($submissions as $submission) {
                $title = str_replace("”","",$submission->title);
                $title = str_replace("“","",$title);
                $title = str_replace("“< BR>","\n",$title);
                $title = str_replace("< BR>","\n",$title);
                $title = str_replace("amp;","",$title);
                if ($title != $submission->title) {
                    $submission->title = $title;
                    $submission->save();
                }
            }
            $this->info($page);
            $page++;
            $submissions = Submission::where('type','review')->simplePaginate(100,['*'],'page',$page);
        }

        return;
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $slug = '/products/salesforce-crm/reviews';
        $tag = Tag::find(39739);
        $page=1;
        $needBreak = false;
        while (true) {
            $data = $this->reviewData($slug,$page);
            if ($data->count() <= 0) {
                sleep(5);
                $data = $this->reviewData($slug,$page);
            }
            if ($data->count() <= 0 && $page == 1) {
                $this->info('tag:抓取点评失败');
                break;
            }
            if ($data->count() <= 0) {
                $this->info('tag:无数据，page:'.$page);
                break;
            }
            foreach ($data as $item) {
                $item['body'] = trim($item['body']);
                $item['body'] = trim($item['body'],'"');
                $item['body'] = trim($item['body']);
                if (strlen($item['body']) <= 50) continue;
                $this->info($item['link']);
                RateLimiter::instance()->hSet('review-submission-url',$item['link'],1);

                $sslug = app('pinyin')->abbr(strip_tags($item['body']));
                if (empty($sslug)) {
                    $sslug = 1;
                }
                if (strlen($sslug) > 50) {
                    $sslug = substr($sslug,0,50);
                }

                $submission = Submission::withTrashed()->where('slug', $sslug)->first();
                if ($submission) {
                    if (!isset($submission->data['origin_title'])) {
                        $this->info($submission->id);
                        $title = Translate::instance()->translate($item['body']);
                        $sdata = $submission->data;
                        $sdata['origin_title'] = $item['body'];
                        $submission->data = $sdata;
                        $submission->title = $title;
                        $submission->save();
                    }
                    continue;
                }

                preg_match('/\d+/',$item['star'],$rate_star);
                $title = $item['body'];
                if (config('app.env') == 'production' || $page <= 1) {
                    $title = Translate::instance()->translate($item['body']);
                }
                $submission = Submission::create([
                    'title'         => $title,
                    'slug'          => $this->slug($item['body']),
                    'type'          => 'review',
                    'category_id'   => $tag->id,
                    'group_id'      => 0,
                    'public'        => 1,
                    'rate'          => firstRate(),
                    'rate_star'     => $rate_star[0]/2,
                    'hide'          => 0,
                    'status'        => 0,
                    'user_id'       => 504,
                    'views'         => 1,
                    'created_at'    => date('Y-m-d H:i:s',strtotime($item['datetime'])),
                    'data' => [
                        'current_address_name' => '',
                        'current_address_longitude' => '',
                        'current_address_latitude' => '',
                        'category_ids' => [$tag->category_id],
                        'author_identity' => '',
                        'origin_author' => $item['name'],
                        'origin_title'  => $item['body'],
                        'img' => []
                    ]
                ]);
                Tag::multiSaveByIds($tag->id,$submission);
                $authors[$item['name']][] = $submission->id;
            }
            if ($needBreak) break;
            $this->info('page:'.$page);
            $page++;
        }
        return;

        $submissions = Submission::where('type','review')->where('id','<=',19332)->get();
        foreach ($submissions as $submission) {
            $submission->title = Translate::instance()->translate($submission->data['origin_title']);
            $submission->save();
        }
        return;
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $html = $ql->browser('https://www.g2crowd.com/products/salesforce-crm/reviews?page=1')->rules([
            'name' => ['div.font-weight-bold.mt-half.mb-4th','text'],
            'link' => ['a.pjax','href'],
            'star' => ['div.stars.large','class'],
            'datetime' => ['time','datetime'],
            'body' => ['div.d-f:gt(0)>.f-1','text']
        ])->range('div.mb-2.border-bottom')->query()->getData();
        Storage::disk('local')->put('attachments/test4.html',json_encode($html));
        var_dump($html);
        return;
        TagCategoryRel::sum('reviews');
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $tags = Tag::where('category_id','>=',43)->where('summary','')->get();
        foreach ($tags as $tag) {
            $slug = strtolower($tag->name);
            $slug = str_replace(' ','-',$slug);
            $url = 'https://www.g2crowd.com/products/'.$slug.'/details';
            $content = $ql->browser($url);
            $desc = $content->find('div.column.xlarge-8.xxlarge-9>div.row>div.xlarge-8.column>p')->eq(1)->text();
            if (empty($desc)) {
                $desc = $content->find('div.column.xlarge-7.xxlarge-8>p')->text();
                if (empty($desc)) {
                    $desc = $content->find('p.pt-half.product-show-description')->text();
                    //$desc = $content->find('div.column.large-8>p')->text();
                }
            }
            if (empty($desc)) continue;
            $summary = Translate::instance()->translate($desc);
            $tag->summary = $summary;
            $tag->description = $desc;
            $tag->save();
        }
        return;
        Translate::instance()->translate('hello');
        return;
        $tr = new TranslateClient('en', 'zh',['proxy'=>'socks5h://127.0.0.1:1080']);
        $en = $tr->translate('Salesforce helps businesses of all sizes accelerate sales, automate tasks and make smarter decisions so you can grow your business faster. Salesforce CRM offers: - Lead & Contact Management - Sales Opportunity Management - Workflow Rules & Automation - Customizable Reports & Dashboards - Mobile Application');
        var_dump($en);
        return;
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $content = $ql->browser('https://www.g2crowd.com/categories/crm',false,[
            '--proxy' => '127.0.0.1:1080',
            '--proxy-type' => 'socks5'
        ])->getHtml();
        //$company_description = $content->find('meta[name=Description]')->content;
        var_dump($content);
        //Storage::disk('local')->put('attachments/test4.html',$content);
        return;
        $ql = QueryList::getInstance();
        $cookies = Setting()->get('scraper_jianyu360_cookie','');
        $cookiesPcArr = explode('||',$cookies);
        $content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
            'pageNumber' => 1,
            'reqType' => 'bidSearch',
            'searchvalue' => 'SAP',
            'area' => '',
            'subtype' => '',
            'publishtime' => '',
            'selectType' => 'all',
            'minprice' => '',
            'maxprice' => '',
            'industry' => '',
            'tabularflag' => 'Y'
        ],[
            'timeout' => 60,
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => $cookiesPcArr[4]
            ]
        ])->getHtml();
        var_dump($content);
        return;
        $submissions = Submission::whereIn('group_id',[56])->get();
        foreach ($submissions as $submission) {
            Taggable::where('taggable_id',$submission->id)->where('taggable_type',get_class($submission))->update(['is_display'=>0]);
        }
        return;
        $domain = 'sogou';
        $members = RateLimiter::instance()->sMembers('proxy_ips_deleted_'.$domain);
        foreach ($members as $member) {
            deleteProxyIp($member,$domain);
        }
        return;
        $info['url'] = 'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ3WmpSc0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen';
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $html = curlShadowsocks('https://news.google.com/articles/CBMiWmh0dHBzOi8vd3d3LnRoZXJlZ2lzdGVyLmNvLnVrLzIwMTgvMDkvMTMvc2FwX3NvdXRoX2FmcmljYV9wcm9iZV9jb3JydXB0aW9uX3dhdGVyX21pbmlzdHJ5L9IBAA?hl=en-US&gl=US&ceid=US%3Aen');

        $item['href'] = $ql->setHtml($html)->find('div.m2L3rb.eLNT1d')->children('a')->attr('href');
        var_dump($item['href']);
        return;

        $list = $ql->get($info['url'],[],[
            'proxy' => 'socks5h://127.0.0.1:1080',
        ])->rules([
            'title' => ['a.ipQwMb.Q7tWef>span','text'],
            'link'  => ['a.ipQwMb.Q7tWef','href'],
            'author' => ['.KbnJ8','text'],
            'dateTime' => ['time.WW6dff','datetime'],
            'description' => ['p.HO8did.Baotjf','text'],
            'image' => ['img.tvs3Id.dIH98c','src']
        ])->range('div.NiLAwe.y6IFtc.R7GTQ.keNKEd.j7vNaf.nID9nc')->query()->getData();

        foreach ($list as &$item) {
            sleep(1);
            $item['href'] = $ql->get('https://news.google.com/' . $item['link'], [], [
                'proxy' => 'socks5h://127.0.0.1:1080',
            ])->find('div.m2L3rb.eLNT1d')->children('a')->attrs('href');
        }
        var_dump($list);
        Storage::disk('local')->put('attachments/test4.html',json_encode($list));
        return;
        // Get the QueryList instance
        $ql = QueryList::getInstance();
// Get the login form
        $form = $ql->get('https://github.com/login')->find('form');

// Fill in the GitHub username and password
        $form->find('input[name=login]')->val('hank789');
        $form->find('input[name=password]')->val('wanghui8831');

// Serialize the form data
        $fromData = $form->serializeArray();
        $postData = [];
        foreach ($fromData as $item) {
            $postData[$item['name']] = $item['value'];
        }

// Submit the login form
        $actionUrl = 'https://github.com'.$form->attr('action');
        $rs = $ql->post($actionUrl,$postData);
        //var_dump($rs->getHtml());
// To determine whether the login is successful
// echo $ql->getHtml();

        $userName = $ql->get('https://github.com/')->find('span.text-bold')->text();
        //Storage::disk('local')->put('attachments/test4.html',$userName);
        var_dump($userName);
        if($userName)
        {
            echo 'Login successful ! Welcome:'.$userName;
        }else{
            echo 'Login failed !';
        }
        return;
        $wechat = new WechatSpider();
        $mp = WechatMpInfo::find(4);
        $items = $wechat->getGzhArticles($mp);
        var_dump($items);
        return;
        /*$sUrl = 'https://m.lagou.com/search.json?city=%E5%85%A8%E5%9B%BD&positionName=sap&pageNo=1&pageSize=15';
        $aHeader = [
            'Accept: application/json',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'Cookie: _ga=GA1.2.845934384.1535426841; user_trace_token=20180828112721-465c1caa-aa72-11e8-b24b-5254005c3644; LGUID=20180828112721-465c2202-aa72-11e8-b24b-5254005c3644; index_location_city=%E5%85%A8%E5%9B%BD; JSESSIONID=ABAAABAAAGCABCCD28DF8209A7B49B1E86DFDDA7FC4CB8F; _ga=GA1.3.845934384.1535426841; fromsite="zhihu.hank.com:8080"; utm_source=""; _gid=GA1.2.1118280405.1535619468; Hm_lvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535455700,1535455777,1535455805,1535626070; _gat=1; LGSID=20180831103210-0fb55e88-acc6-11e8-be55-525400f775ce; PRE_UTM=; PRE_HOST=; PRE_SITE=; PRE_LAND=https%3A%2F%2Fwww.lagou.com%2F; LGRID=20180831103238-207ec83e-acc6-11e8-b30a-5254005c3644; Hm_lpvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535682758',
            'Host: m.lagou.com',
            'Referer: https://m.lagou.com/search.html',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
            'X-Requested-With: XMLHttpRequest'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
        $sResult = curl_exec($ch);

        curl_close($ch);
        $s = json_decode($sResult,true);
        var_dump($s);*/
        $ql = QueryList::getInstance();
        $opts = [
            //Set the timeout time in seconds
            'timeout' => 10,
            'headers' => [
                'Host'   => 'weixin.sogou.com',
            ]
        ];
        $content = $ql->get('http://mp.weixin.qq.com/profile?src=3&timestamp=1536830900&ver=1&signature=NKQVmha9HAVDZdnvcqm2poIuSypgNmHb4Z8rZ8UUdwhtLSyUv2LnpneWG8ovrr7FjSoKABpEexJ7puIjcgQ-eA==',null,$opts);
        //var_dump($content->getHtml());
        return;



        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);
        //$ips = getProxyIps();
        $ips = ['139.217.24.50:3128'=>1];
        foreach ($ips as $ip=>$score) {
            $content = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($cookiesAppArr,$ip){
                //$r->setMethod('POST');
                $r->setUrl('https://www.jianyu360.com/jyapp/article/content/ABCY2EAfTIvJyksJFZhcHUJJzACHj1mZnB%2FKA4gPy43eFJzfzNUCZM%3D.html');
                /*$r->setRequestData([
                    'keywords' => '',
                    'publishtime' => '',
                    'timeslot' => '',
                    'area' => '',
                    'subtype' => '',
                    'minprice' => '',
                    'maxprice' => '',
                    'industry' => '',
                    'selectType' => 'title'
                ]);*/
                //$r->setTimeout(10000); // 10 seconds
                //$r->setDelay(3); // 3 seconds
                //$r->addHeader('Cookie','UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371');
                $r->setHeaders([
                    'Host'   => 'www.jianyu360.com',
                    'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Cookie' => $cookiesAppArr[0]
                ]);
                return $r;
            },false,[
                '--proxy' => $ip,
                '--proxy-type' => 'http'
            ]);
            $source_url = $content->find('a.original')->href;
            var_dump($source_url);
            $bid_html_body = $content->removeHead()->getHtml();
            if ($bid_html_body == '<html></html>') {
                var_dump($ip);
            }
            sleep(3);
        }
        return;


        // 安装时需要设置PhantomJS二进制文件路径
        //$ql->use(PhantomJs::class,config('services.phantomjs.path'));
        //$h = file_get_contents(storage_path().'/app/attachments/test3.html');
        //$ql->html($h);

        //$bid_html_body = $ql->removeHead()->getHtml();
        //$dom = new Dom();
        //$dom->load($bid_html_body);
        //$html = $dom->find('pre#h_content');
        //var_dump((string)$html);
        //return;
        //use Shadowsocks
        $content = $ql->browser('https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ5Y0RKakVnSmxiaWdBUAE',false,[
            '--proxy' => '127.0.0.1:1080',
            '--proxy-type' => 'socks5'
            //'proxy' => 'socks5h://127.0.0.1:1080',
        ])->rules([
            'title' => ['a.ipQwMb.Q7tWef>span','text'],
            'link'  => ['a.ipQwMb.Q7tWef','href'],
            'author' => ['.KbnJ8','text'],
            'description' => ['p.HO8did.Baotjf','text'],
            'image' => ['img.tvs3Id.dIH98c','src']
        ])->range('div.NiLAwe.y6IFtc.R7GTQ.keNKEd.j7vNaf.nID9nc')->query()->getData();
        var_dump($content);
        //Storage::disk('local')->put('attachments/test4.html',$content);
        return;
        $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
            'pageNumber' => 2,
            'pageType' => ''
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;
        /*$content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
            'pageNumber' => 1,
            'reqType' => 'bidSearch',
            'searchvalue' => '系统',
            'area' => '',
            'subtype' => '',
            'publishtime' => '',
            'selectType' => 'title',
            'minprice' => '',
            'maxprice' => '',
            'industry' => '',
            'tabularflag' => 'Y'
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;*/
        //$ql = QueryList::get('https://www.lagou.com/jobs/list_前端?labelWords=&fromSearch=true&suginput=');
        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);
        $content = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($cookiesAppArr){
            //$r->setMethod('POST');
            $r->setUrl('https://www.jianyu360.com/jyapp/article/content/ABCY2EAfTIvJyksJFZhcHUJJzACHj1mZnB%2FKA4gPy43eFJzfzNUCZM%3D.html');
            /*$r->setRequestData([
                'keywords' => '',
                'publishtime' => '',
                'timeslot' => '',
                'area' => '',
                'subtype' => '',
                'minprice' => '',
                'maxprice' => '',
                'industry' => '',
                'selectType' => 'title'
            ]);*/
            //$r->setTimeout(10000); // 10 seconds
            //$r->setDelay(3); // 3 seconds
            //$r->addHeader('Cookie','UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371');
            $r->setHeaders([
                'Host'   => 'www.jianyu360.com',
                'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Cookie' => $cookiesAppArr[0]
            ]);
            return $r;
        },false,[
            '--proxy' => 'http://89.22.175.42:8080',
            '--proxy-type' => 'http'
        ]);
        $source_url = $content->find('a.original')->href;
        var_dump($source_url);
        $bid_html_body = $content->removeHead()->getHtml();
        var_dump($bid_html_body);
        $dom = new Dom();
        $dom->load($bid_html_body);
        $html = $dom->find('pre#h_content');
        var_dump($html->__toString());
        //$content = $ql->browser('http://36kr.com/p/5151347.html?ktm_source=feed')->find('link[href*=.ico]')->href;
        var_dump($source_url);
        //var_dump($bid_html_body);

        //Storage::disk('local')->put('attachments/test1.html',$content);
        return;
    }

    public function getHtmlData($i) {
        if ($i == 4) return $i;
        return null;
    }

    protected function reviewData($slug,$page) {
        $html = $this->ql->browser('https://www.g2crowd.com'.$slug.'?page='.$page)->rules([
            'name' => ['div.font-weight-bold.mt-half.mb-4th','text'],
            'link' => ['a.pjax','href'],
            'star' => ['div.stars.large','class'],
            'datetime' => ['time','datetime'],
            'body' => ['div.d-f:gt(0)>.f-1','text']
        ])->range('div.mb-2.border-bottom')->query()->getData();
        return $html;
    }
}
