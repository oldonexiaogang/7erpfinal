<?php

namespace App\Admin\Controllers;

use App\Models\CraftColor;
use App\Models\ClientModel;
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

class CraftColorController extends AdminController
{
    /**
     * 列表
     * @param Content $content
     * @return Content
     */
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
                        $form->action(admin_url('craft-color'));
                        $form->selectResource('client_model_id')
                            ->path('dialog/client-model') // 设置表格页面链接
                            ->options(function ($v) { // 显示已选中的数据
                                if (!$v) return $v;
                                return ClientModel::findOrFail($v)->pluck('company_model_name', 'id');
                            })->required()->oneline(true)->width(8,2);
                        $form->textarea('craft_color_name')->required()
                            ->oneline(true)->width(8,2)->help('批量添加一行一个');
                        $form->hidden('client_model');
                        $column->append(Box::make(trans('common.new'), $form));
                    });
                });
        }

    }
    /**
     *列表数据
     */
    protected function grid()
    {
        return Grid::make(new CraftColor(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->client_model;
            $grid->craft_color_name;
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
                $filter->like('client_model')->width(4);
                $filter->like('craft_color_name')->width(4);
            });
        });
    }
    /**
     * 表单
     */
    protected function form()
    {
        return Form::make(new CraftColor(), function (Form $form) {
            if($form->isCreating()){
                $form->selectResource('client_model_id')
                    ->path('dialog/client-model') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return ClientModel::findOrFail($v)->pluck('client_model_name', 'id');
                    })->required()->oneline(true)->width(8,2);
            }
//            if($form->isEditing()){
//                $form->select('client_model_id')->options('/api/client-model')->width(3);
//            }
//            $form->hidden('client_model');
            $form->text('craft_color_name');

            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
        });
    }
    public function storeH(Request $request)
    {

        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('craft-color'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
    protected function saveH(Request $request, $id = null)
    {

        $data = $request->all();
        $arr = str_replace("\r\n",",",$data['craft_color_name']);
        $spec_arr = explode(',',$arr);
        $client_model_id =$data['client_model_id'];
        $client_model_info = ClientModel::find($client_model_id);
        $client_model_name =$client_model_info->client_model_name;
        $craftColorModel = new CraftColor();

        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        //添加
        try {
            foreach ($spec_arr as $k=>$v) {
                $num = $craftColorModel->where('client_model_id',$client_model_id)
                    ->where('client_model',$client_model_name)
                    ->where('craft_color_name',$v)
                    ->count();

                if ($num>0) {
                    return [
                        'message' => '【'.$v.'】已存在，请修改在提交',
                        'status'  => 'error',
                    ];
                }

                $insertData[] = [
                    'client_model_id'=>$client_model_id,
                    'client_model'=>$client_model_name,
                    'craft_color_name'=>$client_model_name.'#'.$v,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }

            CraftColor::insert($insertData);
            DB::commit();
            return [
                'message' => '成功',
                'status'  => 'success',
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status'  => 'error',
            ];
        }
    }
    /**
     * dec:api获取数据
     *  @param Request $request
     * author : happybean
     * date: 2020-04-19
     */
    public function apiByClientModelIndex(Request $request)
    {
        $client_model_id = $request->post('client_model_id');

        $result =  CraftColor::where('client_model_id',$client_model_id)->orderBy('created_at','desc')->get();

        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (CraftColor $data) {
            return ['id' => $data->id, 'text' => $data->craft_color_name];
        });

        if($result){
            return json_encode([
                'code'=>200,
                'msg'=>'获取成功',
                'data'=>$result
            ]);
        }else{
            return json_encode([
                'code'=>100,
                'msg'=>'获取失败',
            ]);
        }
    }


    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid($field='')
    {
        $grid = new IFrameGrid(new CraftColor());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()
            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('craft_color_name');
        $grid->client_model;
        $grid->craft_color_name;
        $grid->showActions();
        $grid->disableRefreshButton();
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use($field) {
            $create->action('craft-color');
            $create->otherField($field);
            $create->select('client_model_id')->options('/api/client-model')->width(3);
            $create->text('craft_color_name');
            $create->hidden('client_model');
        });
        $grid->withBorder();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('craft_color_name')->width(3);
            $filter->like('client_model')->width(3);
        });

        return $grid;
    }
}
