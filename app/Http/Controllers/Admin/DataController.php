<?php

namespace App\Http\Controllers\Admin;

use App\Models\Doing;
use App\Models\RecommendRead;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DataController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function views(Request $request)
    {
        $filter =  $request->all();
        $data = [];
        $dateRange = [
            date('Y-m-d',strtotime('-7 days')),
            date('Y-m-d')
        ];

        /*提问时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $dateRange = explode(" - ",$filter['date_range']);
        }
        $filter['date_range'] = implode('-',$dateRange);
        $labelTimes = [];
        $j = 0;
        while (strtotime($dateRange[0].' +'.$j.' days')<=strtotime($dateRange[1])) {
            $labelTimes[$j] = date('Y-m-d',strtotime($dateRange[0].' +'.$j.' days'));
            $j++;
        }
        $cacheKey = 'admin-data-cache-views-'.$filter['date_range'];
        $data = Cache::get($cacheKey);
        if (!$data) {
            $recommendIds = RecommendRead::where('source_type',Submission::class)
                ->where('audit_status',1)
                ->select('source_id')
                ->pluck('source_id')
                ->toArray();

            for( $i=0 ; $i < $j ; $i++ ){
                $startTime = $labelTimes[$i].' 00:00:00';
                $endTime = $labelTimes[$i].' 23:59:59';
                //文章阅读数
                $data[$i]['article']['views'] = Doing::where('action',Doing::ACTION_VIEW_SUBMISSION)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();
                $data[$i]['article']['users'] = Doing::select('user_id')->where('action',Doing::ACTION_VIEW_SUBMISSION)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->distinct()
                    ->get()->count();
                //精选阅读数
                $data[$i]['recommend']['views'] = Doing::where('action',Doing::ACTION_VIEW_SUBMISSION)
                    ->whereIn('source_id',$recommendIds)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();
                $data[$i]['recommend']['users'] = Doing::select('user_id')->where('action',Doing::ACTION_VIEW_SUBMISSION)
                    ->whereIn('source_id',$recommendIds)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->distinct()
                    ->get()->count();
                //问题浏览数
                $data[$i]['question']['views'] = Doing::whereIn('action',[Doing::ACTION_VIEW_PAY_QUESTION,Doing::ACTION_VIEW_FREE_QUESTION])
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();
                $data[$i]['question']['users'] = Doing::select('user_id')->whereIn('action',[Doing::ACTION_VIEW_PAY_QUESTION,Doing::ACTION_VIEW_FREE_QUESTION])
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->distinct()
                    ->get()->count();
                //回答浏览数
                $data[$i]['answer']['views'] = Doing::where('action',Doing::ACTION_VIEW_ANSWER)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();
                $data[$i]['answer']['users'] = Doing::select('user_id')->where('action',Doing::ACTION_VIEW_ANSWER)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->distinct()
                    ->get()->count();
                //名片浏览数
                $data[$i]['resume']['views'] = Doing::where('action',Doing::ACTION_VIEW_RESUME)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();
                $data[$i]['resume']['users'] = Doing::select('user_id')->where('action',Doing::ACTION_VIEW_RESUME)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->distinct()
                    ->get()->count();
            }
            Cache::put($cacheKey,$data,30);
        }


        return view("admin.data.views")->with('filter',$filter)->with('data',$data)->with('labelTimes',$labelTimes);
    }
}
