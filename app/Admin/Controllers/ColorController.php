<?php

namespace App\Admin\Controllers;

use App\Models\Color;
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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dcat\Admin\Controllers\AdminController;

class ColorController extends AdminController
{
    public function index(Content $content)
    {
        $is_dialog = request()->dialog;

        if($is_dialog){
            $field= request()->field;
            return $content->body(function (Row $row) use($field){
                $row->column(12, $this->iFrameGrid($field));
            });
        }else{
            return $content
                ->title($this->title())
                ->description(trans('admin.list'))
                ->body(function (Row $row) {
                    $row->column(6, $this->grid());

                    $row->column(6, function (Column $column) {
                        $form = new \Dcat\Admin\Widgets\Form();
                        $form->action(admin_url('color'));
                        $form->text('color_name')->required();
                        $column->append(Box::make(trans('common.new'), $form));
                    });
                });
        }

    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Color(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->color_name;
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->disableEditButton();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('color_name')->width(4);
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
        return Form::make(new Color(), function (Form $form) {

            $form->text('color_name');

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
        $result =  Color::where('color_name', 'like', "%$q%")->orderBy('created_at','desc')->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (Color $data) {
            return ['id' => $data->id, 'text' => $data->color_name];
        });
        return $result;
    }

    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid($field='')
    {
        $grid = new IFrameGrid(new Color());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()
            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('color_name');
        $grid->color_name;
        $grid->showActions();
        $grid->disableRefreshButton();
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use($field) {
            $create->action('color');
            $create->otherField($field);
            $create->text('color_name', '颜色名称');
        });
        $grid->withBorder();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('color_name')->width(3);
        });

        return $grid;
    }
}
