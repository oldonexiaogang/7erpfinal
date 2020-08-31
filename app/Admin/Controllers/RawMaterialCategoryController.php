<?php

namespace App\Admin\Controllers;

use App\Models\RawMaterialCategory;
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

class RawMaterialCategoryController extends AdminController
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
                    $bumenModel = new RawMaterialCategory();
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('raw-material-category'));
                    $form->select('parent_id')->options($bumenModel::selectOptions())->required();
                    $form->text('raw_material_category_name')->required();
                    $form->textarea('description');
                    $column->append(Box::make(trans('common.new'), $form));
                });
            });
    }
    /**
     * @return \Dcat\Admin\Tree
     */
    protected function treeView()
    {
        $menuModel = new RawMaterialCategory();

        $tree = new Tree(new $menuModel());

        $tree->disableCreateButton();
        $tree->disableEditButton();
        $tree->disableQuickCreateButton();

        $tree->branch(function ($branch) {
            $payload = "<strong>{$branch['raw_material_category_name']}</strong>";
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
        return Form::make(new RawMaterialCategory(), function (Form $form) {
            $form->select('parent_id')->options(RawMaterialCategory::selectOptions())->required();
            $form->text('raw_material_category_name')->required();
            $form->textarea('description');
        });
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
        $grid = new IFrameGrid(new RawMaterialCategory());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
       //$grid->rowSelector()->titleColumn('raw_material_category_name');
        $grid->raw_material_category_name->width('80%');
        $grid->disableRefreshButton();
        $grid->withBorder();
        $grid->disableRowSelector();
        $grid->disableEditButton();
        $grid->disableViewButton();
        $grid->disableQuickEditButton();
        $grid->disableDeleteButton();
        $grid->showActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // 当前行的数据数组
            $rowArray = $actions->row->toArray();
            $actions->append('<a href="#" onclick="chooseThis(
            {raw_material_category_id:'.$rowArray['id'].',
            raw_material_category_name:\''.$rowArray['raw_material_category_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=raw_material_category_id]").empty().text(data.raw_material_category_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('raw_material_category_name')->width(6);
        });
        return $grid;
    }
}
