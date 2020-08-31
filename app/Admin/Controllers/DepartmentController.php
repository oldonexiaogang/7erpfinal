<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use App\Models\Personnel;
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

class DepartmentController extends AdminController
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
                $row->column(7, $this->treeView()->render());

                $row->column(5, function (Column $column) {
                    $bumenModel = new Department();
                    $form = new \Dcat\Admin\Widgets\Form();
                    $form->action(admin_url('department'));
                    $form->select('parent_id')->options($bumenModel::selectOptions())->required();
                    $form->text('department_name')->required();
                    $column->append(Box::make(trans('common.new'), $form));
                });
            });
    }
    /**
     * @return \Dcat\Admin\Tree
     */
    protected function treeView()
    {
        $menuModel = new Department();

        $tree = new Tree(new $menuModel());

        $tree->disableCreateButton();
        $tree->disableEditButton();
        $tree->disableQuickCreateButton();

        $tree->branch(function ($branch) {
            $payload = "<strong>{$branch['department_name']}</strong>";
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
        $departmentmodel = new Department();
        return Form::make($departmentmodel, function (Form $form) use($departmentmodel) {
            $form->select('parent_id')
                ->options($departmentmodel::selectOptions())
                ->required();
            $form->text('department_name');
            $form->text('order');
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
        $result =  Department::where('department_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (Department $data) {
            return ['id' => $data->id, 'text' => $data->department_name];
        });
        return $result;
    }

    /**
     * 通过部门查找员工
     * @param Request $request
     * @return mixed
     */
    public function apiIndexToPersonnel(Request $request)
    {
        $q = $request->get('q');
        $result =  Personnel::where('department_id',$q)->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (Personnel $data) {
            return ['id' => $data->id, 'text' => $data->name];
        });
        return $result;
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
        $grid = new IFrameGrid(new Department());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('department_name');
        $grid->department_name->width('80%');
        $grid->disableRefreshButton();
        $grid->disableRowSelector();
        $grid->disableEditButton();
        $grid->disableViewButton();
        $grid->disableQuickEditButton();
        $grid->disableDeleteButton();
        $grid->withBorder();
        $grid->showActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // 当前行的数据数组
            $rowArray = $actions->row->toArray();
            $actions->append('<a href="#" onclick="chooseThis(
            {department_id:'.$rowArray['id'].',
            department_name:\''.$rowArray['department_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=department_id]").empty().text(data.department_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('department_name')->width(6);
        });

        return $grid;
    }
}
