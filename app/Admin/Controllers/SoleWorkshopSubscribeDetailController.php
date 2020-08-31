<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\SoleWorkshopSubscribeDetailStatusChange;
use App\Admin\Extensions\Grid\RowAction\SoleWorkshopSubscribeDetailCheckStatusChange;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\RawMaterialProductInformation;
use App\Models\SoleWorkshopSubscribe;
use App\Models\PurchaseStandard;
use App\Models\Supplier;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Carbon\Carbon;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;
use App\Admin\Extensions\Tools\SoleWorkshopSubscribeMultiPrint;
use App\Admin\Extensions\Tools\SoleWorkshopSubscribeMultiStorageIn;
use App\Admin\Extensions\Tools\SoleWorkshopSubscribeMultiCheck;
use App\Admin\Extensions\Tools\SoleWorkshopSubscribeMultiDelete;
use App\Admin\Extensions\Grid\RowAction\SoleWorkshopSubscribeCheck;

class SoleWorkshopSubscribeDetailController extends AdminController
{

    public function __construct(){
        $this->is_void_arr = config('plan.paper_void');
        $this->check_status_arr = config('plan.sole_workshop_subscribe_detail_check_status');
    }
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make(new SoleWorkshopSubscribeDetail(), function (Grid $grid) {
            $grid->model()->with('sole_workshop_subscribe')->orderBy('created_at','desc');
            $grid->fixColumns(2, -1);
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->column('date_at')->display(function (){
                return $this->sole_workshop_subscribe['date_at'];
            });
            $grid->column('sole_workshop_subscribe_no')->display(function (){
                return $this->sole_workshop_subscribe['sole_workshop_subscribe_no'];
            });
            $grid->column('supplier_name')->display(function (){
                return $this->sole_workshop_subscribe['supplier_name'];
            });
            $grid->column('raw_material_product_information_no')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_product_information_no'];
            });
            $grid->column('raw_material_category_name')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_category_name'];
            });
            $grid->column('raw_material_product_information_name')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_product_information_name'];
            });
            $grid->purcahse_standard_name;

            $grid->column('apply_num','申请数量')->display(function (){
                return $this->apply_num. $this->unit_name;
            });
            $grid->column('approval_num','批准数量')->display(function (){
                return $this->approval_num. $this->unit_name;
            });
            $grid->storage_in_num;
            $grid->column('change_coefficient','系数')->display(function (){
                return is_float_number($this->change_coefficient);
            });
            $grid->column('storage_in_num_kg','已入库公斤数')->display(function (){
                return $this->storage_in_num* $this->change_coefficient;
            });
            $grid->column('wait_storage_in_num','未入库数量')->display(function (){
                return ($this->apply_num-$this->storage_in_num);
            });
            $grid->column('wait_storage_in_num_kg','未入库公斤数')->display(function (){
                return ($this->apply_num-$this->storage_in_num) * $this->change_coefficient;
            });
            $grid->column('add_storage_in','创建入库单')->display(function (){
                if($this->is_void==0&&$this->check_status=='verify'&&$this->apply_num>$this->approval_num){
                    return '<a href="'.admin_url('sole-workshop-subscribe-storage-in').'/create/'.$this->id.'">
                       <i class="fa fa-plus" aria-hidden="true"></i>
                    </a>';
                }else{
                    return '-';
                }
            });
            $grid->check_status->action(SoleWorkshopSubscribeDetailCheckStatusChange::class);
            $grid->is_void->action(SoleWorkshopSubscribeDetailStatusChange::class);
            $grid->column('apply_user_name')->display(function (){
                return $this->sole_workshop_subscribe['apply_user_name'];
            });
            $grid->column('check_user_name','审核人')->display(function (){
                return $this->check_user_name;
            });
            $grid->column('check_time','审核时间')->display(function (){
                return $this->check_time;
            });
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableActions();
            $grid->disableQuickEditButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
            $grid->tableWidth('140%');
            $grid->toolsWithOutline(false);

            $grid->column('check','审核')->action(
                new SoleWorkshopSubscribeCheck());
            $grid->tools([
                new SoleWorkshopSubscribeMultiDelete('批量删除'),
                new SoleWorkshopSubscribeMultiCheck('批量验收'),
                new SoleWorkshopSubscribeMultiPrint('批量打印'),
                new SoleWorkshopSubscribeMultiStorageIn('批量入库')
            ]);
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if($actions->row->check_status!='unreviewed'&&$actions->row->check_status!='part'){
                    $actions->disableQuickEdit();
                }
            });
            $grid->header(function (){
                $all_approval_num = SoleWorkshopSubscribeDetail::where('is_void','0')
                    ->where('check_status','verify')
                    ->sum('approval_num');
                Form::dialog('新增鞋底车间申购')
                    ->click('.create-form-sole-workshop') // 绑定点击按钮
                    ->url('sole-workshop-subscribe/create') // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                    ->width('980px') // 指定弹窗宽度，可填写百分比，默认 720px
                    ->height('700px') // 指定弹窗高度，可填写百分比，默认 690px
                    ->success('Dcat.reload()'); // 新增成功后刷新页面
                return "
<div style='position:absolute;top:-30px;left:408px;'>
<button
class='create-form-sole-workshop btn btn-primary btn-mini btn-sm'>新增</button>
&nbsp;&nbsp;&nbsp;&nbsp;
<span style='position: relative;top:3px;' class='text-danger'>批准数量统计:".$all_approval_num."</span>
</div>

";
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                $batch->disableDelete();
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('sole_workshop_subscribe_no')->width(2);
                $filter->like('raw_material_product_information_no')->width(2);
                $filter->like('raw_material_product_information_name')->width(2);
                $filter->equal('check_status')->select($this->check_status_arr)->width(2);
                $filter->equal('supplier_id')
                    ->selectResource('dialog/supplier')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->width(2);
                $filter->between('created_at')->date()->width(4);
            });
            //导出
            $titles = [
                'date_at' => '申购日期',
                'sole_workshop_subscribe_no' => '申购编号',
                'supplier_name' => '供应商',
                'raw_material_product_information_no' => '原材料编号',
                'raw_material_category_name' => '原材料类型',
                'raw_material_product_information_name' => '原材料名称',
                'purcahse_standard_name' => '规格',
                'apply_num_text' => '申购数量',
                'approval_num_text' => '批准数量',
                'storage_in_num' => '已入库数量',
                'storage_in_num_kg' => '已入库公斤数',
                'wait_storage_in_num' => '未入库数',
                'wait_storage_in_num_kg' => '未入库公斤数',
                'apply_user_name' => '申请人',
                'check_user' => '审核人',
                'check_time' => '审核时间',
                'check_status' => '审核状态',
                'is_void' => '是否作废',
            ];
            $filename = '鞋底车间申购'.date('Y-m-d H:i:s');

            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['date_at'] = $row['sole_workshop_subscribe']['created_at'];
                    $row['sole_workshop_subscribe_no'] =$row['sole_workshop_subscribe']['sole_workshop_subscribe_no'];
                    $row['supplier_name'] =$row['sole_workshop_subscribe']['supplier_name'];
                    $row['raw_material_product_information_no'] =$row['sole_workshop_subscribe']['raw_material_product_information_no'];
                    $row['raw_material_category_name'] =$row['sole_workshop_subscribe']['raw_material_category_name'];
                    $row['raw_material_product_information_name'] =$row['sole_workshop_subscribe']['raw_material_product_information_name'];
                    $row['apply_num_text'] = $row['apply_num'].$row['unit_name'];
                    $row['approval_num_text'] = $row['approval_num'].$row['unit_name'];
                    $row['storage_in_num_kg'] = $row['storage_in_num']*$row['change_coefficient'];
                    $row['wait_storage_in_num'] =  $row['apply_num']-$row['storage_in_num'];
                    $row['wait_storage_in_num_kg'] = ($row['apply_num']-$row['storage_in_num'])*$row['change_coefficient'];
                    $row['apply_user_name'] = $row['sole_workshop_subscribe']['apply_user_name'];
                    $row['check_status'] =  config('plan.sole_workshop_subscribe_detail_check_status')[$row['check_status']];
                    $row['is_void'] = config('plan.paper_void')[$row['is_void']];
                }
                return $rows;
            })->xlsx();
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SoleWorkshopSubscribeDetail(), function (Form $form) {
            $no = getOrderNo('sole_workshop_subscribe','DC',10,'sole_workshop_subscribe_no');
            $id = $form->getKey();
            $info = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->find($id);
            $form->column(6, function (Form $form) use($no,$info){
                $form->text('sole_workshop_subscribe_no')->required()->value($info->sole_workshop_subscribe->sole_workshop_subscribe_no);
                $form->hidden('raw_material_category_id')->value($info->sole_workshop_subscribe->raw_material_category_id);
                $form->text('raw_material_category_name')->required()->readonly()
                    ->value($info->sole_workshop_subscribe->raw_material_category_name);

                $form->selectResource('supplier_id')
                    ->path('dialog/supplier_id') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->required()->value($info->sole_workshop_subscribe->supplier_id);
                $form->hidden('supplier_name')->value($info->sole_workshop_subscribe->supplier_name);
                $form->hidden('color_id')->value($info->sole_workshop_subscribe->color_id);
                $form->hidden('unit_name')->value($info->unit_name);
                $form->hidden('unit_id')->value($info->unit_id);
                $form->hidden('change_coefficient')->value($info->change_coefficient);
                $form->text('color')->readonly()->required()->value($info->sole_workshop_subscribe->color);;
                $form->text('apply_num','申购数量')->append("<div style='display: inline-block;margin-left:20px;'><label class=' label-h' style='height:36px;padding:0 10px;line-height:36px;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;'  >".$info->change_coefficient."</label >&nbsp;&nbsp;x&nbsp;&nbsp; <label class='label-h' style='height:36px;padding:0 10px;line-height:36px;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;' >".$info->unit_name."</label></div>");

            });
            $form->column(6, function (Form $form) use($info){
                $form->selectResource('raw_material_product_information_id','原材料编号')
                    ->path('dialog/raw-material-product-information') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return RawMaterialProductInformation::findOrFail($v)->pluck('raw_material_product_information_no', 'id');
                    })->required()->value($info->sole_workshop_subscribe->raw_material_product_information_id);
                $form->hidden('raw_material_product_information_no')->value($info->sole_workshop_subscribe->raw_material_product_information_no);
                $form->text('raw_material_product_information_name')->readonly()->required()->value($info->sole_workshop_subscribe->raw_material_product_information_name);
                $form->text('price')->append('<span style="position: relative;top:8px;">&nbsp;&nbsp;元</span>')->required()->readonly()->value($info->sole_workshop_subscribe->price);;
                $form->select('purcahse_standard_id')->options('/api/purchase-standard')->value($info->purchase_standard_id);
                $form->hidden('purcahse_standard_name');
                $form->text('total_price','总金额');
                $form->hidden('_token')->value(csrf_token());
            });
            $form->column(6, function (Form $form) use($info){
                $form->hidden('apply_user_id')->value($info->sole_workshop_subscribe->apply_user_id)->readonly();
                $form->text('apply_user_name')->value($info->sole_workshop_subscribe->apply_user_name)->readonly();
            });
            $form->column(6, function (Form $form) use($info){
                $form->datetime('date_at','申请时间')->format('YYYY-MM-DD HH:mm:ss')->default($info->sole_workshop_subscribe->date_at)->value($info->sole_workshop_subscribe->date_at)->required();
            });
            $form->column(12, function (Form $form) use($info){
                $form->textarea('subscribe_remark')->width(10,1)->value($info->sole_workshop_subscribe->subscribe_remark);
                $form->textarea('subscribe_content')->width(10,1)->value($info->sole_workshop_subscribe->subscribe_content);
            });
            $form->column(12, function (Form $form) {
                $form->html(function () {
                    return  <<<EHTML
<script >
$(function() {
  //选择数量
  $(document).on('change','.field_apply_num',function() {
       calculatePrice()
  })
})
function calculatePrice() {

        var price =  $("input[name=price]").val()>0?$("input[name=price]").val():0;
        var apply_num =  $("input[name=apply_num]").val()>0?$("input[name=apply_num]").val():0;
        var change_coefficient =  $("input[name=change_coefficient]").val()>0?$("input[name=change_coefficient]").val():0;
        var all_price = parseFloat(price)*parseFloat(change_coefficient)*parseFloat(apply_num);
        all_price = all_price>0?all_price:0
        $("input[name=total_price]").val(all_price.toFixed(2))
    }
</script>
EHTML;
                },' ')->width(10,1);
            });
            $form->footer(function ($footer) {
                // 去掉`查看`checkbox
                $footer->disableViewCheck();
                // 去掉`继续编辑`checkbox
                $footer->disableEditingCheck();
                // 去掉`继续创建`checkbox
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
        });
    }

    /**
     * 保存页面
     * @param ProductRequest $request
     * @return mixed
     */
    public function updateH(Request $request)
    {
        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('sole-workshop-subscribe-detail'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
    /**
     * 保存
     * @param Request $request
     * @param null $id
     * @return \App\Models\WorkshopPurchase
     */
    protected function saveH(Request $request, $id = null)
    {

        $data=$request->all();
        DB::beginTransaction(); //开启事务
        //添加
        $sole_workshop_subscribe = new SoleWorkshopSubscribe();
        $sole_workshop_subscribe_detail = new SoleWorkshopSubscribeDetail();
        try{
            $now = Carbon::now();
            $id= $request->id;
            $sole_workshop_subscribe_detail_info = $sole_workshop_subscribe_detail->find($id);
            $sole_workshop_subscribe_info =$sole_workshop_subscribe->find($sole_workshop_subscribe_detail_info->sole_workshop_subscribe_id);


            $detail_data['purcahse_standard_id']= $data['purcahse_standard_id'];
            $detail_data['purcahse_standard_name']= PurchaseStandard::find($data['purcahse_standard_id'])->purchase_standard_name;
            $detail_data['apply_num']=$data['apply_num'];
            $detail_data['unit_name']= $data['unit_name'];
            $detail_data['unit_id']= $data['unit_id'];
            $detail_data['change_coefficient']= $data['change_coefficient'];
            $detail_data['total_price']=$data['total_price'];
            $detail_data['price']= $data['price'];

            $sole_workshop_subscribe_detail_info->update($detail_data);
            $total_num = $sole_workshop_subscribe_detail->where('sole_workshop_subscribe_id',$sole_workshop_subscribe_detail_info->sole_workshop_subscribe_id)
                ->count('apply_num');
            $sole_workshop_subscribe_info->update([
                'sole_workshop_subscribe_no'=>$data['sole_workshop_subscribe_no'],
                'raw_material_product_information_no'=>$data['raw_material_product_information_no'],
                'raw_material_product_information_id'=>$data['raw_material_product_information_id'],
                'raw_material_product_information_name'=>$data['raw_material_product_information_name'],
                'raw_material_category_id'=>$data['raw_material_category_id'],
                'raw_material_category_name'=>$data['raw_material_category_name'],
                'supplier_id'=>$data['supplier_id'],
                'supplier_name'=>$data['supplier_name'],
                'price'=>$data['price'],
                'color_id'=>$data['color_id'],
                'color'=>$data['color'],
                'total_num'=>$total_num,
                'subscribe_remark'=>$data['subscribe_remark'],
                'subscribe_content'=>$data['subscribe_content'],
                'date_at'=>$data['date_at'],
            ]);
            $showdata  =$sole_workshop_subscribe_detail;
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'data'=>$showdata
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }

    //批量打印的预览
    //打印预览
    public function printPreviewMultiIndex(Request $request,Content $content){
        $id = $request->id;
        return $content
            ->title('鞋底车间申购打印')
            ->row(function (Row $row) use ($id){
                $row->column(12, $this->printPreviewGrid($id));
            });
    }
    /**
     * dec:鞋底车间申购预览表格
     * author : happybean
     * date: 2020-04-22
     */
    protected function printPreviewGrid($id)
    {
        $idarr = explode(',',$id);
        return Grid::make(new SoleWorkshopSubscribeDetail(), function (Grid $grid) use($idarr,$id) {
            if(count($idarr)>1){
                $grid->model()->with('sole_workshop_subscribe')->whereIn('id',$idarr);
            }else{
                $grid->model()->with('sole_workshop_subscribe')->where('id',$id);
            }
            $grid->column('sole_workshop_subscribe_no')->display(function (){
                return $this->sole_workshop_subscribe['sole_workshop_subscribe_no'];
            });
            $grid->column('raw_material_product_information_no')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_product_information_no'];
            });
            $grid->column('raw_material_category_name')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_category_name'];
            });
            $grid->column('raw_material_product_information_name')->display(function (){
                return $this->sole_workshop_subscribe['raw_material_product_information_name'];
            });
            $grid->purcahse_standard_name;
            $grid->column('supplier_name')->display(function (){
                return $this->sole_workshop_subscribe['supplier_name'];
            });
            $grid->column('apply_num')->display(function (){
                return $this->apply_num.$this->unit_name;
            });
            $grid->column('apply_user_name')->display(function (){
                return $this->sole_workshop_subscribe['apply_user_name'];
            });
            $no =getOrderGang('sole_workshop_subscribe_paper','',8,'no');
            $grid->header(function ($query) use($no,$id) {
                return '<div>
                            <p align="left">*请检查以下信息是否正确！单据号:<span class="text-danger">'.$no.'</span></p>
                            <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                                <a href="' . admin_url('sole-workshop-subscribe-detail/print?id='.$id.'&no='.$no) . '" target="_blank" class="btn btn-sm btn-info" title="查看入库信息">
                                   <span class="hidden-xs">&nbsp;&nbsp;确认打印&nbsp;&nbsp;</span>
                                </a>
                            </div>
                            <div class="btn-group pull-left " style="margin-right: 10px">
                                <a href="' . admin_url('sole-workshop-subscribe-detail') . '" class="btn btn-sm btn-info" title="取消打印">
                                   <span class="hidden-xs">&nbsp;&nbsp;取消打印&nbsp;&nbsp;</span>
                                </a>
                            </div>
                        </div>';
            });
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->toolsWithOutline(false);
            $grid->withBorder();
        });
    }


}
