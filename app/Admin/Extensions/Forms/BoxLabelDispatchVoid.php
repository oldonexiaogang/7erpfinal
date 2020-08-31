<?php

namespace App\Admin\Extensions\Forms;

use App\Models\Dispatch;
use App\Models\DispatchDetail;
use App\Models\PlanList;
use App\Models\PlanListDetail;
use App\Models\DispatchVoidLog;
use App\Models\BoxLabelDispatchPaper;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class BoxLabelDispatchVoid extends Form
{
    // 增加一个自定义属性保存用户ID
    protected $id;
    protected $data;
    protected $num;
    // 构造方法的参数必须设置默认值
    public function __construct($id = null)
    {
        $this->id = $id;
        parent::__construct();
    }
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return Response
     */
    public function handle(array $input)
    {
        $id = $input['id'] ?? null;
        $check_status = $input['status'] ?? null;
        $reason = $input['reason'] ?? null;
        if (! $id) {
            return $this->error('参数错误');
        }
        $info = Dispatch::find($id);
        $log_model = new DispatchVoidLog();
        if($check_status=='not_void'){
            return $this->error('无需作废操作');
        }
        DB::beginTransaction(); //开启事务
        try{
            //箱标派工废除操作
            //1.判断是否有单据、单据是否作废
            $box_label_dispatch_paper_num = BoxLabelDispatchPaper::where('dispatch_id',$id)
                ->where('is_void','0')->count();

            if($box_label_dispatch_paper_num==0){
                //计划单回退已派工数量
                $plan_list = PlanList::where('id',$info->plan_list_id)->first();
                $plan_list_detail = PlanListDetail::where('plan_list_id',$plan_list->id)->get();
                foreach ($plan_list_detail as $value){
                    //派工单详情中尺码对应数量回退
                    $dispatch_detail =DispatchDetail::where('plan_list_detail_id',$value->id)
                        ->first();
                    if($dispatch_detail){
                        //回退数量
                        $value->update([
                            'box_label_dispatch_num'=>$value->box_label_dispatch_num-$dispatch_detail->num,
                            'box_label_dispatch_complete'=>'0',//回退，派工必定未完成
                        ]);
                    }
                }
                //计划单箱标派工状态检测
                $has_dispatch_num = Dispatch::query()->where('plan_list_id',$info->plan_list_id)
                    ->where('is_void','0')
                    ->where('type','box_label')
                    ->count();
                if($has_dispatch_num){
                    //有其他箱标派工，则定在进行中
                    $plan_list->box_label_status = '1';

                }else{
                    $plan_list->box_label_status = '0';
                }
                $plan_list->save();
                $old_void_staus = $info->is_void;
                //派工单作废
                $info->is_void = '1';
                $info->save();
                //派工作废记录
                $log_model->insert([
                    'dispatch_id'=>$info->id,
                    'old_is_void'=>$old_void_staus,
                    'new_is_void'=>'1',
                    'void_user_id'=>Admin::user()->id,
                    'void_user_name'=>Admin::user()->name,
                    'void_at'=>Carbon::now(),
                    'void_reason'=>$reason,
                ]);

            }elseif($box_label_dispatch_paper_num>0){
                //单据存在但未作废,f返回提示信息。单据未作废，暂不能作废派工单
                DB::rollback();
                return $this->error('存在未作废单据,请先作废单据');
            }

            DB::commit();
            return $this->success('作废成功');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->error('数据异常,请检查');
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // 设置隐藏表单，传递用户id
        $check_status = [
            'no_void' => '正常',
            'void' => '作废',
        ];
        $this->hidden('id')->value($this->id);
        $this->hidden('status')->value('void');

        $this->radio('status', '是否作废')->options($check_status)->default('void');
        $this->textarea('reason', '作废原因');
    }

}
