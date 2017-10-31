<?php namespace App\Console\Commands\Readhub;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Comment;
use App\Models\Readhub\Comment as ReadhubComment;
use Illuminate\Console\Command;

class SyncComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readhub:comment:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步阅读站的评论';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $comments = ReadhubComment::all();
        foreach ($comments as $comment) {
            $exist = Comment::where('source_id',$comment->id)->where('source_type',get_class($comment))->first();
            if (!$exist) {
                Comment::create(
                    [
                        'user_id'     => $comment->user_id,
                        'content'     => $comment->body,
                        'source_id'   => $comment->id,
                        'source_type' => get_class($comment),
                        'to_user_id'  => 0,
                        'status'      => 1,
                        'supports'    => 0
                    ]
                );
            }
        }
    }

}