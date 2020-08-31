<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\RawMaterialStorageOut;

class MoldInformationMultiPrint extends CommonTools
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
            "您确定要打印所选数据？",
            // 确认弹窗 content
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        $ids = implode(',',$keys);
        return $this->response()->redirect(admin_url('mold-information/print?id='.$ids));
    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
