<?php

namespace App\Admin\Controllers;

use App\Models\Unit;
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

class UnitController extends AdminController
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
                    $form->action(admin_url('unit'));
                    $form->text('unit_name')->required()->width(9,2);
                    $form->textarea('description')->required()->width(9,2);
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
        return Grid::make(new Unit(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->unit_name;
            $grid->description;
            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableEditButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('unit_name')->width(4);
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
        return Form::make(new Unit(), function (Form $form) {
            $form->text('unit_name')->required();
            $form->textarea('description');
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
        $result =  Unit::where('unit_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (Unit $data) {
            return ['id' => $data->id, 'text' => $data->unit_name];
        });
        return $result;
    }
}
