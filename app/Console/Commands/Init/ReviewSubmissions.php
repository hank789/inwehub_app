<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Jobs\NewSubmissionJob;
use App\Models\Category;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\User;
use App\Services\Translate;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use QL\Ext\PhantomJs;
use QL\QueryList;
use App\Traits\SubmitSubmission;

class ReviewSubmissions extends Command
{
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:review-submissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化点评内容';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $tagRels = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->get();
        $role1 = Role::where('slug','operatorrobot')->first();
        $userIds = RoleUser::where('role_id',$role1->id)->pluck('user_id')->toArray();
        $authors = [];

        foreach ($tagRels as $tagRel) {
            $slug = RateLimiter::instance()->hGet('review-tags-url',$tagRel->tag_id);
            if (!$slug) {
                $this->info('tag:'.$tagRel->tag_id->id.',地址不存在');
                continue;
            }
            $tag = Tag::find($tagRel->tag_id);
            $page=1;
            $needBreak = false;
            while (true) {
                $data = $this->reviewData($slug,$page);
                if ($data->count() <= 0) {
                    sleep(5);
                    $data = $this->reviewData($slug,$page);
                }
                if ($data->count() <= 0 && $page == 1) {
                    $this->info('tag:'.$tagRel->tag_id.'，抓取点评失败');
                    break;
                }
                if ($data->count() <= 0) {
                    $this->info('tag:'.$tagRel->tag_id.'，无数据，page:'.$page);
                    break;
                }
                foreach ($data as $item) {
                    $link = RateLimiter::instance()->hGet('review-submission-url',$item['link']);
                    if ($link) continue;
                    $item['body'] = trim($item['body'],'"');
                    $item['body'] = trim($item['body']);
                    if (strlen($item['body']) <= 50) continue;
                    $this->info($item['link']);
                    RateLimiter::instance()->hSet('review-submission-url',$item['link'],1);
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
                        'status'        => config('app.env') == 'production'?0:1,
                        'user_id'       => config('app.env') == 'production'?504:$userIds[rand(0,count($userIds))],
                        'views'         => 1,
                        'created_at'    => date('Y-m-d H:i:s',strtotime($item['datetime'])),
                        'data' => [
                            'current_address_name' => '',
                            'current_address_longitude' => '',
                            'current_address_latitude' => '',
                            'category_ids' => [$tag->category_id],
                            'author_identity' => '',
                            'origin_author' => $item['name'],
                            'img' => []
                        ]
                    ]);
                    Tag::multiSaveByIds($tag->id,$submission);
                    if ($submission->status == 1) {
                        dispatch(new NewSubmissionJob($submission->id,true,'g2点评数据；'));
                    }
                    $authors[$item['name']][] = $submission->id;
                }
                if ($needBreak) break;
                $this->info('page:'.$page);
                //if (config('app.env') != 'production' && $page >= 2) break;
                $page++;
            }
        }

        Cache::forever('review_submissions',$authors);
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