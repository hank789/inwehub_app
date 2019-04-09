<?php

namespace App\Jobs;

use App\Models\ContentCollection;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\Weapp\Tongji;
use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class WeappActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    public $data;

    public $user_oauth_id;



    public function __construct($user_oauth_id, array $data)
    {
        $this->user_oauth_id = $user_oauth_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = $this->data['params'];
        $event_id = 0;
        $scene = 0;
        $parent_refer = 0;
        $from_oauth_id = 0;
        $product_id = 0;
        if (isset($params['id'])) {
            $event_id = $params['id'];
        } elseif (isset($params['slug'])) {
            $submission = Submission::where('slug',$params['slug'])->first();
            $event_id = $submission->id;
            $product_id = $submission->category_id;
        }
        if (isset($params['from_oauth_id'])) {
            $from_oauth_id = $params['from_oauth_id'];
        }
        if (isset($params['scene'])) {
            $scene =  urldecode($params['scene']);
            $sceneArr = explode('=',$scene);
            if (isset($sceneArr[2]) && $sceneArr[2] > 0) {
                $from_oauth_id = $sceneArr[2];
            }
        }

        if ($this->data['page'] == 'pages/majorProduct/majorProduct') {
            $product_id = $event_id;
        }

        if ($this->data['page'] == 'pages/url/url') {
            $url = parse_url($params['url']);
            if (in_array($url['host'],['api.ywhub.com','api.inwehub.com'])) {
                $event_id = explode('/',$url['path'])[2];
                $url_query = parse_query($url['query']);
                $parent_refer = $url_query['source']??0;
                if ($parent_refer) {
                    $parent_refer_arr = explode('_',$parent_refer);
                    if (isset($parent_refer_arr[0]) && $parent_refer_arr[0] == 'product') {
                        $product_id = $parent_refer_arr[1];
                    }
                }
            }
        }
        if ($this->data['page'] == 'pages/moreInfo/moreInfo') {
            $parent_refer = $params['type'];
            if ($parent_refer == 'product') {
                $product_id = $event_id;
            }
        }
        if ($this->data['page'] == 'pages/allDianping/allDianping') {
            $tag = Tag::getTagByName($params['name']);
            $event_id = $tag->id;
            $product_id = $tag->id;
        }
        if (in_array($this->data['page'],['pages/pdf/pdf','pages/video/video'])) {
            $case = ContentCollection::find($event_id);
            $parent_refer = $case->source_id;
            $product_id = $case->source_id;
        }
        Tongji::create([
            'user_oauth_id' => $this->user_oauth_id,
            'start_time' => $this->data['start_time'],
            'end_time'   => $this->data['end_time'],
            'stay_time'  => $this->data['end_time']-$this->data['start_time'],
            'event_id'   => $event_id,
            'page'      => $this->data['page'],
            'scene'     => $scene,
            'product_id' => $product_id,
            'parent_refer' => $parent_refer,
            'from_oauth_id' => $from_oauth_id
        ]);
    }
}
