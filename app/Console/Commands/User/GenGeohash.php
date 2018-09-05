<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\LoginRecord;
use App\Models\UserData;
use App\Services\GeoHash;
use Illuminate\Console\Command;

class GenGeohash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:gen_geohash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成用户的最近地理位置';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $records = LoginRecord::orderBy('created_at','desc')->get();
        $uids = [];
        foreach ($records as $record) {
            if ($record->longitude && !isset($uids[$record->user_id])) {
                $uids[$record->user_id] = $record->user_id;
                UserData::where('user_id',$record->user_id)->update([
                    'last_visit' => $record->created_at,
                    'last_login_ip' => $record->ip,
                    'longitude'    => $record->longitude,
                    'latitude'     => $record->latitude,
                    'geohash'      => GeoHash::instance()->encode($record->latitude,$record->longitude)
                ]);
            }
        }
    }

}