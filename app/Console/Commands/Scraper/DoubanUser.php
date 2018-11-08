<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\User;
use App\Services\RateLimiter;
use App\Services\Registrar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/19 下午4:27
 * @email:    hank.HuiWang@gmail.com
 */

class DoubanUser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:douban:user';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取豆瓣用户信息';

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
        $ignoreImgs = [
            'https://img1.doubanio.com/icon/user_normal.jpg',
            'https://img3.doubanio.com/icon/user_normal_f.jpg',
            'https://img3.doubanio.com/icon/u180623390-1.jpg'
        ];
        $offset = 0;
        $registrar = new Registrar();
        while (true) {
            $url = 'https://www.douban.com/group/sap/members?start='.$offset;
            $content = $this->ql->get($url);
            $list = $content->rules([
                'name' => ['div.name>a','text'],
                'logo' => ['div.pic>a>img','src']
            ])->range('div.member-list>ul>li')->query()->getData();
            if (count($list) <= 0) break;
            foreach ($list as $item) {
                if (!in_array($item['logo'],$ignoreImgs)) {
                    $this->info($item['name'].';'.$item['logo']);
                    $ignoreImgs[] = $item['logo'];
                    $user = $registrar->create([
                        'name' => $item['name'],
                        'email' => null,
                        'mobile' => null,
                        'rc_uid' => 0,
                        'title'  => '',
                        'company' => '',
                        'gender' => 0,
                        'password' => time(),
                        'status' => 0,
                        'visit_ip' => '127.0.0.1',
                        'source' => User::USER_SOURCE_DOUBAN,
                    ]);
                    $user->attachRole(7); //默认注册为普通用户角色
                    $user->userData->email_status = 1;
                    $user->userData->save();
                    $logo = saveImgToCdn($item['logo']);
                    if ($logo) {
                        $user->avatar = $logo;
                    }
                    $user->save();
                }
            }
            $offset+=35;
        }
        $this->info('finished');
    }
}