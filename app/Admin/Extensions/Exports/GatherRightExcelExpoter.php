<?php

namespace App\Admin\Extensions\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;
use App\Models\PlanList;
use App\Models\Delivery;

class GatherRightExcelExpoter implements FromView
{
    public function __construct($start,$end){
        $this->time_start = $start;
        $this->time_end = $end;
    }
    public function view(): View
    {
        $month_today = date('Y-m-d',time());
        $planorder = new PlanList();
        $data = PlanList::whereDate('created_at','<',$month_today)->get()->groupBy('client_id')->toArray();
        $showarr = [];
        $time_start = $this->time_start?date('Y-m-d H:s',strtotime($this->time_start)): Carbon::parse('15 days ago')->toDateString();
        $time_end = $this->time_end?date('Y-m-d H:s',strtotime($this->time_end)): Carbon::tomorrow()->toDateString();
        foreach($data as $kk=>$vv){
            $showarr[]=[
                'client_name'=>$vv[0]['client_name'],
                'client_id'=>$vv[0]['client_id'],
                'num'=>$planorder->planListNoCompleteClientNum($vv[0]['client_id'],$time_start,$time_end),
                'TPU'=>$planorder->planListNoCompleteTpu($vv[0]['client_id'],$time_start,$time_end),
                'rubber'=>$planorder->planListNoCompleteRubber($vv[0]['client_id'],$time_start,$time_end),
                'welt'=>$planorder->planListNoCompleteWelt($vv[0]['client_id'],$time_start,$time_end),
            ];
        }
        $all['num'] = $planorder->planListNoCompleteClientNum(0,$time_start,$time_end);
        $all['tpu'] = $planorder->planListNoCompleteTpu(0,$time_start,$time_end);
        $all['rubber'] = $planorder->planListNoCompleteRubber(0,$time_start,$time_end);
        $all['welt'] = $planorder->planListNoCompleteWelt(0,$time_start,$time_end);
        $all['color'] = $planorder->planListNoCompleteColor(0,$time_start,$time_end);

        //成品发货信息
        $showarr  =arraySort($showarr,'num');

        return view('admin.gather.left', [
            'data'=>$showarr,
            'all'=>$all,
            'time'=>['rstart'=>$time_start,'rend'=>$time_end],
            ]);
    }
}
