<?php

namespace App\Admin\Controllers;

use App\Models\ClientCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Dcat\Admin\Controllers\AdminController;

class ClientCategoryController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description(trans('admin.list'))
            ->body(function (Row $row) {
                $row->column(6, $this->treeView()->render());
                $row->column(6, function (Column $column) {
                    $cateModel = new ClientCategory();
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('client-category'));
                    $form->select('parent_id')->options($cateModel::selectOptions())->required()->oneline(true)->width(8,2);
                    $form->text('name')->required()->oneline(true)->width(8,2);
                    $column->append(Box::make(trans('common.new'), $form));
                });
            });
    }
    /**
     * @return \Dcat\Admin\Tree
     */
    protected function treeView()
    {
        $menuModel = new ClientCategory();

        $tree = new Tree(new $menuModel());

        $tree->disableCreateButton();
        $tree->disableEditButton();
        $tree->disableQuickCreateButton();

        $tree->branch(function ($branch) {
            $payload = "<strong>{$branch['name']}</strong>";
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
        $clientCategoryModel = new ClientCategory();
        return Form::make($clientCategoryModel, function (Form $form) use($clientCategoryModel){
            $form->select('parent_id')->options($clientCategoryModel::selectOptions())->required()->oneline(true)->width(8,2);
            $form->text('name');
        });
    }
}
