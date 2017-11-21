<?php namespace App\Console\Commands\Readhub;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Category;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Readhub\Bookmark;
use App\Models\Readhub\Category as ReadhubCategory;
use App\Models\Readhub\Submission as ReadhubSubmission;
use App\Models\Readhub\SubmissionUpvotes;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readhub:data:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'readhub的数据迁移到app';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::get();
        foreach ($users as $user) {
            $user->is_expert = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
            $user->save();
        }

        $readhub_categories = ReadhubCategory::get();
        $channel = Category::create([
            'parent_id' => 0,
            'grade' => 1,
            'name'  => '动态频道',
            'icon'  => null,
            'slug'  => 'channel',
            'type'  => 'tags,submissions',
            'sort'  => 0,
            'status' => 1
        ]);
        foreach ($readhub_categories as $readhub_category) {
            Category::create([
                'id' => $readhub_category->id + 40,
                'parent_id' => $channel->id,
                'grade' => 1,
                'name'  => $readhub_category->name,
                'icon'  => null,
                'slug'  => 'channel_'.app('pinyin')->abbr($readhub_category->name),
                'type'  => 'tags,submissions',
                'sort'  => 0,
                'status' => 1
            ]);
        }

        $readhub_submissions = ReadhubSubmission::get();
        foreach ($readhub_submissions as $readhub_submission) {
            $data = $readhub_submission->toArray();
            $data['category_id'] += 40;
            Submission::create($data);
        }
        $readhub_submission_bookmarks = Bookmark::where('bookmarkable_type','App\Submission')->get();
        foreach ($readhub_submission_bookmarks as $readhub_submission_bookmark) {
            Collection::create([
                'user_id' => $readhub_submission_bookmark->user_id,
                'source_id' => $readhub_submission_bookmark->bookmarkable_id,
                'source_type' => 'App\Models\Submission',
                'subject'  => ''
            ]);
        }
        $readhub_submission_upvotes = SubmissionUpvotes::get();
        foreach ($readhub_submission_upvotes as $readhub_submission_upvote) {
            Support::create([
                'user_id' => $readhub_submission_upvote->user_id,
                'supportable_id' => $readhub_submission_upvote->submission_id,
                'supportable_type' => 'App\Models\Submission'
            ]);
        }
        $readhub_comments = Comment::where('source_type','App\Models\Readhub\Comment')->get();
        foreach ($readhub_comments as $readhub_comment) {
            $readhub_comment->source_type = 'App\Models\Submission';
            $comment = \App\Models\Readhub\Comment::find($readhub_comment->source_id);
            $readhub_comment->source_id = $comment->submission_id;
            $readhub_comment->save();
        }
    }

}