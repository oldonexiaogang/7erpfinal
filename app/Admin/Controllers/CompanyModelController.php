<?php

namespace App\Admin\Controllers;

use App\Models\CompanyModel;
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

class CompanyModelController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CompanyModel(), function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'desc');
            $grid->company_model_name;
            $grid->status->bool(['1' => true, '0' => false]);

            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableBatchDelete();
            $grid->disableRowSelector();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('company_model_name');

            });
        });
    }

    /**
     * dec:弹框选择
     * author : happybean
     * date: 2020-04-19
     */
    public function dialogIndex(Content $content)
    {
        return $content->body($this->iFrameGrid());

    }

    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid()
    {
        $grid = new IFrameGrid(new CompanyModel());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->where('status', '1')->orderBy('created_at', 'desc');
        $grid->rowSelector()->titleColumn('company_model_name');
        $grid->company_model_name->width('80%');
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
            {company_model_id:' . $rowArray['id'] . ',
            company_model:\'' . $rowArray['company_model_name'] . '\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=company_model_id]").empty().text(data.company_model);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('company_model_name')->width(6);
        });

        return $grid;
    }

    public function pureApiIndex(Request $request)
    {
        $q      = $request->get('q');
        $query =  CompanyModel::where('status','1');
        if($q){
            $query->where('company_model_name', 'like',$q);
        }
        $result =$query->orderBy('created_at','desc')->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (CompanyModel $data) {
            return ['id' => $data->id, 'text' => $data->company_model_name];
        });
        return $result;

    }
}
