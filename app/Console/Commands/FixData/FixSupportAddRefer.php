<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Answer;
use App\Models\Collection;
use App\Models\Pay\Order;
use App\Models\Pay\Ordergable;
use App\Models\Support;
use App\Models\User;
use Illuminate\Console\Command;

class FixSupportAddRefer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:support:add_refer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复点赞数据，增加被点赞人';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $supports = Support::get();
        foreach ($supports as $support) {
            $source = $support->source;
            if (empty($source)) {
                $this->warn($support->supportable_type.':'.$support->supportable_id);
                continue;
            }
            $support->refer_user_id = $source->user_id;
            $support->save();
        }
    }

}