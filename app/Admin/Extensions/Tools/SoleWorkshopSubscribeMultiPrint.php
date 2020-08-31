<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribe;

class SoleWorkshopSubscribeMultiPrint extends CommonTools
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
        //判断是否包含未验收或者未入库的数据
        $noprintcount = SoleWorkshopSubscribeDetail::whereIn('id',$keys)
            ->where(function ($query) {
                $query->orWhere('is_void','1')
                    ->orWhere('is_print','1')
                    ->orWhere('check_status','!=','verify');
            })->count();
        if($noprintcount>0){
            return $this->response()->error('请确认所选数据已审核且未作废未打印')->refresh();
        }
        $ids = implode(',',$keys);
        return $this->response()->redirect(admin_url('sole-workshop-subscribe-detail/preview?id='.$ids));
    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
