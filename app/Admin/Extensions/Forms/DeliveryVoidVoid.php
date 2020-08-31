<?php

namespace App\Admin\Extensions\Forms;

use App\Models\Delivery;
use App\Models\DeliveryPaper;
use App\Models\DeliveryDetail;
use App\Models\DeliveryVoidLog;
use App\Models\PlanList;
use App\Models\PlanListDetail;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class DeliveryVoidVoid extends Form
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
        $info = Delivery::find($id);
        $log_model = new DeliveryVoidLog();
        if($check_status=='not_void'){
            return $this->error('无需作废操作');
        }
        DB::beginTransaction(); //开启事务
        try{
            //发货废除操作
            //1.判断是否有单据、单据是否作废
            $delivery_paper_num = DeliveryPaper::where('delivery_id',$id)->where('is_void','0')->count();
            if($delivery_paper_num==0){

                //计划单回退已派工数量
                $plan_list = PlanList::where('id',$info->plan_list_id)->first();
                $plan_list_detail = PlanListDetail::where('plan_list_id',$plan_list->id)->get();
                foreach ($plan_list_detail as $value){
                    //已发货数量回退
                    $delivery_detail = DeliveryDetail::where('plan_list_detail_id',$value->id)
                        ->first();
                    if($delivery_detail){
                        //回退数量
                        $value->update([
                            'delivery_num'=>$value->delivery_num-$delivery_detail->num,
                            'delivery_complete'=>'0',//回退，派工必定未完成
                        ]);
                    }
                }
                //计划单成品发货状态检测
                $delivery_num = Delivery::query()->where('plan_list_id',$info->plan_list_id)
                    ->where('is_void','0')->where('id','!=',$info->id)
                    ->count();
                if($delivery_num>0){
                    //有其他成品发货，则定在进行中
                    $plan_list->delivery_status = '1';

                }else{
                    $plan_list->delivery_status = '0';
                }
                $plan_list->save();

                //发货单作废
                $old_void_staus=$info->is_void;
                $info->is_void = '1';
                $info->save();
                //发货作废记录
                $log_model->insert([
                    'dispatch_id'=>$info->id,
                    'old_is_void'=>$old_void_staus,
                    'new_is_void'=>'1',
                    'void_user_id'=>Admin::user()->id,
                    'void_user_name'=>Admin::user()->name,
                    'void_at'=>Carbon::now(),
                    'void_reason'=>$reason,
                ]);

            }elseif($delivery_paper_num>0){
                //单据存在但未作废,f返回提示信息。单据未作废，暂不能作废派工单
                DB::rollback();
                return $this->error('单据未作废,请先作废单据');
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
