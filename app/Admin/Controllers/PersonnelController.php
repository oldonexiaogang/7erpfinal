<?php

namespace App\Admin\Controllers;

use App\Models\Personnel;
use App\Models\Department;
use App\Models\Position;
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
use  App\Admin\Extensions\Tools\PersonnelMultiDelete;
class PersonnelController extends AdminController
{

    public function __construct(){
        $this->work_status = config('plan.personnel_work_status');
        $this->status = config('plan.personnel_status');
        $this->sex = config('plan.personnel_sex');
        $this->controllername = 'personnel';
    }
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make( new Personnel(), function (Grid $grid) {
            $controllername = $this->controllername;
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->model()->with(['department','position'])->orderBy('created_at','desc');
            $grid->personnel_no->dialog(function () use($controllername){
                return ['type'=>'url',
                        'url'=>admin_url($controllername.'/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<span style="text-decoration: underline;font-size: 12px">'.$this->personnel_no.'</span>'
                ];
            });
            $grid->name;
            $grid->sex->using($this->sex);
            $grid->column('department_id')->display(function (){
                return $this->department['department_name'];
            });
            $grid->column('position_id')->display(function (){
                return $this->position['position_name'];
            });
            $grid->work_status->using($this->work_status);

//            $grid->column('operation_view','查看')
//                ->dialog(function ()use($controllername){
//                    return ['type'=>'url','url'=> admin_url($controllername.'/'.$this->id.'?dialog=1'),
//                            'value'=>'<i class=" text-info feather icon-search grid-action-icon"></i>',
//                            'width'=>config('plan.dialog.width'),
//                            'height'=>config('plan.dialog.height')];
//                });
            $grid->column('delete','删除')->display(function () use($controllername){
                return '<a href="javascript:void(0);" data-url="'.admin_url($controllername.'/'.$this->id).'" data-action="delete">
                        <i class="feather icon-trash grid-action-icon"></i>
                    </a>';
            });
            $grid->batchActions(function ($batch) {
                $batch->add(new PersonnelMultiDelete('批量删除'));
            });
            // 禁用批量删除按钮
            $grid->disableBatchDelete();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('personnel_no')->width(2);
                $filter->like('name')->width(2);
                $filter->equal('work_status')->select($this->work_status)->width(2);
                $filter->equal('status')->select($this->status)->width(2);
                $filter->between('come_at')->date()->width(4);
                $filter->equal('department_id')->select('api/department')->width(2);
            });
        });
    }
    /**
     * 查看详情
    */
    protected function detail($id)
    {
        $title = "查看";
        $is_dialog = request()->dialog;
        $personnel = Personnel::with(['department','position'])->findOrFail($id);
        $length=6;
        $info=[
            [
                'label'=>'员工工号',
                'value'=>$personnel->personnel_no,
                'length'=>$length
            ],
            [
                'label'=>'员工名称',
                'value'=>$personnel->name,
                'length'=>$length
            ],
            [
                'label'=>'所属部门',
                'value'=>$personnel->department->department_name,
                'length'=>$length
            ],
            [
                'label'=>'所属职位',
                'value'=>$personnel->position->position_name,
                'length'=>$length
            ],
            [
                'label'=>'性别',
                'value'=>$this->sex[$personnel->sex],
                'length'=>$length
            ],
            [
                'label'=>'民族',
                'value'=>$personnel->nation,
                'length'=>$length
            ],
            [
                'label'=>'户口所在地',
                'value'=>$personnel->address,
                'length'=>$length
            ],
            [
                'label'=>'身份证号',
                'value'=>$personnel->idcard,
                'length'=>$length
            ],
            [
                'label'=>'出生年月',
                'value'=>$personnel->birthday_at,
                'length'=>$length
            ],
            [
                'label'=>'进厂日期',
                'value'=>$personnel->come_at,
                'length'=>$length
            ],

            [
                'label'=>'离厂日期',
                'value'=>$personnel->out_at,
                'length'=>$length
            ],
            [
                'label'=>'员工性质',
                'value'=>$this->work_status[$personnel->work_status],
                'length'=>$length
            ],
            [
                'label'=>'员工性质',
                'value'=>$this->status[$personnel->status],
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$personnel->remark,
                'length'=>$length
            ],

        ];
        $reback = admin_url($this->controllername);
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * 添加、修改的表单
     */
    protected function form()
    {
        return Form::make(new Personnel(), function (Form $form) {

            $form->column(6, function (Form $form) {
                $personnel_no = getNo('personnel','YG',2,'personnel_no');
                $form->text('personnel_no')->required()->rules(function ($form) {
                    // 如果不是编辑状态，则添加字段唯一验证
                    if (!$id = $form->model()->id) {
                        return 'unique:personnel,personnel_no';
                    }
                })->default($personnel_no);
                $form->radio('sex')->options($this->sex)->default('boy');
                $form->selectResource('department_id')
                    ->path('dialog/department') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Department::findOrFail($v)->pluck('department_name', 'id');
                    })->required();
                $form->text('idcard');
                $form->date('birthday_at')->format('YYYY-MM');
                $form->datetime('work_at')->format('YYYY-MM-DD')->required();
                $form->radio('work_status')->options($this->work_status)->default('formal');
            });
            $form->column(6, function (Form $form) {
                $form->text('name')->required();
                $form->text('nation')->default('汉');
                $form->selectResource('position_id')
                    ->path('dialog/position') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Position::findOrFail($v)->pluck('position_name', 'id');
                    })->required();
                $form->radio('status')->options($this->status)->default('on')->required();
                $form->datetime('come_at')->format('YYYY-MM-DD')->required();
                $form->datetime('out_at')->format('YYYY-MM-DD ');
                $form->text('address');
                $form->hidden('_token')->value(csrf_token());
            });

            $form->column(12, function (Form $form) {
                $form->text('remark')->width(10,1);
            });

            //检测单号是否存在
            $form->saving(function (Form $form) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    $no_num = Personnel::where('personnel_no',$form->personnel_no)->count();
                    if($no_num){
                        return $form->error('员工编号已存在,请修改~');
                    }
                }
                $form->deleteInput('_token');
            });
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
     * 接口获取数据
     */
    public function apiIndex(Request $request)
    {
        $q = $request->get('q');
        $result =  Personnel::where('name', 'like', "%$q%")->get();
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
        $grid = new IFrameGrid(new Personnel());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('name');
        $grid->name->width('80%');
        $grid->disableRefreshButton();
        $grid->withBorder();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('name')->width(6);
        });

        return $grid;
    }
}
