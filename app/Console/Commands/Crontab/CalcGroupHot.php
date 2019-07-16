<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Http\Controllers\Admin\IndexController;
use App\Models\Comment;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\Submission;
use App\Models\Support;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use Illuminate\Console\Command;

class CalcGroupHot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:calc-group-hot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算圈子热度';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get();
        //删除昨日
        RateLimiter::instance()->del('group-daily-hot-'.date('Ymd',strtotime('-1 day')));
        foreach ($groups as $group) {
            //当日圈子分享点赞数
            $submissionIds = Submission::where('group_id',$group->id)->pluck('id')->toArray();
            $upvotes = Support::whereIn('supportable_id',$submissionIds)
                ->where('supportable_type',Submission::class)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();
            //当天圈子回复数
            $comments = Comment::whereIn('source_id',$submissionIds)
                ->where('source_type',Submission::class)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();
            //当日圈子分享数
            $submissions = Submission::where('group_id',$group->id)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();
            //当日圈子新增成员数
            $members = GroupMember::where('group_id',$group->id)
                ->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();
            //当日群聊数
            $messages = 0;
            $room = Room::where('r_type',2)
                ->where('source_id',$group->id)
                ->where('source_type',Group::class)
                ->where('status',Room::STATUS_OPEN)->first();
            if ($room) {
                $messages = MessageRoom::where('room_id',$room->id)->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])->count();
            }
            $score = $upvotes + $comments + $submissions + $members + $messages;
            RateLimiter::instance()->zAdd('group-daily-hot-'.date('Ymd'),$score,$group->id);
        }
        //今日热门产品
        RateLimiter::instance()->del('product-daily-hot-'.date('Ymd',strtotime('-1 day')));
        $products = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->where('reviews','>=',1)->get();
        foreach ($products as $product) {
            //当日点评数
            $submissions = Submission::where('category_id',$product->tag_id)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();
            //当日点赞数
            $submissionIds = Submission::where('category_id',$product->tag_id)->pluck('id')->toArray();
            $upvotes = Support::whereIn('supportable_id',$submissionIds)
                ->where('supportable_type',Submission::class)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
                ->count();

            $score = $submissions + $upvotes;
            RateLimiter::instance()->zAdd('product-daily-hot-'.date('Ymd'),$score,$product->tag_id);
        }
        $controller = new IndexController();
        $controller->index();
    }

}