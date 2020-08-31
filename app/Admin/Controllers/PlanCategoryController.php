<?php

namespace App\Admin\Controllers;

use App\Models\PlanCategory;
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

class PlanCategoryController extends AdminController
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
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $plancategoryModel = new PlanCategory();
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('plan-category'));
                    $form->select('parent_id')->options($plancategoryModel::selectOptions())->required();
                    $form->text('plan_category_name')->required();
                    $column->append(Box::make(trans('common.new'), $form));
                });
            });
    }


    protected function treeView()
    {
        $menuModel = new PlanCategory();

        $tree = new Tree(new $menuModel());

        $tree->disableCreateButton();
        $tree->disableEditButton();
        $tree->disableQuickCreateButton();

        $tree->branch(function ($branch) {
            $payload = "<strong>{$branch['plan_category_name']}</strong>";
            return $payload;
        });

        return $tree;
    }



    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new PlanCategory(), function (Form $form) {
            $plancategoryModel = new PlanCategory();
            $form->select('parent_id')->options($plancategoryModel::selectOptions())->required();
            $form->text('plan_category_name')->required();
        });
    }

    /**
     * dec:接口调用
     * * @param Request $request
     * author : happybean
     * date: 2020-08-15
     */
    public function apiIndex(Request $request)
    {
        $q = $request->get('q');
        $result =  PlanCategory::where('plan_category_name', 'like', "%$q%")->get();
        $result = $result->map(function (PlanCategory $data) {
            return ['id' => $data->id, 'text' => $data->plan_category_name];
        });
        return $result;
    }
}
