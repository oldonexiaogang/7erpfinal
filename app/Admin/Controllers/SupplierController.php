<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\SupplierMultiDelete;
use App\Models\Supplier;
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

class SupplierController extends AdminController
{
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make(new Supplier(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->supplier_no->dialog(function () {
                return ['type'=>'url',
                        'url'=>admin_url('supplier/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<span style="text-decoration: underline;font-size: 12px">'.$this->supplier_no.'</span>'
                ];
            });
            $grid->supplier_name;
            $grid->pinyin;
            $grid->contact;
            $grid->tel;
            $grid->fax;
            $grid->column('delete','删除')->display(function (){
                if($this->check!=2){
                    return '<a href="javascript:void(0);" data-url="'.admin_url('supplier/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });
            $grid->batchActions(function ($batch) {
                $batch->add(new SupplierMultiDelete('批量删除'));
            });
            $grid->disableBatchDelete();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableViewButton();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('pinyin')->width(2);
                $filter->like('supplier_no')->width(2);

            });
        });
    }

    /**
     * 详情
     */
    protected function detail($id)
    {
        $title = "供应商";
        $is_dialog = request()->dialog;
        $supplier = Supplier::findOrFail($id);
        $length=4;
        $info=[
            [
                'label'=>'供应商编号',
                'value'=>$supplier->supplier_no,
                'length'=>$length
            ],
            [
                'label'=>'供应商名称',
                'value'=>$supplier->supplier_name,
                'length'=>$length
            ],
            [
                'label'=>'供应商拼音',
                'value'=>$supplier->pinyin,
                'length'=>$length
            ],
            [
                'label'=>'联系人',
                'value'=>$supplier->contact,
                'length'=>$length
            ],
            [
                'label'=>'电话',
                'value'=>$supplier->tel,
                'length'=>$length
            ],
            [
                'label'=>'邮箱',
                'value'=>$supplier->email,
                'length'=>$length
            ],

            [
                'label'=>'传真',
                'value'=>$supplier->fax,
                'length'=>$length
            ],
            [
                'label'=>'地址',
                'value'=>$supplier->address,
                'length'=>$length
            ],
            [
                'label'=>'所在银行',
                'value'=>$supplier->bank,
                'length'=>$length
            ],
            [
                'label'=>'银行账号',
                'value'=>$supplier->bank_account,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$supplier->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('supplier');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * 表单
     */
    protected function form()
    {
        return Form::make(new Supplier(), function (Form $form) {
            $supplier_no = getNo('supplier','GY',2,'supplier_no');

            $form->column(6, function (Form $form) use($supplier_no){
                $form->text('supplier_no')->default($supplier_no)->required();
                $form->text('pinyin')->required();
                $form->text('tel')->required();
                $form->text('fax');
                $form->text('bank');
            });
            $form->column(6, function (Form $form) {
                $form->text('supplier_name')->required();
                $form->text('contact')->required();
                $form->email('email');
                $form->text('address');
                $form->text('bank_account');
                $form->hidden('_token')->value(csrf_token());
            });
            $form->column(12, function (Form $form) {
                $form->textarea('remark')->width(10,1);
            });
            $form->submitted(function (Form $form) {
                // 删除用户提交的数据
                $form->deleteInput('_token');
            });
            $form->footer(function ($footer) {
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
        $grid = new IFrameGrid(new Supplier());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('supplier_name');
        $grid->supplier_name->width('80%');
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
            {supplier_id:'.$rowArray['id'].',
            supplier_name:\''.$rowArray['supplier_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=supplier_id]").empty().text(data.supplier_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('supplier_name')->width(6);
        });

        return $grid;
    }
}
