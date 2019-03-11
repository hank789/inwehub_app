<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\Weapp\Tongji;
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
        try{
            $params = $this->data['params'];
            $event_id = 0;
            $scene = 0;
            if (isset($params['id'])) {
                $event_id = $params['id'];
            } elseif (isset($params['slug'])) {
                $submission = Submission::where('slug',$params['slug'])->first();
                $event_id = $submission->id;
            }
            if (isset($params['scene'])) {
                $scene =  $params['scene'];
            }
            if ($this->data['page'] == 'pages/url/url') {
                $url = parse_url($params['url']);
                if (in_array($url['host'],['api.ywhub.com','api.inwehub.com'])) {
                    $event_id = explode('/',$url['path'])[2];
                }
            }
            Tongji::create([
                'user_oauth_id' => $this->user_oauth_id,
                'start_time' => $this->data['start_time'],
                'end_time'   => $this->data['end_time'],
                'stay_time'  => $this->data['end_time']-$this->data['start_time'],
                'event_id'   => $event_id,
                'page'      => $this->data['page'],
                'scene'     => $scene
            ]);
        }catch (\Exception $e){
            app('sentry')->captureException($e);
        }
    }
}
