<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Models\Groups\Group;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Console\Command;

class InitGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:init:group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化圈子数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $group = Group::create([
            'user_id' => 79,
            'name'    => '小哈公社',
            'description' => '小哈的自留地，顾问的精神家园',
            'public'  => 1,
            'logo'    => 'https://cdn.inwehub.com/media/69/user_origin_79.jpg',
            'audit_status' => Group::AUDIT_STATUS_SUCCESS,
            'subscribers'  => 1
        ]);

        $submissions = Submission::get();
        $group->articles = $submissions->count();
        $group->save();
        foreach ($submissions as $submission) {
            $submission->views = $submission->upvotes * rand(5,10) + $submission->comments_number * rand(5,10);
            $submission->group_id = $group->id;
            $submission->save();
        }
        $questions = Question::get();
        foreach ($questions as $question) {
            $question->views = $question->answers()->sum('views');
            $question->save();
        }
    }

}