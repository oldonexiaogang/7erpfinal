<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use App\Models\Dispatch;
use App\Models\TransitStorage;
use App\Models\DispatchDetail;
use App\Models\TransitStorageIn;
use App\Models\TransitStorageLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Faker\Factory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class TransitStorageController extends AdminController
{

    /**
     * 中转仓入库信息管理
     * @param Content $content
     * @return mixed
     */
    public function outInIndex(Content $content){
        return $content
            ->title('中转仓出入明细')
            ->row(function (Row $row) {
                $row->column(12, $this->outInIndexGrid());
            });
    }
    protected function outInIndexGrid(){
        return Grid::make(new TransitStorageLog(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->created_at;
            $grid->company_model;
            $grid->spec;
            $grid->type->using(config('plan.transit_storage_type'));
            $grid->in_num;
            $grid->out_num;
            $grid->storage;
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableCreateButton();
            $grid->disableBatchDelete();
            $grid->toolsWithOutline(false);
            $grid->withBorder();
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->between('created_at', '日期')->date()->width(4);
            });
            $grid->disableActions();
        });
    }
    /**
     * 中转仓入库信息管理
     * @param Content $content
     * @return mixed
     */
    public function countIndex(Content $content){
        return $content
            ->title('中转仓计件')
            ->row(function (Row $row) {
                $row->column(12, $this->countIndexGrid());
            });
    }
    protected function countIndexGrid()
    {
        return Grid::make(TransitStorageIn::with(['dispatch_info']), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->plan_list_no;
            $grid->company_model;
            $grid->spec;
            $grid->column('dispatch_num','派工数')->display(function (){
                return $this->dispatch_info['all_num'];
            });
            $grid->column('transit_storage_in_num')->display(function (){
                return $this->all_num;
            });
            $grid->column('count_num','计件数量')->display(function (){
                return $this->all_num;
            });
            $grid->column('inject_mold_price','工价')->display(function (){
                return $this->inject_mold_price;
            });
            $grid->column('all_price','加工费')->display(function (){
                return sprintf('%.2f',$this->all_num*$this->inject_mold_price);
            });
            $grid->column('operation','操作')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('transit-storage-count/'.$this->id.'?dialog=1'),
                        'value'=>'<i class="fa fa-search"></i>', 'width'=>'900px',
                        'height'=>'600px'];
            });
            $all = TransitStorageIn::sum('all_num');
            // $allprice = ZhongzhuanRuku::sum('num*yuangong_gongjia');
            // 加工费合计：'.$allprice.'元
            $grid->header(function ($query) use ($all){
                return ' <label>计件数合计'.$all.'双
 *导出Excel文件的功能，请先输入条件搜索后再导出！</label>
                     ';
            });
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('plan_list_no')->width(2);
                $filter->like('dispatch_no')->width(2);
                $filter->like('company_model')->width(2);
                $filter->like('spec')->width(2);
                $filter->equal('count_type')->select(config('plan.transit_storage_count_type'))->width(2);
                $filter->like('personnel_name')->width(2);
            });
            //导出
            $titles = [
                'plan_order_no' => '计划编号', 'xiedi_model' => '鞋底型号',
                'purchase_spec_name' => '明细规格', 'zhusudetail' => '派工数',
                'num' => '入库数量',
                'jijiannum' => '计件数量', 'gongjia' => '工价',
                'jiagongfei' => '加工费' ];
            $filename = date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['zhusudetail'] = $row['zhusupaigongdetail']['paidan_num'];
                    $row['jijiannum'] = $row['num'];
                    $row['gongjia'] = $row['yuangong_gongjia'];
                    $row['jiagongfei'] = $row['num']*$row['yuangong_gongjia'];
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 中转仓入库信息管理
     * @param Content $content
     * @return mixed
     */
    public function inManageIndex(Content $content){
        return $content
            ->title('中转仓入库管理')
            ->row(function (Row $row) {
                $row->column(12, $this->inManageIndexGrid());
            });
    }

    /**
     * 中转入库信息数据
     * @return Grid
     */
    protected function inManageIndexGrid()
    {
        $status_arrs = config('plan.plan_status_html');

        $model = new DispatchDetail();
        return Grid::make($model->with('dispatch_info'), function (Grid $grid) use($status_arrs,$model) {
            $grid->model()->whereHas('dispatch_info',function ($q){
                $q->where('type','inject_mold');
            })->orderBy('created_at','desc');

            $grid->column('created_at','派工时间')->display(function (){
                return $this->created_at;
            });
            $grid->column('plan_list_no')->display(function (){
                return $this->dispatch_info['plan_list_no'];
            });
            $grid->column('dispatch_no')->display(function (){
                return $this->dispatch_info['dispatch_no'];
            });
            $grid->column('client_name')->display(function (){
                return $this->dispatch_info['client_name'];
            });
            $grid->column('company_model')->display(function (){
                return $this->dispatch_info['company_model'];
            });
            $grid->column('spec')->display(function (){
                return $this->spec;
            });
            $grid->column('craft_color_name','工艺颜色')->display(function (){
                return $this->dispatch_info['craft_color_name'];
            });
            $grid->column('sole_material_name','材料用料')->display(function (){
                return $this->dispatch_info['sole_material_name'];
            });
            $grid->column('type','派工类型')->display(function (){
                return config('plan.dispatch_process_workshop')['inject_mold'];
            });
            $grid->column('num','派工数量');
            $grid->column('storage_in','入库数量')->display(function (){
                return is_float_number($this->storage_in);
            });
            $grid->column('status','派工情况')->display(function () use($status_arrs){
                if($this->storage_in==$this->num){
                    $status=2;
                }else {
                    $status=1;
                }
                return $status_arrs[$status];
            });
            $grid->column('storage_in_operation','中转入库')
                ->if(function ($column) {
                    $res = $this->storage_in<$this->num;
                    return $res;
                })
//                ->dialog(function (){
//                    return ['type'=>'url','url'=> admin_url('transit-storage-in/create/'.$this->id.'?dialog=1&keep=1'),
//                            'value'=>'<i class=" fa fa-arrow-circle-o-down"></i>', 'width'=>'900px',
//                            'height'=>'600px'];
//                })
                ->display(function (){
                    Form::dialog('中转入库')
                        ->click('#transit_storage_in_'.$this->id) // 绑定点击按钮
                        ->url(admin_url('transit-storage-in/create/'.$this->id.'?dialog=1')) // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                        ->width('900px') // 指定弹窗宽度，可填写百分比，默认 720px
                        ->height('650px') // 指定弹窗高度，可填写百分比，默认 690px
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    $id= $this->id;
                    return '<i class=" text-info fa fa-arrow-circle-o-down" id="transit_storage_in_'.$id.'"></i>';
                })
                ->else()
                ->display(function(){
                    return '-';
                });
            $grid->header(function ($query) {
                return '  <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('transit-storage-in-list') . '" class="btn btn-sm btn-info" title="查看入库信息">
                           <span class="hidden-xs">&nbsp;&nbsp;查看入库信息&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('xiedi-paigongs/create') . '" class="btn btn-sm btn-info" title="打印入库单">
                           <span class="hidden-xs">&nbsp;&nbsp;打印入库单&nbsp;&nbsp;</span>
                        </a>
                    </div>
                     <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('transit-storage') . '" class="btn btn-sm btn-info" title="查看库存">
                           <span class="hidden-xs">&nbsp;&nbsp;查看库存&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('transit-storage-in/create') . '" class="btn btn-sm btn-info" title="新增">
                           <span class="hidden-xs">&nbsp;&nbsp;新增&nbsp;&nbsp;</span>
                        </a>
                    </div>';
            });
            $grid->disableActions();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableCreateButton();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('plan_no','计划单号')->width(2);
            });
        });
    }
    /**
     * 中转仓出库信息管理
     * @return Grid
     */
    public function outManageIndex(Content $content){
        return $content
            ->title('中转仓出库管理')
            ->row(function (Row $row) {
                $row->column(12, $this->outManageIndexGrid());
            });
    }
    /**
     * 中转出库信息数据
     * @return Grid
     */
    protected function outManageIndexGrid()
    {
        $status_arrs = config('plan.plan_status_html');

        $model = new Dispatch();
        return Grid::make($model->with('plan_list'), function (Grid $grid) use($status_arrs,$model) {
            $grid->model()->where('type','sole')
                ->whereHas('detail',function ($q){
                    $q->where('status','!=','0');
            })->orderBy('created_at','desc');

            $grid->column('created_at','派工时间')->display(function (){
                return $this->created_at;
            })->width('80px');
            $grid->column('plan_list_no')->display(function (){
                return $this->plan_list_no;
            });
            $grid->column('dispatch_no')->display(function (){
                return $this->dispatch_no;
            });
            $grid->column('client_name')->display(function (){
                return $this->client_name;
            });
            $grid->column('company_model')->display(function (){
                return $this->company_model;
            });
            $grid->column('client_model')->display(function (){
                return $this->client_model;
            });
            $grid->column('craft_color_name','工艺颜色')->display(function (){
                return $this->craft_color_name;
            });
            $grid->column('sole_material_name','材料用料')->display(function (){
                return $this->sole_material_name;
            });
            $grid->column('product_category_name','产品类型')->display(function (){
                return $this->product_category_name;
            });
            $grid->column('all_num','派工数量')->display(function (){
                return is_float_number($this->all_num);
            });
            $grid->column('storage_out_num','已出库')->display(function () use($model){
                //派工单对应的出库
                $num = $model->getStorageOutNum($this->id);
                return is_float_number($num);
            });
            $grid->column('wait_storage_out','未出库')->display(function () use($model){
                $num = $model->getStorageOutNum($this->id);
                return is_float_number($this->all_num-$num);
            });
            $grid->column('storage_out_operation','中转出库')
                ->if(function ($column) use($model){
                    $num = $model->getStorageOutNum($this->id);
                    $res = $num<$this->all_num;
                    return $res;
                })
                ->display(function (){
                    Form::dialog('中转出库')
                        ->click('#transit_storage_out_'.$this->id) // 绑定点击按钮
                        ->url(admin_url('transit-storage-out/create/'.$this->id.'?dialog=1')) // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                        ->width('900px') // 指定弹窗宽度，可填写百分比，默认 720px
                        ->height('700px') // 指定弹窗高度，可填写百分比，默认 690px
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    $id= $this->id;
                    return '<i class=" text-info fa fa-arrow-circle-o-down" id="transit_storage_out_'.$id.'"></i>';
                })
                ->else()
                ->display(function(){
                    return '-';
                });
            $grid->column('storage_out_status', '出库进度')->display(function () use($status_arrs) {
                return $status_arrs[$this->storage_out_status];
            });
            $grid->column('code_33', '33')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($model){
                $arr = $model->getWaitStorageOutNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");

            $grid->disableActions();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableCreateButton();
            $grid->withBorder();
            $grid->tableWidth('140%');
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id','客户')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
                    })->width(2);
                $filter->like('company_model')->width(2);
                $filter->like('client_model')->width(2);
                $filter->like('craft_color_name','工艺颜色')->width(2);
                $filter->like('sole_material_name','材料用料')->width(2);
                $filter->like('dispatch_no','鞋底派工编号')->width(2);
                $filter->multiInput('plan_no',function ($qq){
                    if($this->input1){
                        $qq->orWhere('plan_list_no','like','%'.$this->input1.'%');
                    }
                    if($this->input2){
                        $qq->orWhere('plan_list_no','like','%'.$this->input2.'%');
                    }
                    if($this->input3){
                        $qq->orWhere('plan_list_no','like','%'.$this->input3.'%');
                    }
                },'计划编号',3)->width(3);
                $filter->like('product_category_name','产品类型')->select('api/product-category')->width(2);
                $filter->equal('storage_out_status','出库进度')->select(config('plan.status'))->width(2);
                $filter->between('created_at')->date()->width(3);
            });
        });
    }
    public function countDetail($id){
        $title = "中转仓计件查看";
        $is_dialog = request()->dialog;
        $transit_storage_in = TransitStorageIn::findOrFail($id);
        $length=4;
        $info=[
            [
                'label'=>'派工单号',
                'value'=>$transit_storage_in->dispatch_no,
                'length'=>12
            ],

            [
                'label'=>'鞋跟型号',
                'value'=>$transit_storage_in->company_model,
                'length'=>$length
            ],
            [
                'label'=>'明细规格',
                'value'=>$transit_storage_in->spec,
                'length'=>$length
            ],
            [
                'label'=>'计件类型',
                'value'=>config('plan.transit_storage_count_type')[$transit_storage_in->count_type],
                'length'=>$length
            ],
            [
                'label'=>'计件时间',
                'value'=>$transit_storage_in->created_at,
                'length'=>$length
            ],
            [
                'label'=>'计件员工',
                'value'=>$transit_storage_in->personnel_name,
                'length'=>$length
            ],
            [
                'label'=>'计件数量',
                'value'=>$transit_storage_in->all_num,
                'length'=>$length
            ],
            [
                'label'=>'加工费用',
                'value'=>$transit_storage_in->inlect_mold_price,
                'length'=>$length
            ],
            [
                'label'=>'总加工费',
                'value'=>$transit_storage_in->inlect_mold_price*$transit_storage_in->all_num,
                'length'=>$length
            ],
        ];
        $reback = admin_url('zhongzhuan-jijian');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }
}
