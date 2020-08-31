<?php

namespace App\Admin\Extensions\Forms;

use App\Models\SoleWorkshopSubscribeCheckLog;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribeVoidLog;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class SoleWorkshopSubscribeDetailStatusChange extends Form
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
        if (! $id) {
            return $this->error('参数错误');
        }
        $info = SoleWorkshopSubscribeDetail::find($id);

        DB::beginTransaction(); //开启事务
        try{
            $void_log_model = new SoleWorkshopSubscribeVoidLog();
            $now = Carbon::now();
            $void_log_model->insert([
                'sole_workshop_subscribe_detail_id'=>$info->id,
                'old_is_void'=>$info->is_void,
                'new_is_void'=>$check_status,
                'void_user_id'=>Admin::user()->id,
                'void_user_name'=>Admin::user()->name,
                'void_reason'=>$reason,
                'void_at'=>$now,
                'created_at'=>$now,
                'updated_at'=>$now,
            ]);
            $info->is_void = $check_status;
            $info->save();
            DB::commit();
            return $this->success('操作成功');
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
            '1' => '作废',
            '0' => '正常',
        ];
        $this->hidden('id')->value($this->id);
        $this->hidden('status')->value('1');

        $this->radio('status', '是否作废')
            ->options($check_status)->default('1');
        $this->textarea('reason', '原因');
    }


}
