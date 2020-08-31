<?php

namespace App\Admin\Controllers;

use App\Models\InjectMoldPrice;
use App\Models\CompanyModel;
use App\Models\ProductCategory;
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
use Dcat\Admin\Admin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class InjectMoldPriceController extends AdminController
{


    public function __construct(){
        $this->mold_types = config('plan.inject_mold_types');
        $this->out_nums = config('plan.inject_mold_out_nums');

    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new InjectMoldPrice(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->company_model;
            $grid->mold_type->using($this->mold_types);
            $grid->out_num->using($this->out_nums);
            $grid->product_feature;
            $grid->product_category_name;
            $grid->price;
            $grid->remark;
            $grid->check_user_name;
            $grid->column('delete','删除')->display(function (){
                if($this->num==0){
                    return '<a href="javascript:void(0);" data-url="'.admin_url('inject-mold-price/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });
            $grid->withBorder();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('company_model_id')
                    ->selectResource('dialog/company-model')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return CompanyModel::findOrFail($v)->pluck('company_model_name', 'id');
                    })->width(2);
                $filter->equal('product_category_id')->select('api/product-category')->width(2);
                $filter->equal('mold_type')->select($this->mold_types)->width(2);
                $filter->equal('out_num')->select($this->out_nums)->width(2);
                $filter->between('created_at')->datetime()->width(3);
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
        return Form::make(new InjectMoldPrice(), function (Form $form) {
            $form->column(6, function (Form $form){
                $form->selectResource('company_model_id')
                    ->path('dialog/company-model') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return CompanyModel::findOrFail($v)->pluck('company_model_name', 'id');
                    })->required();
                $form->hidden('company_model');
                $form->select('out_num')->options($this->out_nums);
                $form->text('product_feature');
                $form->hidden('check_user_id')->default(Admin::user()->id);
                $form->text('check_user_name')->default(Admin::user()->name)->required()->readonly();
            });
            $form->column(6, function (Form $form){
                $form->select('mold_type')->options($this->mold_types);
                $form->select('product_category_id')->options('api/product-category');
                $form->hidden('product_category_name');
                $form->text('price');

            });
            $form->column(12, function (Form $form){
                $form->textarea('remark')->width(10,1);
                $form->multipleImage('image')->width(10,1)->uniqueName()->autoUpload();
                $form->hidden('_token')->value(csrf_token());

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
            $form->submitted(function (Form $form) {
                // 删除用户提交的数据
                $form->deleteInput('_token');
            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });

        });

    }

    private function afterSave($id,$form){

        $priceinfo= InjectMoldPrice::find($id);
        if(!$form->company_model){
            $info = CompanyModel::find($form->company_model_id);
            $priceinfo->company_model = $info->company_model_name;
        }
        if(!$form->product_category_name){
            $company_model = ProductCategory::find($form->product_category_id);
            $priceinfo->product_category_name = $company_model->product_category_name;
        }
        $priceinfo->check_user_name = Admin::user()->name;
        $priceinfo->save();
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
    protected function iFrameGrid()
    {
        $grid = new IFrameGrid(new InjectMoldPrice());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->where('check_user_id','>',0)

            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('price');
        $grid->company_model;
        $grid->product_category_name;
        $grid->mold_type->using($this->mold_types);
        $grid->out_num->using($this->out_nums);
        $grid->price;
        $grid->disableRefreshButton();
        $grid->withBorder();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('company_model')->width(3);
            $filter->like('product_category_name')->width(3);
            $filter->equal('mold_type')->select($this->mold_types)->width(3);
            $filter->equal('out_num')->select($this->out_nums)->width(3);
        });

        return $grid;
    }
}
