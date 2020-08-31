<?php

namespace App\Admin\Controllers;

use App\Models\MoldPrice;
use App\Models\MoldMaker;
use App\Models\MoldCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\Admin;
use Carbon\Carbon;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;
use App\Admin\Extensions\Tools\MoldPriceStatusMulti;
use App\Admin\Extensions\Tools\MoldPriceCheckMulti;
class MoldPriceController extends AdminController
{

    public function __construct(){
          $this->check_arr = config('plan.mold_price_check');
          $this->status_arr = config('plan.mold_price_status');
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new MoldPrice(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->column('date_at')->display(function (){
                return date('Y-m-d',strtotime($this->date_at));
            });
            $grid->mold_category_parent_name;
            $grid->mold_category_child_name;
            $grid->price;
            $grid->mold_maker_name;
            $grid->column('operation','操作')->display(function (){
                if($this->check==1){
                    return '-';
                }else{
                    $url= admin_url('mold-price/'.$this->id.'/edit?dialog=1');
                    Form::dialog('修改',$url)
                        ->click('#update_form_'.$this->id)
                        ->url($url)
                        ->width(config('plan.dialog.width'))
                        ->height(config('plan.dialog.height'))
                        ->saved(
                            <<<JS
JS
                        );
                    return "<a href='javascript:void(0)' id='update_form_".$this->id."'>
<i  class=\"feather icon-edit grid-action-icon\"></i></a>";
                }

            });
            $grid->column('chakan','查看')->dialog(function (){
                return ['type'=>'url',
                        'url'=>admin_url('mold-price/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<i class="feather icon-search grid-action-icon"></i>'
                ];
            });
            $grid->log_user_name;
            $grid->check->using(config('plan.mold_price_check_text'));
            $grid->status->using(config('plan.mold_price_status_text'));
            //批量验收
            $grid->batchActions(function ($batch) {
                $batch->add(new MoldPriceCheckMulti('验收'));
                $batch->add(new MoldPriceStatusMulti('禁用'));
            });
            $grid->withBorder();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableActions();

            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('mold_category_parent_id')->select('api/mold-category-parent')->width(2);
                $filter->equal('mold_category_child_id')->select('api/mold-category-child')->width(2);
                $filter->equal('check')->select($this->check_arr)->width(2);
            });
        });
    }

    /**
     * 详情
     */
    protected function detail($id)
    {
        $is_dialog = request()->dialog?:0;
        $title = "模具单价维护";
        $moldprice = MoldPrice::findOrFail($id);
        $length=4;
        $info=[
            [
                'label'=>'材料类别',
                'value'=>$moldprice->mold_category_parent_name,
                'length'=>$length
            ],
            [
                'label'=>'模具产品类别',
                'value'=>$moldprice->mold_category_child_name,
                'length'=>$length
            ],
            [
                'label'=>'单价',
                'value'=>$moldprice->price,
                'length'=>$length
            ],
            [
                'label'=>'记录人',
                'value'=>$moldprice->log_user_name,
                'length'=>$length
            ],
            [
                'label'=>'生成时间',
                'value'=>date('Y-m-d',strtotime($moldprice->date_at)),
                'length'=>$length
            ],
            [
                'label'=>'模具制造商',
                'value'=>$moldprice->mold_maker_name,
                'length'=>$length
            ],
            [
                'label'=>'状态',
                'value'=>$this->status_arr[$moldprice->status],
                'length'=>$length
            ],
            [
                'label'=>'是否验收',
                'value'=>$this->check_arr[$moldprice->check],
                'length'=>$length
            ],
            [
                'label'=>'图片',
                'value'=>$moldprice->image,
                'length'=>'img'
            ],
        ];
        $reback = admin_url('mold-price');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     *修改与添加表格
     */
    protected function form()
    {
        return Form::make(new MoldPrice(), function (Form $form) {
            $form->column(6, function (Form $form) {
                $form->date('date_at')->format('YYYY-MM-DD')->default(Carbon::now());
                $form->select('mold_category_parent_id')->options('api/mold-category-parent')->load('mold_category_child_id','api/mold-category-child');
                $form->text('price');
            });
            $form->column(6, function (Form $form) {
                $form->selectResource('mold_maker_id')
                    ->path('dialog/mold-maker') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return MoldMaker::findOrFail($v)->pluck('name', 'id');
                    })->required();
                $form->select('mold_category_child_id');
                $form->hidden('log_user_id')->default(Admin::user()->id);
                $form->text('log_user_name')->default(Admin::user()->name);
            });
            $form->column(12, function (Form $form) {
                $form->multipleImage('image')->width(10,1)->uniqueName()->autoUpload();
                $form->hidden('_token')->value(csrf_token());
            });
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
            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                $query = MoldPrice::query()
                    ->where('mold_maker_id',$form->mold_maker_id)
                    ->where('mold_category_parent_id',$form->mold_category_parent_id)
                    ->where('mold_category_child_id',$form->mold_category_child_id)
                    ->where('check','1')
                    ->where('status','1');

                if ($form->isCreating()) {
                    $check = $query->first();
                }elseif($form->isEditing()){
                    $id = $form->getKey();
                    $check =$query->where('price',$form->price)
                        ->where('id','!=',$id)->first();
                }
                if($check){
                    return $form->error('该模具单价已存在~');
                }
            });
            //检测单价3个条件是否有重复
            $form->submitted(function (Form $form) {
                // 获取用户提交参数
                $mold_category_parent_id = $form->mold_category_parent_id;
                $mold_category_child_id = $form->mold_category_child_id;
                $mold_maker_id = $form->mold_maker_id;

                $num = MoldPrice::where('mold_category_parent_id',$mold_category_parent_id)
                    ->where('mold_category_child_id',$mold_category_child_id)
                    ->where('mold_maker_id',$mold_maker_id)
                    ->where('status',1)
                    ->where('check',1)
                    ->count();
                if($num){
                    return $form->error('价格已存在,请先禁用其他');
                }

            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                $id = $form->getKey();
                if($form->mold_category_parent_id>0){
                    $mold_category_parent= MoldCategory::find($form->mold_category_parent_id);
                    $mold_category_child= MoldCategory::find($form->mold_category_child_id);
                    $mold_maker= MoldMaker::find($form->mold_maker_id);
                    $moldprice = MoldPrice::find($id);
                    $moldprice->mold_category_parent_name = $mold_category_parent->mold_category_name;
                    $moldprice->mold_category_child_name = $mold_category_child->mold_category_name;
                    $moldprice->mold_maker_name = $mold_maker->mold_maker_name;
                    $moldprice->save();
                }

            });
        });
    }
    public function apiSearchIndex(Request $request)
    {
        $mold_maker_id = $request->post('mold_maker_id');
        $mold_category_parent_id = $request->post('mold_category_parent_id');
        $mold_category_child_id = $request->post('mold_category_child_id');

        $result =  MoldPrice::where('mold_maker_id',$mold_maker_id)
            ->where('mold_category_parent_id',$mold_category_parent_id)
            ->where('mold_category_child_id',$mold_category_child_id)
            ->where('status','1')->where('check','1')
            ->first();
        if($result){
            return json_encode([
                'code'=>200,
                'msg'=>'获取成功',
                'data'=>[
                    'price'=>$result->price,
                ]
            ]);
        }else{
            return json_encode([
                'code'=>100,
                'msg'=>'不存在',
                'price'=>0,
            ]);
        }
    }

}
