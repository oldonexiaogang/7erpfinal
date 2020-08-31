<?php

namespace App\Admin\Extensions\Forms;

use App\Models\ClientSoleInformation;
use App\Models\ClientSoleInformationVoidLog;
use App\Models\PlanList;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class ClientSoleInformationVoidChange extends Form
{
    // 增加一个自定义属性保存用户ID
    protected $id;
    protected $data;
    protected $num;
    // 构造方法的参数必须设置默认值
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->data = ClientSoleInformation::find($id);
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
        $info = ClientSoleInformation::find($id);
        if($check_status=='0'){
            //检查plan_list是否有使用
            $usenum = PlanList::where('client_sole_information_id',$id)->count();
            if($usenum>0){
                return $this->error('有计划单在使用,不可以禁用');
            }
        }
        DB::beginTransaction(); //开启事务
        try{
            $void_log_model = new ClientSoleInformationVoidLog();
            $now = Carbon::now();
            $void_log_model->insert([
                'client_sole_information_id'=>$info->id,
                'old_is_void'=>$info->is_use,
                'new_is_void'=>$check_status,
                'void_user_id'=>Admin::user()->id,
                'void_user_name'=>Admin::user()->name,
                'void_reason'=>$reason,
                'void_at'=>$now,
                'created_at'=>$now,
                'updated_at'=>$now,
            ]);
            $info->is_use = $check_status;
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
            '1' => '正常',
            '0' => '禁用',
        ];
        $this->hidden('id')->value($this->id);
        $this->hidden('status')->value('1');

        $this->radio('status', '是否正常')
            ->options($check_status)->default('1');
        $this->textarea('reason', '原因');
    }


}
