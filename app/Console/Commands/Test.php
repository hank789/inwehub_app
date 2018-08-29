<?php

namespace App\Console\Commands;

use App\Models\RecommendRead;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Taggable;
use App\Services\BosonNLPService;
use Illuminate\Console\Command;


class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        WechatWenzhangInfo::where('topic_id','>',0)->update(['status'=>2]);
        WechatWenzhangInfo::where('topic_id','=',0)->update(['status'=>3]);
        $recommends = RecommendRead::get();
        foreach ($recommends as $recommend) {
            $tags = [];
            $taggables = Taggable::where('taggable_id',$recommend->id)->where('taggable_type',get_class($recommend))->get();
            foreach ($taggables as $taggable) {
                if (isset($tags[$taggable->tag_id])) {
                    $taggable->delete();
                } else {
                    $tags[$taggable->tag_id] = $taggable->tag_id;
                }
            }
        }
        return;
    }
}
