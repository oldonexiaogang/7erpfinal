<?php

namespace App\Admin\Extensions\Tools;

use App\Models\RawMaterialStorageOutPaper;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;

class RawMaterialStorageOutPaperCheckMulti extends CommonTools
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
        //判断是否包含未验收或者未入库的数据
        $noprintcount = RawMaterialStorageOutPaper::whereIn('id',$keys)
            ->where(function ($query) {
                $query->orWhere('is_void','1')
                    ->orWhere('is_check','1');
            })->count();
        if($noprintcount>0){
            return $this->response()->error('请确认所选数据未审核且未作废')->refresh();
        }
        $data = RawMaterialStorageOutPaper::whereIn('id',$keys)->get();
        //批量审核数据
        DB::beginTransaction(); //开启事务
        try{
            $log = new RawMaterialStorageOutPaper();
            $now = Carbon::now();
            $logdata=[];
            foreach($data as $k=>$v){
                $v->is_check = '1';
                $v->check_user_id = Admin::user()->id;
                $v->check_user_name = Admin::user()->name;
                $v->check_time = $now;
                $v->save();
            }
            $log->insertAll($logdata);
            DB::commit();
            return $this->response()->success('审核成功')->refresh();
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error('审核数据异常,请检查')->refresh();
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
