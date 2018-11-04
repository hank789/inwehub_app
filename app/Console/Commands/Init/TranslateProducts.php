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

class TranslateProducts extends Command
{
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:translate-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '翻译产品';

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
        $tags = Tag::where('category_id','>=',43)->where('summary','')->orderBy('id','desc')->get();
        foreach ($tags as $tag) {
            $slug = strtolower($tag->name);
            $slug = str_replace(' ','-',$slug);
            $slug = str_replace('.','-',$slug);
            $slug = str_replace('(','-',$slug);
            $slug = str_replace(')','-',$slug);
            $slug = str_replace(',','',$slug);
            $slug = str_replace('-&amp;','',$slug);
            $slug = str_replace('---;','-',$slug);
            $slug = str_replace('+;','-',$slug);

            $slug = trim($slug,'-');
            $url = 'https://www.g2crowd.com/products/'.$slug.'/details';
            $content = $this->ql->browser($url);
            $desc = $content->find('div.column.xlarge-8.xxlarge-9>div.row>div.xlarge-8.column>p')->eq(1)->text();
            if (empty($desc)) {
                $desc = $content->find('div.column.xlarge-7.xxlarge-8>p')->text();
                if (empty($desc)) {
                    $desc = $content->find('p.pt-half.product-show-description')->text();
                    //$desc = $content->find('div.column.large-8>p')->text();
                }
            }
            $this->info($slug.';'.$desc);
            if (empty($desc)) continue;
            $summary = Translate::instance()->translate($desc);
            $tag->summary = $summary;
            $tag->description = $desc;
            $tag->save();
        }

    }

}