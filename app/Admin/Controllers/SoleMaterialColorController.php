<?php

namespace App\Admin\Controllers;

use App\Models\SoleMaterialColor;
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

class SoleMaterialColorController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description(trans('admin.list'))
            ->body(function (Row $row) {
                $row->column(6, $this->grid());

                $row->column(6, function (Column $column) {
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('sole-material-color'));
                    $form->text('sole_material_color_name')->required()->oneline(true)->width(8,2);
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
        return Grid::make(new SoleMaterialColor(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->sole_material_color_name;

            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->disableEditButton();
            $grid->withBorder();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('sole_material_color_name')->width(5);
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
        return Form::make(new SoleMaterialColor(), function (Form $form) {
            $form->text('sole_material_color_name');
            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
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
        $result =  SoleMaterialColor::where('sole_material_color_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (SoleMaterialColor $data) {
            return ['id' => $data->id, 'text' => $data->sole_material_color_name];
        });
        return $result;
    }
}
