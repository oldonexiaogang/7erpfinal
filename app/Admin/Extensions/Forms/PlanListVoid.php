<?php

namespace App\Admin\Extensions\Forms;

use App\Models\PlanList;
use App\Models\PlanListDetail;
use App\Models\PlanListVoidLog;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class PlanListVoid extends Form
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
        $info = PlanList::find($id);
        $log_model = new PlanListVoidLog();
        if($check_status=='not_void'){
            return $this->error('无需作废操作');
        }
        DB::beginTransaction(); //开启事务
        try{

            //计划单作废
            $old_void_staus = $info->is_void;
            $info->is_void = '1';
            $info->save();
            //计划单作废记录
            $log_model->insert([
                'dispatch_id'=>$info->id,
                'old_is_void'=>$old_void_staus,
                'new_is_void'=>'1',
                'void_user_id'=>Admin::user()->id,
                'void_user_name'=>Admin::user()->name,
                'void_at'=>Carbon::now(),
                'void_reason'=>$reason,
            ]);


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
