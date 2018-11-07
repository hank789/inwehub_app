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
use QL\Ext\PhantomJs;
use QL\QueryList;
use App\Traits\SubmitSubmission;

class TagRoles extends Command
{
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:tag-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化角色标签';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $category = Category::where('slug','role')->first();
        if (!$category) {
            $category = Category::create([
                'parent_id' => 0,
                'grade'     => 0,
                'name'      => '用户角色',
                'icon'      => null,
                'slug'      => 'role',
                'type'      => 'role',
                'sort'      => 0,
                'status'    => 1
            ]);
        }
        $roles = [
            '终端用户',
            '管理人员',
            '公司高管',
            '内部顾问',
            '外部顾问',
            '乙方或原厂',
            '代理机构',
            '行业研究员',
        ];
        foreach ($roles as $role) {
            $tag = Tag::where('name',$role)->first();
            if (!$tag) {
                $tag = Tag::create([
                    'name' => $role,
                    'category_id' => $category->id,
                    'logo' => '',
                    'summary' => $role,
                    'description' => '',
                    'parent_id' => 0,
                    'reviews' => 0
                ]);
            }
            $tagRel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category->id)->first();
            if (!$tagRel) {
                TagCategoryRel::create([
                    'tag_id' => $tag->id,
                    'category_id' => $category->id,
                    'review_average_rate' => 0,
                    'review_rate_sum' => 0,
                    'reviews' => 0,
                    'type' => TagCategoryRel::TYPE_DEFAULT
                ]);
            }

        }
        $this->info('完成');

    }

}