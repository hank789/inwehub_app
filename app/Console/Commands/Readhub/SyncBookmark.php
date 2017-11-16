<?php namespace App\Console\Commands\Readhub;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Comment;
use App\Models\Readhub\Bookmark;
use App\Models\Readhub\Comment as ReadhubComment;
use Illuminate\Console\Command;

class SyncBookmark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readhub:bookmark:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步阅读站的收藏';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Bookmark::where('bookmarkable_type','App\Submission')->update(['bookmarkable_type'=>'App\Models\Readhub\Submission']);
    }

}