<?php

namespace App\Admin\Controllers;

use App\Models\RawMaterialProductInformation;
use App\Models\RawMaterialCategory;
use App\Models\Supplier;
use App\Models\Color;
use App\Models\Unit;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Dcat\Admin\Controllers\AdminController;

class RawMaterialProductInformationController extends AdminController
{
    /**
     *列表数据
     */
    protected function grid()
    {
        return Grid::make(new RawMaterialProductInformation(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->raw_material_product_information_no;
            $grid->supplier_name;
            $grid->raw_material_product_information_name;
            $grid->raw_material_category_name;
            $grid->unit;

            $grid->material_level;
            $grid->color;
            $grid->standard;
            $grid->change_coefficient;
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->column('delete','删除')->display(function (){
                return '<a href="javascript:void(0);" data-url="'.admin_url('raw-material-product-information/'.$this->id).'" data-action="delete">
                        <i class="feather icon-trash grid-action-icon"></i>
                    </a>';
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('raw_material_product_information_no')->width(2);
                $filter->like('raw_material_product_information_name')->width(2);
                $filter->equal('raw_material_category_id')
                    ->selectResource('dialog/raw-material-category')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return RawMaterialCategory::findOrFail($v)->pluck('raw_material_category_name', 'id');
                    })->width(2);
                $filter->equal('supplier_id')
                    ->selectResource('dialog/supplier')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->width(2);

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
        return Form::make(new RawMaterialProductInformation(), function (Form $form) {
            $form->column(6, function (Form $form) {
                $form->text('raw_material_product_information_name')->required();
                $no = getNo('raw_material_product_information','C',1,'raw_material_product_information_no');
                $form->text('raw_material_product_information_no')->default($no)->required();
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('color_id')
                    ->options('api/color')
                    ->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeColor"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeColor').on('click',function (){
        layer.open({
          type: 2,
          title: '颜色',
          shadeClose: true,
          shade: false,
          maxmin: true, //开启最大化最小化按钮
          area: ['700px', '600px'],
          content: '/'+envheader+'/color?dialog=1&field=color_id'
        });

    })

})
function change_color_id(){
    var target = $('.field_color_id')
    let getturl = '/'+envheader+'/api/color'

    $.post(getturl,{_token:_token},function(data,ret) {
       if(ret=='success'){
           let target=$('.field_color_id');
           target.find("option").remove();
           target.select2({
                data: data,
               //默认空点选
            }).val(target.attr('data-value')).trigger('change');
       }
    })
}
</script>
EOD

                    );
                $form->hidden('color');
                $form->text('standard');
                $form->text('price');
            });
            $form->column(6, function (Form $form) {
                $form->selectResource('raw_material_category_id')
                    ->path('dialog/raw-material-category') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return RawMaterialCategory::findOrFail($v)->pluck('raw_material_category_name', 'id');
                    })->required();
                $form->hidden('raw_material_category_name');
                $form->selectResource('supplier_id')
                    ->path('dialog/supplier') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->required();
                $form->hidden('supplier_name');
                $form->text('material_level');
                $form->select('unit_id')->options('api/unit')->required();
                $form->hidden('unit');
                $form->text('change_coefficient')->required();
            });
            $form->column(12, function (Form $form) {
                $form->textarea('remark')->width(10,1);
                $form->hidden('_token')->value(csrf_token());
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
            $form->submitted(function (Form $form) {
                // 删除用户提交的数据
                $form->deleteInput('_token');
            });
            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });
        });
    }
    private function afterSave($id,$form){
        $raw_material = RawMaterialProductInformation::find($id);
        if(!$form->color){
            $client = Color::find($form->color_id);
            $raw_material->color = $client->color_name;
        }
        if(!$form->raw_material_category_name){
            $company_model = RawMaterialCategory::find($form->raw_material_category_id);
            $raw_material->raw_material_category_name = $company_model->raw_material_category_name;
        }
        if(!$form->supplier_name){
            $supplier = Supplier::find($form->supplier_id);
            $raw_material->supplier_name = $supplier->supplier_name;
        }
        if(!$form->unit){
            $unit = Unit::find($form->unit_id);
            $raw_material->unit = $unit->unit_name;
        }
        $raw_material->save();
    }
    /**
     * dec:弹框选择
     * author : happybean
     * date: 2020-04-19
     */
    public function dialogIndex(Content $content){
        return $content->body($this->iFrameGrid());

    }
    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
//    protected function iFrameGrid()
//    {
//        $grid = new IFrameGrid(new RawMaterialProductInformation());
//
//        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
//        $grid->model()->orderBy('created_at','desc');
//        $grid->rowSelector()->titleColumn('raw_material_product_information_no');
//        $grid->raw_material_product_information_no->width('40%');
//        $grid->raw_material_product_information_name->width('40%');
//        $grid->disableRefreshButton();
//        $grid->withBorder();
//        $grid->filter(function (Grid\Filter $filter) {
//            $filter->panel();
//            $filter->expand();
//            $filter->like('raw_material_product_information_no')->width(6);
//            $filter->like('raw_material_product_information_name')->width(6);
//        });
//
//        return $grid;
//    }
    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid()
    {
        $grid = new IFrameGrid(new RawMaterialProductInformation());
        $grid->model()->orderBy('created_at','desc');
        $grid->raw_material_product_information_no;
        $grid->raw_material_product_information_name;
        $grid->showActions();
        $grid->withBorder();
        $grid->disableRowSelector();
        $grid->disableEditButton();
        $grid->disableViewButton();
        $grid->disableDeleteButton();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // 当前行的数据数组
            $rowArray = $actions->row->toArray();
            $actions->append('<a href="#" onclick="chooseThis(
            {raw_material_product_information_id:'.$rowArray['id'].',
            raw_material_product_information_no:\''.$rowArray['raw_material_product_information_no'].'\',
            raw_material_product_information_name:\''.$rowArray['raw_material_product_information_name'].
                '\',raw_material_category_name:\''.$rowArray['raw_material_category_name'].'\',
                raw_material_category_id:\''.$rowArray['raw_material_category_id'].'\',
            color:\''.$rowArray['color'].'\',
            color_id:\''.$rowArray['color_id'].'\',
            price:'.$rowArray['price'].',
            supplier_name:\''.$rowArray['supplier_name'].'\',
            supplier_id:\''.$rowArray['supplier_id'].'\',
            standard:\''.$rowArray['standard'].'\',
            unit:\''.$rowArray['unit'].'\',
            unit_id:\''.$rowArray['unit_id'].'\',

            change_coefficient:\''.$rowArray['change_coefficient'].'\',
            })" > 选择</a>
<script>
var token =\''. csrf_token() .'\'

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    //change unit 符号需要修改
    parent.$(".change").each(function(){
        var that=this
        $(that).text(data.change_coefficient)
        $(that).val(data.change_coefficient)
     });
     parent.$(".unit").each(function(){
           var that=this
        $(that).text(data.unit)
        $(that).val(data.unit)
     });
     if (typeof(eval(" parent.calculatePrice"))=="function"){
        parent.calculatePrice();
     }

    $(window.parent.document).find("div[name=raw_material_product_information_id]").empty().text(data.raw_material_product_information_no);
    $(window.parent.document).find("div[name=supplier_id]").empty().text(data.supplier_name);
    //获取仓库数量
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();
//    $.post("/admin/api/getstore",{gongyingshang_name:data.gongyingshang_name,
//        raw_material_no:data.raw_material_no,purchase_spec_name:data.purchase_spec_name,
//        carft_color:data.carft_color,"_token":token},function(res) {
//
//        $(window.parent.document).find("input[name=kucun_num]").val(res.data);
//        const layerId = self.frameElement.getAttribute(\'id\');
//        $(window.parent.document).find("#"+layerId).parent().parent().hide();
//    })

}
</script>
');
        });
        $grid->disableRefreshButton();
        $grid->disableQuickEditButton();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('raw_material_product_information_no')->width(3);
            $filter->like('raw_material_product_information_name')->width(3);
            $filter->equal('raw_material_category_id')
                ->selectResource('dialog/raw-material-category')
                ->options(function ($v) { // options方法用于显示已选中的值
                    if (!$v) return $v;
                    return RawMaterialCategory::findOrFail($v)->pluck('raw_material_category_name', 'id');
                })->width(3);
        });

        return $grid;
    }
}
