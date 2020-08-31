<?php

namespace App\Admin\Extensions\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;
use App\Models\PlanList;
use App\Models\Delivery;

class GatherLeftExcelExpoter implements FromView
{
    public function __construct($start,$end){
        $this->time_start = $start;
        $this->time_end = $end;
    }
    public function view(): View
    {
        $planorder = new PlanList();
        $month_today = date('Y-m-d',time());
        $time_start = $this->time_start?date('Y-m-d H:s',strtotime($this->time_start)): Carbon::today()->toDateString();
        $time_end = $this->time_end?date('Y-m-d H:s',strtotime($this->time_end)): Carbon::tomorrow()->toDateString();
        $data = PlanList::where('created_at', '>=', date('Y-m-d',strtotime(date('Y-m').'-1')))
            ->where('created_at', '<=',$time_end)
            ->whereDate('created_at','<',$month_today)
            ->get()->groupBy('client_id')->toArray();
        $showarr = [];

        foreach($data as $kk=>$vv){
            $showarr[]=[
                'client_name'=>$vv[0]['client_name'],
                'client_id'=>$vv[0]['client_id'],
                'num'=>$planorder->planListClientNum($vv[0]['client_id'],$time_start,$time_end),
                'TPU'=>$planorder->planListTpu($vv[0]['client_id'],$time_start,$time_end),
                'rubber'=>$planorder->planListRubber($vv[0]['client_id'],$time_start,$time_end),
                'welt'=>$planorder->planListWelt($vv[0]['client_id'],$time_start,$time_end),
            ];
        }
        $all['num'] = $planorder->planListClientNum(0,$time_start,$time_end);
        $all['tpu'] = $planorder->planListTpu(0,$time_start,$time_end);
        $all['rubber'] = $planorder->planListRubber(0,$time_start,$time_end);
        $all['welt'] = $planorder->planListWelt(0,$time_start,$time_end);
        $all['color'] = $planorder->planListColor(0,$time_start,$time_end);

        //成品发货信息
        $month_today = date('Y-m-d',time());
        $day = Delivery::whereDate('created_at', $month_today)->sum('all_num');

        $month = Delivery::where('created_at', '>=', date('Y-m-d',
            strtotime(date('Y-m').'-1')))
            ->where('created_at', '<=',$month_today)->sum('all_num');
        $chengpin['day']=$day;
        $chengpin['month']=$month;
        $showarr  =arraySort($showarr,'num');

        return view('admin.gather.left', [
            'data'=>$showarr,
            'all'=>$all,
            'time'=>['lstart'=>$time_start,
                     'lend'=>$time_end],
            'chengpin'=>$chengpin]);
    }
}
