<?php

namespace App\Admin\Controllers;

use App\Models\MoldCategory;
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

class MoldCategoryController extends AdminController
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
                    $form->action(admin_url('mold-category'));
                    $form->select('parent_id')->options(MoldCategory::selectOptions());
                    $form->text('mold_category_name')->required();
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
        return Grid::make(new MoldCategory(), function (Grid $grid) {
            $grid->model()->with(['parent'])->orderBy('created_at','desc');
            $grid->column('parent_id')->display(function (){
                return $this->parent['mold_category_name'];
            });
            $grid->mold_category_name;
            $grid->withBorder();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('mold_category_name')->width(4);
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
        return Form::make(new MoldCategory(), function (Form $form) {
            $form->select('parent_id')->options(MoldCategory::selectOptions());
            $form->text('mold_category_name')->required();
            $form->hidden('order');
            $form->footer(function ($footer) {
                // 去掉`查看`checkbox
                $footer->disableViewCheck();
                // 去掉`继续编辑`checkbox
                $footer->disableEditingCheck();
                // 去掉`继续创建`checkbox
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
    public function apiParentIndex(Request $request)
    {
        $q = $request->get('q');
        $result =  MoldCategory::where('mold_category_name', 'like', "%$q%")
            ->where('parent_id',0)->get();
        $result = $result->map(function (MoldCategory $data) {
            return ['id' => $data->id, 'text' => $data->mold_category_name];
        });
        return $result;
    }

    public function apiChildIndex(Request $request)
    {
        $q = $request->get('q');
        $query = MoldCategory::query();
        if($q){
            $query=  $query->where('parent_id',$q);
        }else{
            $query=  $query->where('parent_id','>',0);
        }
        $result =  $query->get(['id', DB::raw('mold_category_name as text')]);
        return $result;
    }
}
