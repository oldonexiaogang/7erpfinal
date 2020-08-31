<?php

namespace App\Admin\Extensions\Tools;

use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribe;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;

class SoleWorkshopSubscribeMultiDelete extends CommonTools
{
    protected $action;

    // 注意action的构造方法参数一定要给默认值
    public function __construct($title = null, $action = 1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  ">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要验收所选数据？",
            // 确认弹窗 content
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        try{
            SoleWorkshopSubscribeDetail::whereIn('id',$keys)->delete();
            DB::commit();
            return $this->response()->success('删除成功')->refresh();
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error('数据异常,请检查')->refresh();
        }
    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
