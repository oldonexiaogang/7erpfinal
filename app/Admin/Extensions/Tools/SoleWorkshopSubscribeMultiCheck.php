<?php

namespace App\Admin\Extensions\Tools;

use App\Services\SoleWorkshopSubscribeLogService;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribe;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;

class SoleWorkshopSubscribeMultiCheck extends CommonTools
{
    protected $action;

    // 注意action的构造方法参数一定要给默认值
    public function __construct($title = null, $action = 1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  ">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要验收所选数据？",
            // 确认弹窗 content
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        //判断是否包含未验收或者未入库的数据
        $noprintcount = SoleWorkshopSubscribeDetail::whereIn('id',$keys)
            ->where(function ($query) {
                $query->orWhere('is_void','1')
                    ->orWhere('is_print','1')
                    ->orWhere('check_status','=','verify');
            })->count();
        if($noprintcount>0){
            return $this->response()->error('请确认所选数据未完全审核且未作废,未打印')->refresh();
        }
        $data = SoleWorkshopSubscribeDetail::whereIn('id',$keys)->get();
        //批量审核数据
        DB::beginTransaction(); //开启事务
        try{
            $log = new SoleWorkshopSubscribeLogService();
            $now = Carbon::now();
            $logdata=[];
            foreach($data as $k=>$v){
                $num = $v->apply_num - $v->approval_num;
                $v->approval_num = $v->apply_num;
                $v->check_status = 'verify';
                $v->check_user_id = Admin::user()->id;
                $v->check_user_name = Admin::user()->name;
                $v->check_time = $now;
                $v->save();
                $logdata[] = [
                    'check_user_id'=>Admin::user()->id,
                    'check_user_name'=>Admin::user()->name,
                    'sole_workshop_subscribe_detail_id'=>$v->id,
                    'sole_workshop_subscribe_id'=>$v->sole_workshop_subscribe_id,
                    'approval_num'=> $num,
                    'reason'=> '批量验收',
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }
            $log->insertAll($logdata);
            DB::commit();
            return $this->response()->success('审核成功')->refresh();
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error('审核数据异常,请检查')->refresh();
        }
    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
