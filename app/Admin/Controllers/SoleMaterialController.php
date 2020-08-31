<?php

namespace App\Admin\Controllers;

use App\Models\SoleMaterial;
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

class SoleMaterialController extends AdminController
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
                    $form->action(admin_url('sole-material'));
                    $form->hidden('sole_material_color_name');
                    $form->select('sole_material_color_id')->options('api/sole-material-color')->required()->width(8,2);
                    $form->text('sole_material_name')->required()->width(8,2);
                    $form->textarea('description')->width(8,2);
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
        return Grid::make(new SoleMaterial(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->sole_material_name;
            $grid->sole_material_color_name;
            $grid->description;
            $grid->disableFilterButton();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('sole_material_name')->width(4);
                $filter->equal('sole_material_color_id')->select('api/sole-material-color')->width(4);
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
        return Form::make(new SoleMaterial(), function (Form $form) {
            $form->select('sole_material_color_id')->options('api/sole-material-color')->required()->oneline(true)->width(8,2);
            $form->hidden('sole_material_color_name')->width(8,2);
            $form->text('sole_material_name')->required()->oneline(true)->width(8,2);
            $form->textarea('description')->oneline(true)->width(8,2);
            $form->submitted(function (Form $form) {
                $sole_material_color_id = $form->sole_material_color_id;
                $sole_material_name= $form->sole_material_name;
                $num = SoleMaterial::where('sole_material_color_id',$sole_material_color_id)
                    ->where('sole_material_name',$sole_material_name)
                    ->count();
                if($num>0){
                    return $form->error('用料已存在');
                }
                $color = SoleMaterialColor::find($sole_material_color_id);
                $form->sole_material_color_name = $color->sole_material_color_name;
            });
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
        $result =  SoleMaterial::where('sole_material_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (SoleMaterial $data) {
            return ['id' => $data->id, 'text' => $data->sole_material_name];
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
        $grid = new IFrameGrid(new SoleMaterial());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('sole_material_name');
        $grid->sole_material_name->width('80%');
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
            {sole_material_id:'.$rowArray['id'].',
            sole_material_name:\''.$rowArray['sole_material_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=sole_material_id]").empty().text(data.sole_material_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('sole_material_name')->width(6);
        });

        return $grid;
    }
}
