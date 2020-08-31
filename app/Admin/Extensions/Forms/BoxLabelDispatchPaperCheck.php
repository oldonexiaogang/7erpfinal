<?php

namespace App\Admin\Extensions\Forms;

use App\Models\BoxLabelDispatchPaper;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;

class BoxLabelDispatchPaperCheck extends Form
{
    // 增加一个自定义属性保存用户ID
    protected $id;
    protected $data;
    protected $num;
    // 构造方法的参数必须设置默认值
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->data = BoxLabelDispatchPaper::find($id);
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
        $info = BoxLabelDispatchPaper::find($id);
        if($check_status=='not_void'){
            return $this->error('无需作废操作');
        }
        DB::beginTransaction(); //开启事务
        try{
            $info->is_void = '1';
            $info->void_at = Carbon::now();
            $info->void_reason = $reason;
            $info->save();
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
