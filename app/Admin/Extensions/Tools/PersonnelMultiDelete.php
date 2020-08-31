<?php

namespace App\Admin\Extensions\Tools;

use App\Models\Personnel;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\MoldPrice;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;

class PersonnelMultiDelete extends CommonTools
{
    protected $action;

    // 注意action的构造方法参数一定要给默认值
    public function __construct($title = null,$action=1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  ">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return [
            "您确定要删除所选数据？",
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        $keys = $this->getKey();
        DB::beginTransaction(); //开启事务
        try{
            Personnel::whereIn('id',$keys)->delete();
            DB::commit();
            return $this->response()->success('删除成功')->refresh();
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error('数据异常,请检查')->refresh();
        }
    }
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
