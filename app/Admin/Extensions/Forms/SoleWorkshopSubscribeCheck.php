<?php

namespace App\Admin\Extensions\Forms;

use App\Services\SoleWorkshopSubscribeLogService;
use App\Models\SoleWorkshopSubscribeDetail;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
class SoleWorkshopSubscribeCheck extends Form
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
        $this->wait_num = $this->data?$this->data->apply_num-$this->data->approval_num:0;
        $this->apply_num = $this->data?$this->data->apply_num:0;
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
        $now_approval_num = $input['num'] ?? null;
        $reason = $input['reason'] ?? null;
        if (! $id) {
            return $this->error('参数错误');
        }
        $info = SoleWorkshopSubscribeDetail::find($id);
        $wait_approval = $info->apply_num-$info->approval_num;
        if($now_approval_num>$wait_approval){
            return $this->error('审核数目超过申请数量');
        }
        if($now_approval_num<=0){
            return $this->error('审核数目异常');
        }
        if($check_status=='overrule'){
            $info->check_status = $check_status;
            $info->save();
            return $this->success('驳回成功');
        }
        DB::beginTransaction(); //开启事务
        try{
            $now = Carbon::now();
            //修改状态
            $info->check_status = $check_status;
            if($info->approval_num+$now_approval_num==$info->apply_num){
                $info->approval_num += $now_approval_num;
                $info->check_status = 'verify';
            }else{
                $info->approval_num += $now_approval_num;
                $info->check_status = 'part';
            }
            $info->check_user_id = Admin::user()->id;
            $info->check_user_name = Admin::user()->name;
            $info->check_time = $now;

            $info->save();
            //添加记录
            $log = new SoleWorkshopSubscribeLogService();
            $log->insertOne([
                'check_user_id'=>Admin::user()->id,
                'check_user_name'=>Admin::user()->name,
                'sole_workshop_subscribe_id'=>$info->sole_workshop_subscribe_id,
                'sole_workshop_subscribe_detail_id'=>$info->id,
                'approval_num'=> $now_approval_num,
                'reason'=> $reason,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);
            DB::commit();
            return $this->success('审核成功');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->error('审核数据异常,请检查');
        }
        return $this->success('Processed successfully.', '/');
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // 设置隐藏表单，传递用户id
        $check_status = [
            'verify' => '已审核',
            'overrule' => '驳回',
        ];
        $this->hidden('id')->value($this->id);
        $this->hidden('num')->value(0);
        $this->hidden('status')->value('verify');

        $this->radio('status', '审批')->options($check_status)->default('verify');
        $this->text('num', '准购数量')->default($this->wait_num)->rules('required');
        $this->textarea('reason', '审核意见');
    }
    // 返回表单数据，如不需要可以删除此方法
    public function default()
    {
//        return [
//            'check_status'         => 'verify',
//            'approval_num' => 0,
//            'reason' => '',
//        ];
    }

}
