<?php

namespace App\Admin\Controllers;

use App\Models\RawMaterialStorage;
use App\Models\RawMaterialProductInformation;
use App\Models\PurchaseStandard;
use App\Models\RawMaterialStorageLog;
use App\Models\Supplier;
use App\Models\RawMaterialCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class RawMaterialStorageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RawMaterialStorage(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->raw_material_product_information_no;
            $grid->supplier_name;
            $grid->raw_material_product_information_name;
            $grid->raw_material_category_name;
            $grid->purchase_standard_name;
            $grid->column('change_coefficient')->display(function (){
                return $this->num* $this->change_coefficient;
            });
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('raw_material_category_name')->width(2);
                $filter->like('raw_material_product_information_no')->width(2);
                $filter->like('supplier_name')->width(2);

            });
            $grid->column('storate_detail','查看')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('raw-material-storage-log?raw_material_storage_id='.$this->id.'&dialog=1'),
                        'value'=>'<i class="fa fa-search"></i>', 'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height')];
            });

//            $grid->column('operation','操作')->display(function (){
//                return  '
//                    <a href="'.admin_url('raw-material-storage/'.$this->id.'/edit').'">
//                        <i class="feather icon-edit-1 grid-action-icon"></i>
//                    </a>
//                    ';
//
//            });
            $grid->header(function ($query) {
                return ' <a href="'.admin_url('raw-material-storage/create').'" class="btn btn-sm btn-info" title="新增">
                       <span class="hidden-xs">&nbsp;&nbsp;新增&nbsp;&nbsp;</span>
                    </a>
                        ';
            });
        });
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new RawMaterialStorage(), function (Form $form) {
            $form->column(6, function (Form $form) {
                $form->selectResource('raw_material_product_information_id', '原材料编号')
                    ->path('dialog/raw-material-product-information') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return RawMaterialProductInformation::findOrFail($v)->pluck('raw_material_product_information_no', 'id');
                    })->required();
                $form->hidden('raw_material_product_information_no');
                $form->hidden('raw_material_category_id');
                $form->hidden('unit_id');
                $form->hidden('unit');
                $form->hidden('price');
                $form->hidden('change_coefficient');
                $form->text('raw_material_category_name')->readonly();
                $form->hidden('color_id');
                $form->text('color')->readonly();
                $uniqid = uniqid();
                $form->text('num')->append(
                    <<<EOD
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
EOD
                );
            });
            $form->column(6, function (Form $form) {
                $form->text('raw_material_product_information_name')->readonly();
                $form->selectResource('supplier_id')
                    ->path('dialog/supplier') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return  Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->required();
                $form->hidden('supplier_name');
                $form->select('purchase_standard_id')->options('api/purchase-standard')->required();
                $form->hidden('purchase_standard_name');

                $form->hidden('_token')->value(csrf_token());
            });
            $form->submitted(function (Form $form) {
                // 删除用户提交的数据
                $form->deleteInput('_token');
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
    public function storeH(Request $request)
    {
        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('raw-material-storage'),
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
       // dd($data);
        DB::beginTransaction(); //开启事务
            try{
                $storage = new RawMaterialStorage();
                $storage_log_model = new RawMaterialStorageLog();
                $now = Carbon::now();
                //仓库查找，相同进行修改，不同进行添加
                $storage_info = $storage->where('supplier_id', $data['supplier_id'])
                    ->where('raw_material_product_information_id', $data['raw_material_product_information_id'])
                    ->where('raw_material_category_id', $data['raw_material_category_id'])
                    ->where('color_id', $data['color_id'])
                    ->where('purchase_standard_id', $data['purchase_standard_id'])
                    ->where('unit_id', $data['unit_id'])
                    ->where('change_coefficient', $data['change_coefficient'])
                    ->where('price', $data['price'])
                    ->first();

                    $changedata = [
                        'supplier_id'                           => $data['supplier_id'],
                        'supplier_name'                         => $data['supplier_name'],
                        'raw_material_product_information_id'   => $data['raw_material_product_information_id'],
                        'raw_material_product_information_name' => $data['raw_material_product_information_name'],
                        'raw_material_product_information_no'   => $data['raw_material_product_information_no'],
                        'raw_material_category_id'              => $data['raw_material_category_id'],
                        'raw_material_category_name'              => $data['raw_material_category_name'],
                        'purchase_standard_id'                  => $data['purchase_standard_id'],
                        'purchase_standard_name'                => PurchaseStandard::find($data['purchase_standard_id'])->purchase_standard_name,
                        'color_id'                              => $data['color_id'],
                        'color'                                 => $data['color'],
                        'unit'                                  => $data['unit'],
                        'unit_id'                               => $data['unit_id'],
                        'price'                                 => $data['price'],
                        'change_coefficient'                    => $data['change_coefficient'],
                        'num'                                   => $data['num'],
                        'created_at'                            => $now,
                        'updated_at'                            => $now,
                    ];


                    if ($storage_info) {
                        $changenum =$data['num'] -  $storage_info->num;

                        $storage_info->num=$data['num'];
                        $storage_info->save();
                        //出入库记录
                        if($changenum>0){
                            $temp_data['type']='in';
                        }else{
                            $temp_data['type']='out';
                        }
                        $temp_data['raw_material_storage_id']=$storage_info->id;
                        $temp_data['num']=abs($changenum);
                        $temp_data['after_storage_num']=$changedata['num'];
                        $temp_data['raw_material_product_information_id']=$changedata['raw_material_product_information_id'];
                        $temp_data['raw_material_product_information_name']=$changedata['raw_material_product_information_name'];
                        $temp_data['raw_material_product_information_no']=$changedata['raw_material_product_information_no'];
                        $temp_data['check_user_id']=Admin::user()->id;
                        $temp_data['check_user_name']=Admin::user()->name;
                        $temp_data['from']='仓库修改';
                        $temp_data['created_at']= $now;
                        $temp_data['updated_at']= $now;
                        if(abs($changenum)>0){
                            DB::table('raw_material_storage_log')->insert($temp_data);
                        }

                    } else {
                        $insertId = $storage->create($changedata);
                        if($data['num']){
                            $temp_data['type']='in';
                        }else{
                            $temp_data['type']='out';
                        }
                        $temp_data['raw_material_storage_id']=$insertId;
                        $temp_data['num']=abs($data['num']);
                        $temp_data['after_storage_num']=$data['num'];
                        $temp_data['raw_material_product_information_id']=$changedata['raw_material_product_information_id'];
                        $temp_data['raw_material_product_information_name']=$changedata['raw_material_product_information_name'];
                        $temp_data['raw_material_product_information_no']=$changedata['raw_material_product_information_no'];
                        $temp_data['check_user_id']=Admin::user()->id;
                        $temp_data['check_user_name']=Admin::user()->name;
                        $temp_data['from']='仓库修改';
                        $temp_data['created_at']= $now;
                        $temp_data['updated_at']= $now;
                        if(abs($data['num'])>0){
                            DB::table('raw_material_storage_log')->insert($temp_data);
                        }
                    }
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'data'=>[]
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }
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
                admin_url('raw-material-storage'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
}
