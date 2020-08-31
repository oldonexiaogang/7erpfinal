<?php

namespace App\Admin\Controllers;

use App\Models\StandardDetail;
use App\Models\CompanyModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Carbon\Carbon;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Dcat\Admin\Controllers\AdminController;

class StandardDetailController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description(trans('admin.list'))
            ->body(function (Row $row) {
                $row->column(6, $this->grid());
                $row->column(6, function (Column $column) {
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('standard-detail'));
                    $form->selectResource('company_model_id')
                        ->path('dialog/company-model') // 设置表格页面链接
                        ->options(function ($v) { // 显示已选中的数据
                            if (!$v) return $v;
                            return CompanyModel::findOrFail($v)->pluck('company_model_name', 'id');
                        })->required()->oneline(true)->width(8,2);
                    $form->textarea('standard_detail_name')->required()
                        ->oneline(true)->width(8,2)->help('批量添加一行一个');
                    $form->hidden('company_model');
                    $column->append(Box::make(trans('common.new'), $form));
                });
            });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new StandardDetail(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->company_model;
            $grid->standard_detail_name;

            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('company_model')->width(4);
                $filter->like('standard_detail_name')->width(4);
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
        return Form::make(new StandardDetail(), function (Form $form) {
            $form->selectResource('company_model_id')
                ->path('dialog/company-model') // 设置表格页面链接
                ->options(function ($v) { // 显示已选中的数据
                    if (!$v) return $v;
                    return CompanyModel::findOrFail($v)->pluck('company_model_name', 'id');
                })->required();
            $form->text('standard_detail_name');
            $form->hidden('company_model');

            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
        });
    }
    public function storeH(Request $request)
    {

        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('standard-detail'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
    protected function saveH(Request $request, $id = null)
    {

        $data = $request->all();
        $arr = str_replace("\r\n",",",$data['standard_detail_name']);
        $spec_arr = explode(',',$arr);
        $company_model_id =$data['company_model_id'];
        $company_model_info = CompanyModel::find($company_model_id);
        $company_model_name =$company_model_info->company_model_name;
        $standardDetailModel = new StandardDetail();

        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        //添加
        try {
            foreach ($spec_arr as $k=>$v) {
                $num = $standardDetailModel->where('company_model_id',$company_model_id)
                    ->where('company_model',$company_model_name)
                    ->where('standard_detail_name',$v)
                    ->count();

                if ($num>0) {
                    return [
                        'message' => '【'.$v.'】已存在，请修改在提交',
                        'status'  => 'error',
                    ];
                }

                $insertData[] = [
                    'company_model_id'=>$company_model_id,
                    'company_model'=>$company_model_name,
                    'standard_detail_name'=>$company_model_name.'#'.$v,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }

            StandardDetail::insert($insertData);
            DB::commit();
            return [
                'message' => '成功',
                'status'  => 'success',
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status'  => 'error',
            ];
        }
    }
}
