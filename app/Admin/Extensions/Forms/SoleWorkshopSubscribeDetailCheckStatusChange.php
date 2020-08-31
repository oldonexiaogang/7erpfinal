<?php

namespace App\Admin\Extensions\Forms;

use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribeCheckLog;
use App\Services\SoleWorkshopSubscribeLogService;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class SoleWorkshopSubscribeDetailCheckStatusChange extends Form
{
    // 增加一个自定义属性保存用户ID
    protected $id;
    protected $data;
    protected $num;
    // 构造方法的参数必须设置默认值
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->data = SoleWorkshopSubscribeDetail::find($id);
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
        $num = $input['num'] ?? null;
        $num=is_float_number($num);
        if (! $id) {
            return $this->error('参数错误');
        }
        $info = SoleWorkshopSubscribeDetail::find($id);
        if($info->storage_in_num >0 ){
            return $this->error('已入库不可以修改');
        }
        if($info->apply_num <$num ){
            return $this->error('审核后数量不能大于申请数量');
        }
        $new_check_status = $check_status;
        if($check_status!='overrule'){
            if($num==$info->apply_num){
                $new_check_status = 'verify';
            }else if($num<$info->apply_num){
                $new_check_status = 'part';
            }
        }

        if($new_check_status!=$check_status){
            return $this->error('请确认审核状态');
        }

        DB::beginTransaction(); //开启事务
        try{
            $void_log_model = new SoleWorkshopSubscribeCheckLog();
            $now = Carbon::now();
            $changenum = $num -$info->approval_num;
            if($changenum>0){
                $type='in';
            }else if($changenum<0){
                $type='out';
            }else{
                $type='nochange';
            }
            $void_log_model->insert([
                'sole_workshop_subscribe_detail_id'=>$info->id,
                'old_check_status'=>$info->check_status,
                'new_check_status'=>$check_status,
                'check_user_id'=>Admin::user()->id,
                'check_user_name'=>Admin::user()->name,
                'check_reason'=>$reason,
                'check_at'=>$now,
                'type'=>$type,
                'num'=>abs($changenum),
                'old_num'=>$info->approval_num,
                'now_approval_num'=>$num,
                'created_at'=>$now,
                'updated_at'=>$now,
            ]);
            $info->check_status = $check_status;
            $info->check_time = $now;
            $info->check_user_id = Admin::user()->id;
            $info->check_user_name = Admin::user()->name;
            $info->approval_num = $num;
            $info->save();
            $log = new SoleWorkshopSubscribeLogService();
            $log->insertOne([
                'check_user_id'=>Admin::user()->id,
                'check_user_name'=>Admin::user()->name,
                'sole_workshop_subscribe_id'=>$info->sole_workshop_subscribe_id,
                'sole_workshop_subscribe_detail_id'=>$info->id,
                'approval_num'=> $num,
                'reason'=> "【直接修改审核状态调整】".$reason,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);
            DB::commit();
            return $this->success('操作成功');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->error($e);
            return $this->error('数据异常,请检查');
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // 设置隐藏表单，传递用户id
        $check_status = config('plan.sole_workshop_subscribe_detail_check_status_pure');
        $this->hidden('id')->value($this->id);
        $this->hidden('status')->value('unreviewed');

        $this->radio('status', '修改审核状态')
            ->options($check_status)->default('unreviewed');
        $this->text('num', '修改后入库数量');
        $this->textarea('reason', '原因');
    }

}
