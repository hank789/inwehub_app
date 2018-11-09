<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Models\RecommendRead;
use App\Models\User;
use App\Services\MixpanelService;
use Illuminate\Console\Command;

class DailyReadReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:report:daily:read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '阅读统计报告';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = MixpanelService::instance()->request(['events'],[
            'event' => ['inwehub:discover_detail','inwehub:ask-offer-answers','inwehub:ask-offer-detail','inwehub:read_page_detail','inwehub:dianping-add','inwehub:ask','inwehub:discover_add'],
            'type'  => 'general',
            'unit'  => 'day',
            'interval' => 1
        ]);
        $today = date('Y-m-d');
        $current = $data['data']['values']["inwehub:discover_detail"][$today] + $data['data']['values']["inwehub:ask-offer-answers"][$today] +
            $data['data']['values']["inwehub:ask-offer-detail"][$today] + $data['data']['values']["inwehub:read_page_detail"][$today];
        event(new OperationNotify('今日总阅读数：'.$current));
        event(new OperationNotify('今日发布页面打开数:'.$data['data']['values']["inwehub:ask"][$today].'(提问);'.$data['data']['values']["inwehub:discover_add"][$today].'(分享);'.$data['data']['values']["inwehub:dianping-add"][$today].'(点评)'));
    }

}