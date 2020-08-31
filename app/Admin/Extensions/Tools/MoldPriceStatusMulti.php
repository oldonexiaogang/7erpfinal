<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\MoldPrice;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;

class MoldPriceStatusMulti extends CommonTools
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
            "您确定要禁用所选数据？",
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        //判断是否包含未验收或者未入库的数据
        $noprintcount = MoldPrice::whereIn('id',$keys)
            ->where(function ($query) {
                $query->Where('status',0);
            })->count();

        if($noprintcount>0){
            return $this->response()->error('所选数据包含已禁用数据')->refresh();
        }
        $data = MoldPrice::whereIn('id',$keys)->get();
        //批量审核数据
        DB::beginTransaction(); //开启事务
        try{
            foreach($data as $k=>$v){
                $v->status = 0;
                $v->save();
            }
            DB::commit();
            return $this->response()->success('禁用成功')->refresh();
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error('禁用数据异常,请检查')->refresh();
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