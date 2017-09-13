<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class CloseActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $article_id;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($article_id)
    {
        $this->article_id = $article_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $article = Article::find($this->article_id);
        if ($article && $article->deadline && $article->status == Article::ARTICLE_STATUS_ONLINE && time()>=strtotime($article->deadline)) {
            $article->status = Article::ARTICLE_STATUS_CLOSED;
            $article->save();
        }
    }
}
