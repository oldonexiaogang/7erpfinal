<?php

namespace App\Admin\Controllers;

use App\Models\PurchaseStandard;
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

class PurchaseStandardController extends AdminController
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
                    $form->action(admin_url('purchase-standard'));
                    $form->text('purchase_standard_name')->required();
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
        return Grid::make(new PurchaseStandard(), function (Grid $grid) {
            $grid->id->sortable();
            $grid->purchase_standard_name;

            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->withBorder();
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableRefreshButton();
            $grid->disableRowSelector();
            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('purchase_standard_name')->width(5);
        
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
        return Form::make(new PurchaseStandard(), function (Form $form) {

            $form->text('purchase_standard_name');
            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
        });
    }

    /**
     * dec:api获取数据
     *  @param Request $request
     * author : happybean
     * date: 2020-04-19
     */
    public function apiIndex(Request $request)
    {
        $q = $request->get('q');
        $result =  PurchaseStandard::where('purchase_standard_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (PurchaseStandard $data) {
            return ['id' => $data->id, 'text' => $data->purchase_standard_name];
        });
        return $result;
    }
}
