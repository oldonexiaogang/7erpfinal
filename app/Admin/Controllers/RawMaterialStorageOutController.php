<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\RawMaterialStorageOutMultiDelete;
use App\Admin\Extensions\Tools\RawMaterialStorageOutMultiPrint;
use App\Models\RawMaterialStorageOut;
use App\Models\RawMaterialStorage;
use App\Models\PurchaseStandard;
use App\Models\RawMaterialStorageLog;
use App\Models\RawMaterialProductInformation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Controllers\AdminController;

class RawMaterialStorageOutController extends AdminController
{
    protected $storage_out_type = [
        'out'=>'外派注塑加工出库','in'=>'厂内产品加工出库'
    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RawMaterialStorageOut(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->raw_material_storage_out_no;
            $grid->column('date_at')->display(function (){
                return $this->date_at?date('Y-m-d',strtotime($this->date_at)):'';
            })->width("80px");
            $grid->raw_material_category_name;
            $grid->raw_material_product_information_no;
            $grid->raw_material_product_information_name;
            $grid->purchase_standard_name;
            $grid->column('num')->display(function (){
                return $this->num.'('. $this->unit.')';
            });
            $grid->column('change_coefficient_text','公斤数')->display(function (){
                return $this->num* $this->change_coefficient;
            });
            $grid->remark;
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->disableViewButton();
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                $batch->disableDelete();
            });
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->tools([
                new RawMaterialStorageOutMultiDelete('批量删除'),
                new RawMaterialStorageOutMultiPrint('批量打印'),
            ]);
            $grid->column('view','查看')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('raw-material-storage-out/'.$this->id.'?dialog=1'),
                        'value'=>'<i class="fa fa-search"></i>', 'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height')];
            });


            $grid->column('delete','删除')->display(function (){
                return '<a href="javascript:void(0);" data-url="'.admin_url('raw-material-storage-out/'.$this->id).'" data-action="delete">
                        <i class="feather icon-trash grid-action-icon"></i>
                    </a>';
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('raw_material_product_information_no')->width(2);
                $filter->like('raw_material_category_name')->width(2);
                $filter->like('raw_material_storage_out_no')->width(2);
                $filter->equal('type')->select($this->storage_out_type)->width(2);
                $filter->between('date_at')->date()->width(4);

            });
        });
    }

    protected function detail($id)
    {
        $is_dialog = request()->dialog;
        $title = "原材料出库查看";
        $data = RawMaterialStorageOut::find($id);
        $length=4;
        $info=[
            [
                'label'=>'出库单号',
                'value'=>$data->raw_material_storage_out_no,
                'length'=>$length
            ],
            [
                'label'=>'原材料编号',
                'value'=>$data->raw_material_product_information_no,
                'length'=>$length
            ],
            [
                'label'=>'原材料名称',
                'value'=>$data->raw_material_product_information_name,
                'length'=>$length
            ],
            [
                'label'=>'出库数量',
                'value'=>$data->num.$data->unit,
                'length'=>$length
            ],
            [
                'label'=>'领用厂家',
                'value'=>$data->apply_user_name,
                'length'=>$length
            ],
            [
                'label'=>'单价',
                'value'=>$data->price,
                'length'=>$length
            ],
            [
                'label'=>'出库类型',
                'value'=>$this->storage_out_type[$data->type],
                'length'=>$length
            ],
            [
                'label'=>'总金额',
                'value'=>sprintf('%.2f',$data->price*$data->num),
                'length'=>$length
            ],

            [
                'label'=>'出库日期',
                'value'=>$data->date_at,
                'length'=>$length
            ],
            [
                'label'=>'记录人',
                'value'=>$data->check_user_name,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$data->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('raw-material-storage-out');

        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new RawMaterialStorageOut(), function (Form $form) {
            $no = getOrderNo('raw_material_storage_out','',8,'raw_material_storage_out_no');
            $form->column(6, function (Form $form) use($no){
                $form->text('raw_material_storage_out_no')->default($no)->append('<div style="position: relative;top:8px;margin-left:20px;"><a href="'.admin_url('raw-material-storage').'">库存查看</a></div>');
                $form->radio('type')->options($this->storage_out_type)->default('in');
                $form->selectResource('raw_material_product_information_id')
                    ->path('dialog/raw-material-product-information') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return RawMaterialProductInformation::findOrFail($v)->pluck('raw_material_product_information_no', 'id');
                    })->required();
                $form->select('purchase_standard_id')->options('api/purchase-standard')->required();
                $uniqid = uniqid();
                $form->text('num')->default(0)->append(
                    <<<EQO
<label class="unit" id="unit_{$uniqid}" style="margin-left:10px;padding:8px 15px;border-radius:3px;border:1px solid #d9d9d9">&nbsp;</label>
 <label style="padding:8px ;"> x </label>
 <label class="change" id="change_{$uniqid}" style="margin-left:5px;padding:8px 15px ;border-radius:3px;border:1px solid #d9d9d9">&nbsp;</label>
 <script >
 $(function() {
    getinfo();
 })
 function getinfo(){

    $("#unit_{$uniqid}").text($("input[name=unit]").val())
    $("#change_{$uniqid}").text($("input[name=change_coefficient]").val())
 }
</script>
EQO
 );

                $form->datetime('date_at','出库时间')->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now());
            });

            $form->column(6, function (Form $form) use($no){
                $form->text('check_user_name')->required()->default(Admin::user()->name);
                $form->hidden('check_user_id')->required()->default(Admin::user()->id);
                $form->text('raw_material_product_information_name');
                $form->hidden('raw_material_product_information_no');
                $form->hidden('raw_material_category_id');
                $form->text('raw_material_category_name')->required();
                $form->text('price')->required();
                $form->hidden('purchase_standard_name');
                $form->text('total_price');
                $form->text('apply_user_name');
                $form->hidden('unit_id');
                $form->hidden('unit');
                $form->hidden('change_coefficient');
                $form->hidden('_token')->value(csrf_token());
            });
            $form->column(12, function (Form $form) use($no){
                $form->textarea('remark')->width(10,1);
            });
            $form->column(12, function (Form $form) {

                $form->html(function () {

                    return  <<<EHTML
<script >
$(function() {
  //选择数量

  $(document).on('change','.field_num',function() {
       calculatePrice()
  })
})
function calculatePrice() {

        var price =  $("input[name=price]").val()>0?$("input[name=price]").val():0;
        var num =  $("input[name=num]").val()>0?$("input[name=num]").val():0;
        var change_coefficient =  $("input[name=change_coefficient]").val()>0?$("input[name=change_coefficient]").val():0;
        var all_price = parseFloat(price)*parseFloat(num);
        all_price = all_price>0?all_price:0
        $("input[name=total_price]").val(all_price.toFixed(2))
    }
</script>
EHTML;
                },' ')->width(10,1);
            });
            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                // 去掉`继续编辑`checkbox
                $footer->disableEditingCheck();
                // 去掉`继续创建`checkbox
                $footer->disableCreatingCheck();
            });

            $form->disableViewButton();
            $form->disableDeleteButton();

            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                $query = RawMaterialStorageOut::query()->where('raw_material_storage_out_no',
                    $form->raw_material_storage_out_no);

                if($form->isEditing()){
                    $id = $form->getKey();
                    $query = $query->where('id','!=',$id);
                }
                $no_check = $query->count();
                //检测单号
                if($no_check>0){
                    return $form->error('申购单号已存在，请修改');
                }
                //检测库存
                $info = RawMaterialStorage::query()
                    ->where('raw_material_product_information_id',
                        $form->raw_material_product_information_id)
                    ->where('purchase_standard_id',$form->purchase_standard_id)->first();
                if(!$info||$info->num<$form->num){
                    return $form->error('库存不足');
                }
//                if(!$form->raw_material_product_information_name){
//                    $info = RawMaterialProductInformation::find($form->raw_material_product_information_id);
//                    $storage_out->raw_material_product_information_name = $info->raw_material_product_information_name;
//                    $storage_out->raw_material_category_name = $info->raw_material_category_name;
//                }
//                if(!$form->check_user_name){
//                    $storage_out->check_user_name = Admin::user()->name;
//                }
//                $storage_out->price = $form->price;
//                $storage_out->total_price = $form->total_price;;
               // dd($form);
            });

            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });
        });
    }
    private function afterSave($id,$form){
        $storage_out = RawMaterialStorageOut::find($id);
        if(!$form->purchase_standard_name){
            $standard = PurchaseStandard::find($form->purchase_standard_id);
            $storage_out->purchase_standard_name = $standard->purchase_standard_name;
        }
        //仓库变化
        $storage_info = RawMaterialStorage::where('raw_material_product_information_id', $form->raw_material_product_information_id)
            ->where('purchase_standard_id', $form->purchase_standard_id)
            ->first();
        $storage_info->num -=$form->num;
        $storage_info->save();
        //添加log
        //出入库记录
        if($form->num>0){
            $temp_data['type']='out';
        }else{
            $temp_data['type']='in';
        }
        $now= Carbon::now();
        $temp_data['raw_material_storage_id']=$storage_info->id;
        $temp_data['num']=abs($form->num);
        $temp_data['after_storage_num']= $storage_info->num;
        $temp_data['raw_material_product_information_id'] = $form->raw_material_product_information_id;
        $temp_data['raw_material_product_information_name']=$form->raw_material_product_information_name;
        $temp_data['raw_material_product_information_no']=$form->raw_material_product_information_no;
        $temp_data['check_user_id']=Admin::user()->id;
        $temp_data['check_user_name']=Admin::user()->name;
        $temp_data['from']='原材料仓库出库';
        $temp_data['type']='out';
        $temp_data['created_at']= $now;
        $temp_data['updated_at']= $now;
        if(abs($form->num)>0){
            DB::table('raw_material_storage_log')->insert($temp_data);
        }
        $storage_out->save();
    }

    /**
     * dec: 原材料打印预览
     * @param $id
     * @param Content $content
     * author : happybean
     * date: 2020-04-25
     */
    public function printPreviewMultiIndex(Request $request,Content $content){
        $id = $request->id;
        return $content
            ->title('原材料出库打印')
            ->row(function (Row $row) use ($id){
                $row->column(12, $this->printPriviewGrid($id));
            });
    }

    /**
     * dec:发货打印预览表格
     * author : happybean
     * date: 2020-04-22
     */
    protected function printPriviewGrid($id)
    {
        $idarr = explode(',',$id);
        return Grid::make(new RawMaterialStorageOut(), function (Grid $grid) use($idarr,$id) {
            if(count($idarr)>1){
                $grid->model()->whereIn('id',$idarr);
            }else{
                $grid->model()->where('id',$id);
            }

            $grid->model()->orderBy('created_at','desc');
            $grid->created_at;
            $grid->raw_material_category_name;
            $grid->raw_material_product_information_name;
            $grid->apply_user_name;
            $grid->purchase_standard_name;

            $grid->column('num','出库数量')->display(function (){
                return $this->num.$this->unit;
            });
            $grid->column('change_coefficient','公斤数')->display(function (){
                return $this->num*$this->change_coefficient;
            });

            $no =getOrderGang('raw_material_storage_out_paper','',8);
            $grid->header(function ($query) use($no,$id) {
                return '<div>
                            <p align="left">*请检查以下信息是否正确！单据号:<span class="text-danger">'.$no.'</span></p>
                            <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                                <a href="' . admin_url('raw-material-storage-out/print?id='.$id.'&no='.$no) . '" target="_blank" class="btn btn-sm btn-info" title="查看入库信息">
                                   <span class="hidden-xs">&nbsp;&nbsp;确认打印&nbsp;&nbsp;</span>
                                </a>
                            </div>
                            <div class="btn-group pull-left " style="margin-right: 10px">
                                <a href="' . admin_url('raw-material-storage-out') . '" class="btn btn-sm btn-info" title="取消打印">
                                   <span class="hidden-xs">&nbsp;&nbsp;取消打印&nbsp;&nbsp;</span>
                                </a>
                            </div>
                        </div>';
            });
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
        });
    }
}
