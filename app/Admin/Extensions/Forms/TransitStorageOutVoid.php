<?php

namespace App\Admin\Extensions\Forms;

use App\Models\Dispatch;
use App\Models\PlanList;
use App\Models\TransitStorageOut;
use App\Models\DispatchDetail;
use App\Models\TransitStorageOutDetail;
use App\Models\TransitStorageOutVoidLog;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class TransitStorageOutVoid extends Form
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
        $info = TransitStorageOut::find($id);
        $log_model = new TransitStorageOutVoidLog();
        if($check_status=='not_void'){
            return $this->error('无需作废操作');
        }
        DB::beginTransaction(); //开启事务
        try{
            //出库废除操作
            //检测鞋底派工对应的箱标派工是否存在
            $box_label_num = Dispatch::where('plan_list_id',$info->plan_list_id)
                ->where('type','box_label')->where('is_void','0')->count();
            if($box_label_num>0){
                DB::rollback();
                return $this->error('存在未作废箱标派工，请先作废箱标派工');
            }
            //1.判断c出库数量是否大于0
            if($info->num>0){
                //鞋底派工，已出库数量恢复，
                $transit_storage_out_detail =TransitStorageOutDetail::where('trandit_storage_out_id',$info->id)
                    ->get();
                foreach($transit_storage_out_detail as $value){
                    $dispatch_detail = DispatchDetail::where('id',$value->dispatch_detail_id)
                        ->increment('storage_out',$value->num);
                }
                //出库单出库状态+作废
                $storage_out_num = DispatchDetail::where('dispatch_id',$info->dispatch_id)->whereHas('dispatch_info',function ($q) use($info){
                    $q->where('is_void','0')->where('id','!=',$info->id);
                })->sum('storage_out');
                $old_void_staus = $info->is_void;
                $info->is_void = '1';
                $info->storage_out_status = $storage_out_num>0?'1':'0';
                $info->save();
                //计划单出库数据变化
                $about_plan_list_of_storage_out_num = DispatchDetail::where('dispatch_id',$info->dispatch_id)->whereHas('dispatch_info',function ($q) use($info){
                    $q->where('is_void','0')->where('id','!=',$info->id)->where('plan_list_id',$info->plan_list_id);
                })->sum('storage_out');
                $plan_list_info = PlanList::find($info->plan_list_id);
                $plan_list_info->storage_out_num = $about_plan_list_of_storage_out_num;
                $plan_list_info->storage_out_status = $about_plan_list_of_storage_out_num>0?'1':'0';
                $plan_list_info->save();
                //出库作废记录
                $log_model->insert([
                    'dispatch_id'=>$info->id,
                    'old_is_void'=>$old_void_staus,
                    'new_is_void'=>'1',
                    'void_user_id'=>Admin::user()->id,
                    'void_user_name'=>Admin::user()->name,
                    'void_at'=>Carbon::now(),
                    'void_reason'=>$reason,
                ]);
            }else{

                DB::rollback();
                return $this->error('信息有误，请刷新重试');
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
