<?php

namespace App\Admin\Controllers;

use App\Models\MoldMaker;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Carbon\Carbon;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;
use  App\Admin\Extensions\Tools\MoldMakerMultiDelete;

class MoldMakerController extends AdminController
{
    /**
     * 列表书数据
     */
    protected function grid()
    {
        return Grid::make(new MoldMaker(), function (Grid $grid) {
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));

            $grid->model()->orderBy('created_at','desc');
            $grid->mold_maker_no->dialog(function (){
                return ['type'=>'url',
                        'url'=>admin_url('mold-maker/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<span style="text-decoration: underline;font-size: 12px">'.$this->mold_maker_no.'</span>'
                ];
            });
            $grid->pinyin;
            $grid->mold_maker_name;
            $grid->add_at;
//            $grid->column('operation_view','查看')
//                ->dialog(function (){
//                    return ['type'=>'url','url'=> admin_url('mold-maker'.'/'.$this->id.'?dialog=1'),
//                            'value'=>'<i class=" text-info feather icon-search grid-action-icon"></i>',
//                            'width'=>config('plan.dialog.width'),
//                            'height'=>config('plan.dialog.height')];
//                });
            $grid->column('delete','删除')->display(function (){
                return '<a href="javascript:void(0);" data-url="'.admin_url('mold-maker/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
            });
            $grid->batchActions(function ($batch) {
                $batch->add(new MoldMakerMultiDelete('批量删除'));
            });

            $grid->disableFilterButton();
            $grid->disableBatchDelete();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->disableViewButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('mold_maker_no')->width(2);
                $filter->like('pinyin')->width(2);
                $filter->like('mold_maker_name')->width(3);
                $filter->between('add_at')->date()->width(3);
            });
        });
    }

    /**
     * 详情
     */
    protected function detail($id)
    {
        $is_dialog = request()->dialog?:0;
        $title = "模具生产商";
        $moldMaker = MoldMaker::find($id);
        $length=4;
        $info=[
            [
                'label'=>'模具生产商代号',
                'value'=>$moldMaker->mold_maker_no,
                'length'=>$length
            ],
            [
                'label'=>'模具生产商名称',
                'value'=>$moldMaker->mold_maker_name,
                'length'=>$length
            ],
            [
                'label'=>'联系电话',
                'value'=>$moldMaker->tel,
                'length'=>$length
            ],
            [
                'label'=>'地址',
                'value'=>$moldMaker->address,
                'length'=>$length
            ],
            [
                'label'=>'邮箱',
                'value'=>$moldMaker->email,
                'length'=>$length
            ],
            [
                'label'=>'传真',
                'value'=>$moldMaker->fax,
                'length'=>$length
            ],[
                'label'=>'所在银行',
                'value'=>$moldMaker->bank,
                'length'=>$length
            ],
            [
                'label'=>'银行账号',
                'value'=>$moldMaker->bank_account,
                'length'=>$length
            ],
            [
                'label'=>'添加日期',
                'value'=>$moldMaker->add_at,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$moldMaker->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('mold-maker');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * 添加或修改的表格
     */
    protected function form()
    {
        return Form::make(new MoldMaker(), function (Form $form) {
            $mold_maker_no = getNo('mold_maker','MJ',2,'mold_maker_no');
            $form->column(6, function (Form $form) use($mold_maker_no){
                $form->text('mold_maker_no')->required()->rules(function ($form) {
                    // 如果不是编辑状态，则添加字段唯一验证
                    if (!$id = $form->model()->id) {
                        return 'unique:mold_maker,mold_maker_no';
                    }
                })->default($mold_maker_no);
                $form->text('pinyin')->required();
                $form->email('email');
                $form->text('address');
                $form->text('bank_account');
            });
            $form->column(6, function (Form $form) {
                $form->text('mold_maker_name')->required();
                $form->text('tel');
                $form->text('fax');
                $form->text('bank');
                $form->datetime('add_at')->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now());
            });
            $form->column(12, function (Form $form) {
                $form->textarea('remark')->width(10,1);
                $form->hidden('_token')->value(csrf_token());
            });
            $form->saving(function (Form $form) {
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
        $grid = new IFrameGrid(new MoldMaker());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('mold_maker_name');
        $grid->mold_maker_name->width('80%');
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
            {mold_maker_id:'.$rowArray['id'].',
            mold_maker_name:\''.$rowArray['mold_maker_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=mold_maker_id]").empty().text(data.mold_maker_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('mold_maker_name')->width(5);
        });

        return $grid;
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
        $result =  MoldMaker::where('mold_maker_name', 'like', "%$q%")->get();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (MoldMaker $data) {
            return ['id' => $data->id, 'text' => $data->mold_maker_name];
        });
        return $result;
    }
}
