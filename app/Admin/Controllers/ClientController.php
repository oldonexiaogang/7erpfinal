<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\ClientMultiDelete;
use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\ClientModel;
use App\Models\Personnel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Controllers\AdminController;

class ClientController extends AdminController
{
    protected $controllername = 'client';

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
                $row->column(12, $this->quickAddiFrameGrid($field));
            });
        }else{
            return $content
                ->title($this->title())
                ->description(trans('admin.list'))
                ->body(function (Row $row) {
                    $row->column(12, $this->grid());
                });
        }

    }
    /**
     *列表
     */
    protected function grid()
    {
        return Grid::make(new Client(), function (Grid $grid) {
            $controllername = $this->controllername;
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->model()->with(['client_category'])->orderBy('created_at','desc');
            $grid->client_no->dialog(function () {
                return ['type'=>'url',
                        'url'=>admin_url('client/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<span style="text-decoration: underline;font-size: 12px">'
                            .$this->client_no.'</span>'
                ];
            });
            $grid->pinyin;
            $grid->client_name;
            $grid->sales_name;
            $grid->column('client_category_id')->display(function (){
                return $this->client_category['name'];
            });
            $grid->add_at;
            $grid->column('delete','删除')->display(function () use($controllername){
                return '<a href="javascript:void(0);" data-url="'.admin_url($controllername.'/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
            });
            $grid->batchActions(function ($batch) {
                $batch->add(new ClientMultiDelete('批量删除'));
            });
            $grid->disableBatchDelete();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->toolsWithOutline(false);
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->like('client_no')->width(3);
                $filter->like('pinyin')->width(3);
                $filter->like('client_name')->width(3);
                $filter->between('add_at')->date()->width(4);
            });
        });
    }

    /**
     *详情
     */
    protected function detail($id)
    {
        $title = "客户信息";
        $is_dialog = request()->dialog;
        $client = Client::with(['client_category'])->findOrFail($id);
        $length=4;
        $info=[
            [
                'label'=>'客户代号',
                'value'=>$client->client_no,
                'length'=>$length
            ],
            [
                'label'=>'客户名称',
                'value'=>$client->client_name,
                'length'=>$length
            ],
            [
                'label'=>'业务员',
                'value'=>$client->sales_name,
                'length'=>$length
            ],
            [
                'label'=>'联系电话',
                'value'=>$client->tel,
                'length'=>$length
            ],
            [
                'label'=>'地址',
                'value'=>$client->address,
                'length'=>$length
            ],
            [
                'label'=>'客户类型',
                'value'=>$client->client_category->name,
                'length'=>$length
            ],
            [
                'label'=>'客户代号',
                'value'=>$client->email,
                'length'=>$length
            ],
            [
                'label'=>'传真',
                'value'=>$client->fax,
                'length'=>$length
            ],[
                'label'=>'所在银行',
                'value'=>$client->bank,
                'length'=>$length
            ],
            [
                'label'=>'银行账号',
                'value'=>$client->bank_account,
                'length'=>$length
            ],
            [
                'label'=>'添加日期',
                'value'=>$client->add_at,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$client->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url($this->controllername);
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * 表单
     */
    protected function form()
    {
        return Form::make(new Client(), function (Form $form) {

            $form->column(6, function (Form $form) {
                $client_no = getNo('client','KH',2,'client_no');
                $form->text('client_no')->required()->rules(function ($form) {
                    // 如果不是编辑状态，则添加字段唯一验证
                    if (!$id = $form->model()->id) {
                        return 'unique:client,client_no';
                    }
                })->default($client_no);
                $form->text('pinyin')->required();
                $form->text('tel');
                $form->text('fax');
                $form->select('client_category_id')->options(ClientCategory::selectOptions())->required();
                $form->text('bank_account');
            });
            $form->column(6, function (Form $form) {
                $form->text('client_name')->required();
                $form->select('sales_id')->options('api/personnel')->required();
                $form->hidden('sales_name');
                $form->email('email');
                $form->text('address');
                $form->text('bank');
                $form->datetime('add_at')->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now());
            });
            $form->column(12, function (Form $form) {
                $form->textarea('remark')->width(10,1);
                $form->hidden('_token')->value(csrf_token());
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

            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                $sales_id = $form->sales_id;
                $sales = Personnel::find($sales_id);
                $form->sales_name = $sales->name;
            });
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
        $grid = new IFrameGrid(new Client());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('client_name');
        $grid->client_name->width('80%');
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
            {client_id:'.$rowArray['id'].',
            client_name:\''.$rowArray['client_name'].'\'
            })" > 选择</a>
<script>

function chooseThis(data) {
    for(var key in data){
         $(window.parent.document).find("input[name="+key+"]").val(data[key]);
    }
    $(window.parent.document).find("div[name=client_id]").empty().text(data.client_name);
     const layerId = self.frameElement.getAttribute(\'id\');
    $(window.parent.document).find("#"+layerId).parent().parent().hide();

}
</script>
');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('client_name')->width(6);
        });

        return $grid;
    }

    /**
     *快速添加弹框
     */
    protected function quickAddiFrameGrid($field=''){
        $grid = new IFrameGrid(new Client());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('client_name');
        $grid->client_name->width('80%');
        $grid->showActions();
        $grid->disableRefreshButton();
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use($field) {
            $create->action('client');
            $create->otherField($field);
            $create->select('client_category_id')->options(ClientCategory::selectOptions())->width(3);
            $client_no = getNo('client','KH',2,'client_no');
            $create->text('client_no')->default($client_no);
            $create->text('client_name');
            $create->text('pinyin');
            $create->text('tel');
            $create->text('email');
            $create->select('sales_id')->options('api/personnel');
            $create->hidden('sales_name');
        });
        $grid->withBorder();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('client_name')->width(6);
        });

        return $grid;
    }
    /**
     * dec:接口调用
     * * @param Request $request
     * author : happybean
     * date: 2020-08-15
     */
    public function apiIndex(Request $request)
    {
        $q = $request->get('q');
        $result =  Client::where('client_name', 'like', "%$q%")->orderBy('created_at','desc')->get();
        $result = $result->map(function (Client $data) {
            return ['id' => $data->id, 'text' => $data->client_name];
        });
        return $result;
    }
    //根据客户带出业务员
    public function apiGetPersonnelIndex(Request $request)
    {
        $client_id = $request->post('client_id');
        $result =  Client::find($client_id);
        if($result){
            return json_encode([
                'code'=>200,
                'msg'=>'获取成功',
                'data'=>[
                    'personnel_id'=>$result->sales_id,
                    'personnel_name'=>$result->sales_name,
                ]
            ]);
        }else{
            return json_encode([
                'code'=>100,
                'msg'=>'客户不存在'
            ]);
        }
    }

}
