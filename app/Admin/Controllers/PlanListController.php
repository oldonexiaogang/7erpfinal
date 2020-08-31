<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\PlanListVoid;
use App\Models\ClientSoleInformation;
use App\Models\CraftInformation;
use App\Models\Delivery;
use App\Models\DispatchDetail;
use App\Models\PlanList;
use App\Models\Client;
use App\Models\CarftSkill;
use App\Models\CompanyModel;
use App\Models\ProductCategory;
use App\Models\ClientModel;
use App\Models\Personnel;
use App\Models\PlanCategory;
use App\Models\PlanListDetail;
use App\Models\DeliveryPaper;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Controllers\AdminController;
use App\Admin\Extensions\Tools\PlanListDispatchMultiPrint;
use App\Services\SoleDispatchService;
use App\Services\PaperService;
use App\Services\PrinterService;
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Extensions\Exports\GatherLeftExcelExpoter;
use App\Admin\Extensions\Exports\GatherRightExcelExpoter;
use App\Admin\Extensions\Exports\NoStorageOutExport;

class PlanListController extends AdminController
{

    public function __construct(){
        $this->plan_status = config('plan.plan_status_simple_html');
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PlanList(), function (Grid $grid) {
            $plan_list_model = new PlanList();
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->model()->orderBy('created_at','desc');
            $grid->column('created_at')->display(function ()  {
                return '<span  style="font-size:12px;">'.$this->created_at.'</span>';
            })->width("80px");
            $grid->plan_list_no->dialog(function (){
                return ['type'=>'url',
                        'url'=>admin_url('plan-list/'.$this->id.'?dialog=1'),
                        'width'=>'700px',
                        'height'=>'500px',
                        'value'=>'<span style="text-decoration: underline;font-size: 12px">'.$this->plan_list_no.'</span>'
                ];
            });
            $grid->client_name;
            $grid->client_order_no;
            $grid->product_time;
            $grid->company_model;
            $grid->client_model->dialog(function (){
                $img = CraftInformation::where('company_model',$this->company_model)
                    ->where('client_id',$this->client_id)
                    ->where('client_model',$this->client_model)
                    ->first();
                if($img){
                    $img = $img->sole_image;
                    return  ['type'=>'img','img'=>$img?$img[0]:'', 'width'=>'600px',
                             'value'=>'<span style="text-decoration: underline">'.$this->client_model.'</sapn>',
                             'height'=>'870px'];
                }else{
                    return  ['type'=>'text','content'=>'<h5 align=\'center\'>暂无图片</h5>','value'=>'<span style="text-decoration: underline">'.$this->kehu_model.'</sapn>',];
                }
            });
            $grid->product_category_name;
            $grid->craft_color_name;

            $grid->column('spec_num')->dialog(function (){
                $ordernum =$this->spec_num;
                $data_html='';
                for($i=33;$i<=41;$i++){
                    $data_html.='<td>'.getPlanListNumByCode($this->id,"33").'</td>';
                }
                return  ['type'=>'html','content'=>'
                    <table class=\'table custom-data-table dataTable table-bordered complex-headers \'>
                       <tr>
                            <td>订单编号</td>
                            <td colspan= \'2\'>'.$this->no.'</td>
                            <td>客户型号</td>
                            <td colspan= \'2\'>'.$this->client_model.'</td>
                             <td>雷力型号</td>
                            <td colspan= \'3\'>'.$this->company_model.'</td>
                        </tr>
                        <tr>
                            <td>码数</td>
                            <td>33码</td>
                            <td>34码</td>
                            <td>35码</td>
                            <td>36码</td>
                            <td>37码</td>
                            <td>38码</td>
                            <td>39码</td>
                            <td>40码</td>
                            <td>41码</td>
                        </tr>
                        <tr>
                        <td>数量</td>'.$data_html.'</tr>
                    </table>
                ','value'=>'<span style="text-decoration: underline">'.$ordernum.'</span>'];

            });
            $grid->column('delivery_num', '已发货')->dialog(function (){
                $num = $this->delivery_num;
                return ['type'=>'url','url'=> admin_url('delivery-log-by-plan/' . $this->id .'?dialog=1'),
                        'value'=>'<span style="text-decoration: underline">'.($num!=0?$num:'0').'</span>', 'width'=>'820px',
                        'height'=>'500px'];
            });

            $grid->column('copy','复制')->display(function (){
                $url = admin_url('plan-list/copy/'.$this->id);
                Form::dialog('复制',$url)
                    ->click('#copy_form_plan_list'.$this->id)
                    ->url($url)
                    ->width(config('plan.dialog.width'))
                    ->height(config('plan.dialog.height'))
                    ->success(
                        <<<JS
                    // 保存成功之后刷新页面
                    Dcat.reload();
JS
                    );
                return "<a class='text-info' id='copy_form_plan_list".$this->id."' >
 <i class=\"feather icon-copy grid-action-icon\"></i></a>";
            });
            $grid->column('oprateion','操作')->display(function (){
                if($this->status==0){
                    $url= admin_url('plan-list/'.$this->id.'/edit');
                    Form::dialog('修改',$url)
                        ->click('#plan_list_edit_form_'.$this->id)
                        ->url($url)
                        ->width('900px')
                        ->height('650px')
                        ->success(
                            <<<JS
                    // 保存成功之后刷新页面
                    Dcat.reload();
JS
                        );
                    return "<a class='text-info' id='plan_list_edit_form_".$this->id."' >
<i class=\"feather icon-edit grid-action-icon\"></i></a>";
                }else{
                    return '-';
                }
            });

            $grid->column('custom_dispatch', '订制派工')
                ->dialog(function (){
                if($this->status=='5'){
                    return ['type'=>'onlytext','value'=>'-'];
                }else{
                    return ['type'=>'url','url'=> admin_url('dispatch/' . $this->id .'?dialog=1'),
                            'value'=>'<span style="text-decoration: underline">订制派工</span>', 'width'=>'900px',
                            'height'=>'600px'];
                }
            });
            $grid->column('process')->display(function (){
               return config('plan.plan_process')[$this->process];
            });
//            $grid->column('status')->display(function (){
//                return config('plan.plan_status_simple_html')[$this->status];
//            });
            $grid->column('sole_status', __('鞋底派工'))->display(function ()  {
                return config('plan.plan_status_html')[$this->sole_status];
            });

            $grid->column('box_label_status', __('箱标派工'))->display(function (){
                return config('plan.plan_status_html')[$this->box_label_status];

            });
            $grid->column('inject_mold_status', __('注塑派工'))->display(function ()  {
                return config('plan.plan_status_html')[$this->inject_mold_status];
            });
            $grid->column('dispatch_detail', '派工详情')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('plan-list-diapatch/' . $this->id .'?dialog=1'),
                        'value'=>'<span style="text-decoration: underline">派工详情</span>', 'width'=>'900px',
                        'height'=>'600px'];
            });
            $grid->column('storage_out_status')->display(function () {
                return config('plan.plan_status_html')[$this->storage_out_status];
            });
            $grid->column('delete_operation')->display(function (){
                if($this->status==0){
                    return '<a href="javascript:void(0);" data-url="'.admin_url('plan-list/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });
            $grid->column('void','状态')->action(PlanListVoid::class);
            $grid->column('code_33', '33')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableView();
            });
            $grid->tools(
                new PlanListDispatchMultiPrint('批量派工打印')
            );
           // $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableActions();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->tableWidth('143%');
            $grid->paginate(15);
            $grid->header(function ($query) {
               // <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
//                        <a href="' . admin_url('plan-list/create') . '" class="btn btn-sm btn-info" title="新增计划单">
//                           <span class="hidden-xs">&nbsp;&nbsp;新增计划单&nbsp;&nbsp;</span>
//                        </a>
//                    </div>
                $all_num = PlanList::where('is_void','0')->sum('spec_num');
                $delivery_num = PlanList::where('is_void','0')->sum('delivery_num');
                return '
                    <div style="position: absolute;left:260px;top:-33px;">

                     <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('plan-list/gather') . '" class="btn btn-sm btn-info" title="汇总表预览">
                           <span class="hidden-xs">&nbsp;&nbsp;汇总表预览&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="javascript:void(0)" id="export_no_storage_out" class="btn btn-sm btn-info"  title="导出未出库信息（请先选择时间)">
                           <span class="hidden-xs">&nbsp;&nbsp;导出未出库信息（请先选择时间）&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a href="' . admin_url('plan-list-delivery-paper?_export_=all') . '" class="btn btn-sm btn-info" target="_blank" title="导出计划单对应的出货票据">
                           <span class="hidden-xs">&nbsp;&nbsp;导出计划单对应的出货票据&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    </div>
                    <div style="margin-top:3px;">
                        <label style="margin-left:0px;">计划单发货情况  </label>&nbsp; &nbsp;
                       <label >订单数量:<span class="text-danger">'.is_float_number($all_num).'</span>双 </label>&nbsp; &nbsp;
                       <label >已发数量:<span class="text-danger">'.is_float_number($delivery_num).'</span>双</label> &nbsp; &nbsp;
                       <label >未发数量:<span class="text-danger">'.is_float_number($all_num-$delivery_num).'</span>双</label>
                    </div>
                    <script >
                    $("#export_no_storage_out").on("click",function() {
                        var that=$(this)
                      var start = $("input[name=\'created_at[start]\']").val();
                      var end = $("input[name=\'created_at[end]\']").val();

                      if(!(start)||!(end)){
                          toastr.warning("请先选择时间")
                          return false;
                      }
                      window.open("'.admin_url('no-storage-out/export') .'?start="+start+"&end="+end,"_blank")

                    })
</script>
                    ';
            });

            //搜索
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->like('company_model','雷力型号')->width(2);
                $filter->multiInput('client_model',function ($qq){
                    if($this->input1){
                        $qq->orWhere('client_model','like','%'.$this->input1.'%');
                    }
                    if($this->input2){
                        $qq->orWhere('client_model','like','%'.$this->input2.'%');
                    }
                    if($this->input3){
                        $qq->orWhere('client_model','like','%'.$this->input3.'%');
                    }
                    if($this->input4){
                        $qq->orWhere('client_model','like','%'.$this->input4.'%');
                    }
                },'客户型号',3)->width(4);
                $filter->like('craft_color_name')->width(2);
                $filter->like('product_time')->width(2);
                $filter->equal('personnel_id')->select('api/personnel')->width(2);
                $filter->multiInput('plan_list_no',function ($qq){
                    if($this->input1){
                        $qq->orWhere('plan_list_no','like','%'.$this->input1.'%');
                    }
                    if($this->input2){
                        $qq->orWhere('plan_list_no','like','%'.$this->input2.'%');
                    }
                    if($this->input3){
                        $qq->orWhere('plan_list_no','like','%'.$this->input3.'%');
                    }
                    if($this->input4){
                        $qq->orWhere('plan_list_no','like','%'.$this->input4.'%');
                    }
                })->width(5);
                $filter->like('client_order_no')->width(2);
                //计划类型
                $filter->equal('plan_category_id')->select('/api/plan-category')->width(2);
                $filter->customSelect('need_to_deiver',["0"=>'全部','1'=>'是','2'=>'否'],function ($q){
                    if($this->chooseTrue==1){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->where('delivery_date','>=',$yesterday)
                            ->where('delivery_date','<=',$tomorrow);
                    }elseif($this->chooseTrue==2){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->orWhere('delivery_date','<',$yesterday)
                            ->orWhere('delivery_date','>',$tomorrow);
                    }
                },'急需发货')->width(2);
                $filter->equal('status')->select(config('plan.plan_status_simple'))->width(2);
                $filter->equal('product_category_id')
                    ->selectResource('dialog/product-category')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return ProductCategory::findOrFail($v)->pluck('product_category_name', 'id');
                    })->width(2);
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
            //导出
            $titles = [
                'created_at'=>'定制时间',
                'plan_list_no'=>'计划编号',
                'client_name'=>'客户名称',
                'client_order_no' => '客户计划单号',
                'product_time' => '生产周期',
                'company_model' => '雷力型号',
                'client_model' => '客户型号',
                'product_category_name' => '产品类型',
                'craft_color_name' => '工艺颜色',
                'spec_num' => '订单数量',
                'delivery_num' => '已发货',
                'process' => '计划进度',
                'sole_status' => '鞋底派工',
                'inject_mold_status' => '注塑派工',
                'box_label_status' => '箱标派工',
                'storage_out_status' => '中转出库',
                '33' => '33码',
                '34' => '34码',
                '35' => '35码',
                '36' => '36码',
                '37' => '37码',
                '38' => '38码',
                '39' => '39码',
                '40' => '40码',
                '41' => '41码',
            ];
            $filename = '计划单'.date('Y-m-d H:i:s');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                $plan_list_model = new PlanList();
                foreach ($rows as $index => &$row) {
                    $row['process'] = config('plan.plan_process')[$row['process']];
                    $row['sole_status'] = config('plan.status')[$row['sole_status']];
                    $row['inject_mold_status'] = config('plan.status')[$row['inject_mold_status']];
                    $row['box_label_status'] = config('plan.status')[$row['box_label_status']];
                    $row['storage_out_status'] = config('plan.status')[$row['storage_out_status']];
                    for ($i=33;$i<=41;$i++){
                        $arr = $plan_list_model->getDetailNum($row['id'],''.$i);
                        if($arr['left']>0||$arr['right']>0){
                            $row[''.$i] =  $arr['left'].'/'.$arr['right'];
                        }else{
                            $row[''.$i] =   $arr['all'];
                        }
                    }
                }
                return $rows;
            })->xlsx();






        });
    }

    /**
     * dec:计划单详情
     * author : happybean
     * date: 2020-04-29\
     */
    protected function detail($id)
    {
        $is_dialog = request()->dialog?:0;
        $title = "计划单";
        $order = PlanList::findOrFail($id);
        $length=6;
        $info=[
            [
                'label'=>'计划单编号',
                'value'=>$order->plan_list_no,
                'length'=>$length
            ],
            [
                'label'=>'客户',
                'value'=>$order->client_name,
                'length'=>$length
            ],
            [
                'label'=>'交货日期',
                'value'=>$order->delivery_date,
                'length'=>$length
            ],
            [
                'label'=>'雷力型号',
                'value'=>$order->company_model,
                'length'=>$length
            ],
            [
                'label'=>'客户订单号',
                'value'=>$order->client_order_no,
                'length'=>$length
            ],
            [
                'label'=>'客户型号',
                'value'=>$order->client_model,
                'length'=>$length
            ],
            [
                'label'=>'生产周期',
                'value'=>$order->product_time,
                'length'=>$length
            ],
            [
                'label'=>'产品类型',
                'value'=>$order->product_category_name,
                'length'=>$length
            ],
            [
                'label'=>'工艺类型',
                'value'=>$order->craft_skill_name,
                'length'=>$length
            ],
            [
                'label'=>'工艺颜色',
                'value'=>$order->craft_color_name,
                'length'=>$length
            ],
            [
                'label'=>'业务员',
                'value'=>$order->personnel_name,
                'length'=>$length
            ],
            [
                'label'=>'计划类型',
                'value'=>$order->plan_category_name,
                'length'=>$length
            ],
            [
                'label'=>'尺码规格',
                'value'=>$order->spec->toArray(),
                'length'=>'planListoneLine'
            ],
            [
                'label'=>'计划描述',
                'value'=>$order->plan_describe,
                'length'=>12
            ],
            [
                'label'=>'刀模',
                'value'=>$order->knife_mold,
                'length'=>12
            ],
            [
                'label'=>'革片',
                'value'=>$order->leather_piece,
                'length'=>12
            ],
            [
                'label'=>'沿条',
                'value'=>$order->welt,
                'length'=>12
            ],
            [
                'label'=>'鞋跟',
                'value'=>$order->sole,
                'length'=>12
            ],
            [
                'label'=>'出面',
                'value'=>$order->out,
                'length'=>12
            ],
            [
                'label'=>'注塑要求',
                'value'=>$order->inject_mold_ask,
                'length'=>12
            ],
            [
                'label'=>'工艺要求',
                'value'=>$order->craft_ask,
                'length'=>12
            ],
            [
                'label'=>'计划说明',
                'value'=>$order->plan_remark,
                'length'=>12
            ],
            [
                'label'=>'图片',
                'value'=>$order->image,
                'length'=>'img'
            ],
        ];
        $reback = admin_url('plan_list');
        $types = config('plan.type_text');
        return view('admin.common.show', compact('title','info','reback','is_dialog','types'));
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $specs = config('plan.spec');
        $types = config('plan.type');
        return Form::make(new PlanList(), function (Form $form) use($specs,$types){
            $form->column(6, function (Form $form) {
                $plan_list_no = getOrderNo('plan_list', '',8,'plan_list_no');
                $form->text('plan_list_no')->default($plan_list_no);
                $form->datetime('delivery_date')->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now());
                $form->text('client_order_no');
                $form->text('product_time');
                $form->hidden('carft_skill_name');
                $form->hidden('client_sole_information_id')->default(0);
                $form->selectResource('carft_skill_id')
                    ->path('dialog/craft-skill')// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return CarftSkill::findOrFail($v)->pluck('carft_skill_name', 'id');
                    })->required();
                $form->hidden('personnel_id');
                $form->text('personnel_name')->readonly();
            });
            $form->column(6, function (Form $form) {
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->hidden('client_name');
                $add_url = 'client/create';
                $uniqid = uniqid();
                Form::dialog('添加客户资料',$add_url)
                    ->click('#add_client_of_plan_list_'.$uniqid)
                    ->url($add_url)
                    ->width(config('plan.dialog.width'))
                    ->height(config('plan.dialog.height'))
                    ->success(
                        <<<JS
                    // 保存成功之后刷新页面
                  //  Dcat.reload();
layer.close()
change_client_id();
JS
                    );
                $form->select('client_id')->options('api/client')
                    ->load('company_model_id','api/company-model-and-client')
                    ->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info fa fa-plus dialog-create"
id="add_client_of_plan_list_{$uniqid}"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
function change_client_id(){
    var target = $('.field_client_id')
    let getturl = '/'+envheader+'/api/client'
    $.post(getturl,{_token:_token},function(data,ret) {
       if(ret=='success'&&data.length>0){

           let target=$('.field_client_id');
           target.find("option").remove();
           target.select2({
                data: data,
               //默认空点选
            }).val(target.attr('data-value')).trigger('change');
       }
    })
}
</script>
EOD

                    );
                $form->select('company_model_id')
                    ->required();
                $form->hidden('company_model');
                $form->select('client_model_id')->required();
                $form->hidden('client_model');

                $form->select('craft_color_id')->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor_form_plan_list"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor_form_plan_list').on('click',function (){
        layer.open({
          type: 2,
          title: '工艺颜色',
          shadeClose: true,
          shade: false,
          maxmin: true, //开启最大化最小化按钮
          area: ['700px', '600px'],
          content: '/'+envheader+'/craft-color?dialog=1&field=craft_color_id'
        });

    })

})
function change_craft_color_id(){
    var target = $('.field_craft_color_id')
    let getturl = '/'+envheader+'/api/craft-color-by-client-model'
    var client_model_id = $('select[name=client_model_id]').val()

    $.post(getturl,{_token:_token,client_model_id:client_model_id},function(data,ret) {

        data = JSON.parse(data)
       if(data.code==200&&data.data.length>0){
           let target=$('.field_craft_color_id');
           target.find("option").remove();
           target.select2({
                data: data.data,
               //默认空点选
            }).val(target.attr('data-value')).trigger('change');
       }
    })
}
</script>
EOD

                    );
                $form->hidden('craft_color_name');
                $form->select('product_category_id')->options('/api/product-category')->required();
                $form->hidden('product_category_name');
                $form->select('plan_category_id')->options('/api/plan-category')->required();
                $form->hidden('plan_category_name');
                if($form->isEditing()){
                    $form->hidden('_token')->value(csrf_token());
                }
            });
            $form->column('12',function (Form $form) use ($specs,$types){
                $form->html(function () use($specs,$types,$form){
                    if($form->isEditing()){
                        $id = $form->getKey();
                        $result =  PlanListDetail::where('plan_list_id',$id)->get();
                        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
                        $sizes = $result->map(function (PlanListDetail $data) {
                            return ['id' => $data->id,
                                    'spec' => $data->spec,
                                    'type' => $data->type,
                                    'num' => $data->num,
                                ];
                        });
                        $sizes = json_encode($sizes);
                        //查询鞋底客户资料中的码数
                        $choose_id = PlanList::find($id)->client_sole_information_id;
                        $code_info = ClientSoleInformation::find($choose_id);
                        $start_code=$code_info->start_code;
                        $end_code=$code_info->end_code;
                    }else{
                        $id=0;
                        $sizes=json_encode([]);
                        $start_code=0;
                        $end_code=0;
                    }
                    $is_edit = $form->isEditing()?1:0;
                    $specs = json_encode($specs);
                    $types = json_encode($types);
                    $envheader=getenv('ADMIN_ROUTE_PREFIX');
                    $csrf=csrf_token();
                    return  <<<EHTML
        <style>
        .spec-top{background: #487cd0;color:#fff;padding:10px 25px}
        .spec-title{position: relative;top:2px;padding-right:5px;}
        #spec-table-{$id} tr td,#total tr td{text-align: center}
        #spec-table-{$id} tr td span{
        display: inline-block;
            margin-right:10px;
        }
        .input-h{height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
        select {width:100px}
        .total{}
        #plan_order_num_{$id}{background: #fff}
        </style>
        <div class="spec-top " style="">
            <span class="spec-title">请选择规格/尺码数量</span>
            <select name="" id="plan_order_num_{$id}" class=" col-md-1 select">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
            </select>
        </div>
        <div class="spec-body" id="spec-body">
            <table id="spec-table-{$id}" class="table">

            </table>
        </div>
        <div class="total">
          <table  class="table" id="total">
               <tr>
                  <td width="48%" colspan="2" >
                   <span class="text-danger" id="show_code_field" style="display:none" >* 码数超出范围请确认</span>
                   </td>
                   <td  width="52%">合计数:<span id="total_num">0</span></td>
                </tr>
            </table>
        </div>
        <hr>
         <div  style="width:500px;margin:0 auto;text-align: center">
                <button class="btn btn-primary submit"><i class="feather icon-save"></i> 提交</button>
                <button style="margin-left:20px;" type="reset" class="btn btn-white"><i class="feather icon-rotate-ccw"></i> 取消</button>
            </div>
        <script >
     //获取尺码和规格
        var specs  ={$specs};
        var types  ={$types};
        var sizes  ={$sizes};
        var is_edit  ={$is_edit};
        var _token  ="{$csrf}";
        var start_code = {$start_code};
        var end_code = {$end_code};
        var typeHtml = '';
        $.each(types,function(index,data) {
          typeHtml+='<option value="'+data.type+'">'+data.text+'</option>'
        })
    $(function() {
        //初始化一行
        if(is_edit==1){
              var hasSpec = {$sizes};
              var specHtml = '';
              $('#plan_order_num_{$id}').val(hasSpec.length)
            $.each(hasSpec,function(index,data) {
                specHtml+='<tr>' +
               '<td>'+
                '<span>规格/尺码</span>' +
                 '<input name="specarr[id][]" type="hidden"  value="'+data.id+'" />' +
                  '<select name="specarr[spec][]" class="input-h spec_select" value="'+data.spec+'">'+chooseSpecs(data.spec)+'</select>'+
                  '<select name="specarr[type][]"  class="input-h" value="'+data.type+'">'+chooseTypes(data.type)+'</select>'+
                '</td>' +
                '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="'+data.num+'" /></td>' +
           '</tr>';
            })
            $('#spec-table-{$id}').append(specHtml)
             calcunum()
             checkoutrange(start_code,end_code)
        }else{
            var id={$id}
            let showhtml = ''
            for(j=33;j<=41;j++){
                let is_selected = specs[1][0]==j?'selected':''
                showhtml+='<option value="'+j+'" '+is_selected+'>'+j+'</option>';
            }
              $('#spec-table-{$id}').append(oneline(showhtml))
             calcunum()
              checkoutrange(start_code,end_code)
        }
         //切换数字，表格变化
         $(document).on('change','#plan_order_num_{$id}',function() {

              var that = this;
              var new_spec_num = $(that).val()
              var new_specs = specs[new_spec_num]
              var appendhtml = '';

              $('#spec-table-{$id}').empty();

              for(i=0;i<new_spec_num;i++){
                let showhtml = ''
                for(j=33;j<=41;j++){
                    let is_selected = new_specs[i]==j?'selected':''
                    showhtml+='<option value="'+j+'" '+is_selected+'>'+j+'</option>';
                }
                appendhtml+=oneline(showhtml)
              }

               $('#spec-table-{$id}').append(appendhtml)
                //检测是否超出范围
               checkoutrange(start_code,end_code)
         })
         //数量变化
          $(document).on('blur','.spec_num',function() {
              calcunum()
          })
          //客户携带出业务员
            $('.field_client_id').on('change',function(e) {
                var client_id = $('select[name=client_id]').val();
                let posturl = '/'+"{$envheader}"+'/api/client-personnel'
                let token = "{$csrf}"
                $.post(posturl,{client_id:client_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       $(document).find('input[name=personnel_name]').val(res.data.personnel_name)
                      $(document).find('input[name=personnel_id]').val(res.data.personnel_id)
                   }
                })
           })
       //客户+雷力型号定位 工艺单中的客户型号
          $('.field_client_id,.field_company_model_id').on('change',function(e) {
            var client_id = $('select[name=client_id]').val();
            var company_model_id = $('select[name=company_model_id]').val();
            if(client_id>0&&company_model_id>0){

                let posturl = '/'+"{$envheader}"+'/api/craft-information-client-model'
                let token = "{$csrf}"
                $.post(posturl,{client_id:client_id,company_model_id:company_model_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       let target=$('.field_client_model_id');
                        target.find("option").remove();
                       target.select2({
                            data: res.data,
                           //默认空点选
                        }).val(target.attr('data-value')).trigger('change');
                   }
                })
            }
          })
          //客户型号变化，客户颜色跟着变化
          $('.field_client_model_id').on('change',function(e) {
            var client_model_id = $('select[name=client_model_id]').val();
            if(client_model_id>0){
                let posturl = '/'+"{$envheader}"+'/api/craft-color-by-client-model'
                let token = "{$csrf}"
                $.post(posturl,{client_model_id:client_model_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       let target=$('.field_craft_color_id');
                         target.find("option").remove();
                       target.select2({
                            data: res.data,
                           //默认空点选
                        }).val(target.attr('data-value')).trigger('change');
                   }
                })
            }
          })

          //码数select 变化
          $(document).on('change', '.spec_select', function () {

                checkoutrange(start_code,end_code)
            })


    //确定客户+雷力型号+客户型号+工艺颜色后定位一条客户鞋底资料
     $('.field_client_id,.field_company_model_id,.field_client_model_id,.field_craft_color_id,.field_product_category_id').on("change", function(e) {
            var field_client_id = $('select[name=client_id]').val()
            var field_company_model_id =$('select[name=company_model_id]').val()
            var field_client_model_id = $('select[name=client_model_id]').val()
            var field_craft_color_id = $('select[name=craft_color_id]').val()
            var field_product_category_id = $('select[name=product_category_id]').val()

            if(field_client_id>0&&field_company_model_id>0&&field_client_model_id>0&&field_craft_color_id>0&&field_product_category_id>0){
               //发送ajax 请求规定的数据
                 let posturl = '/'+"{$envheader}"+'/api/plan-list-load-client-sole';
                $.post(posturl,{
                client_id:field_client_id,
                company_model_id:field_company_model_id,
                client_model_id:field_client_model_id,
                product_category_id:field_product_category_id,
                craft_color_id:field_craft_color_id,
                '_token':_token},function(res) {
                     res = JSON.parse(res);
                     if(res.code==200){
                            $(document).find('input[name=client_sole_information_id]').val(res.data.id)
                            $(document).find('input[name=client_name]').val(res.data.client_name)
                            $(document).find('input[name=company_model]').val(res.data.company_model)


                            $(document).find('input[name=client_model]').val(res.data.client_model)
                            $(document).find('input[name=craft_color_name]:not(.filter_column__craft_color_name)').val(res.data.craft_color_name)
                            $(document).find('input[name=product_category_name]').val(res.data.product_category_name)

                            $(document).find('input[name=knife_mold]').val(res.data.knife_mold)
                            $(document).find('input[name=leather_piece]').val(res.data.leather_piece)
                            $(document).find('input[name=welt]').val(res.data.welt)
                            $(document).find('input[name=inject_mold_ask]').val(res.data.inject_mold_ask)
                            $(document).find('input[name=plan_describe]').val(res.data.remark)
                            $(document).find('input[name=craft_ask]').val(res.data.craft_ask)
                            $(document).find('input[name=out]').val(res.data.out)
                            start_code = res.data.start_code
                            end_code = res.data.end_code
                            checkoutrange(start_code,end_code)
                        }else{
                            toastr.error(res.msg)
                        }
                    });
                }
            })
        })

    function chooseSpecs(choose=''){
          var specHtml = '';
          var num = $('#plan_order_num_{$id}').val()
          var choose_specs = specs[num]

          for(j=33;j<=41;j++){
                let is_selected = choose==j?'selected':''
                specHtml+='<option value="'+j+'" '+is_selected+'>'+j+'</option>';
            }
         return specHtml;
     }

    function chooseTypes(choose=0){
          var typesHtml = '';
          var num = $('#plan_order_num_{$id}').val()
          var choose_types = types

          $.each(choose_types,function(index,data) {
              var is_selected = choose==data.type?'selected':''
              typesHtml+='<option value="'+data.type+'" '+is_selected+'>'+data.text+'</option>'
         })
         return typesHtml;
     }
    function oneline(guigeHtml){
         var oneline = '<tr>' +
           '<td>'+
            '<span>规格/尺码</span><select name="specarr[spec][]" class="input-h spec_select">'+guigeHtml+'</select>'+
            '&nbsp;&nbsp;&nbsp;&nbsp;<select name="specarr[type][]" class="input-h">'+typeHtml+'</select>'+
            '</td>' +
            '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="0"/></td>' +
       '</tr>';
         return oneline;
    }
   function calcunum() {
        var num=0;
        $.each($('.spec_num'),function() {
            var check_num = isNaN($(this).val())?0:$(this).val()
            num+=parseFloat(check_num);
        })
        $('#total_num').text(num)
   }
   function checkoutrange(start_code,end_code){
   console.log(start_code,end_code)
        var show_tag =0;

        $('select.spec_select').each(function(index,data){
            let this_code = $(data).val()
            if(!(this_code>=start_code&&this_code<=end_code)){
                show_tag +=1;
            }
        })
        if(show_tag>0){
              $('#show_code_field').show();
        }else{
          $('#show_code_field').hide();
        }
   }

</script>
EHTML;
                },' ')->oneline('true')->width(10,1);
            });

            $form->column(12, function (Form $form) {
                $form->hidden('spec_num');
                $form->text('plan_describe')->width(10,1);
                $form->text('knife_mold')->width(10,1);
                $form->text('leather_piece')->width(10,1);
                $form->text('welt')->width(10,1);
                $form->text('out')->width(10,1);
                $form->text('inject_mold_ask')->width(10,1);
                $form->text('craft_ask')->width(10,1);
                $form->text('plan_remark')->width(10,1);
                $form->image('image')->width(10,1);
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

            $form->submitted(function (Form $form) {
                $form->deleteInput('_token');
                $query = PlanList::query()->where('plan_list_no',
                    $form->plan_list_no);

                if($form->isEditing()){
                    $id = $form->getKey();
                    $query = $query->where('id','!=',$id);
                }
                $no_check = $query->count();
                //检测单号
                if($no_check>0){
                    return $form->error('订单号已存在，请修改');
                }
                //检测规格
                if(isset($form->specarr['num']) && count($form->specarr['num'])){
                    $form->spec_num = array_sum($form->specarr['num']);
                }else{
                    return $form->error('请选择规格');
                }

            });
            $form->saving(function (Form $form){
                $form->deleteInput('_token');
            });
            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });
        });

    }
    private function afterSave($id,$form){
        $plan_list = PlanList::find($id);
        if(!$form->client_name){
            $client = Client::find($form->client_id);
            $plan_list->client_name = $client->client_name;
        }
        if(!$form->company_model_name){
            $company_model = CompanyModel::find($form->company_model_id);
            $plan_list->company_model = $company_model->company_model_name;
        }
        if(!$form->client_model_name){
            $client_model = ClientModel::find($form->client_model_id);
            $plan_list->client_model = $client_model->client_model_name;
        }
        if(!$form->product_category_name){
            $product_category = ProductCategory::find($form->product_category_id);
            $plan_list->product_category_name = $product_category->product_category_name;
        }
        if(!$form->craft_color_name){
            $craft_color = CraftColor::find($form->craft_color_id);
            $plan_list->craft_color_name = $craft_color->craft_color_name;
        }
        if(!$form->personnel_name){
            $personnel= Personnel::find($form->personnel_id);
            $plan_list->personnel_name = $personnel->name;
        }
        if(!$form->plan_category_name){
            $plancategory= PlanCategory::find($form->plan_category_id);
            $plan_list->plan_category_name = $plancategory->plan_category_name;
        }
        if(!$form->carft_skill_name){
            $carftskill= CarftSkill::find($form->carft_skill_id);
            $plan_list->carft_skill_name = $carftskill->carft_skill_name;
        }

        $client_sole_info= ClientSoleInformation::find($plan_list->client_sole_information_id);
        $plan_list->sole = $client_sole_info->sole;

        //记录specarr
       if ($form->isCreating()) {
           $data=[];
           foreach ( $form->specarr['spec'] as $kk=>$vv){
               if($form->specarr['num'][$kk]>0){
                   $data[$kk]['plan_list_id']= $id;
                   $data[$kk]['spec']= $vv;
                   $data[$kk]['type']= $form->specarr['type'][$kk];
                   $data[$kk]['num']= $form->specarr['num'][$kk];
                   $data[$kk]['created_at']= Carbon::now();
                   $data[$kk]['updated_at']= Carbon::now();
               }else{
                   continue;
               }
           }
           PlanListDetail::insert($data);
       }
       if($form->isEditing()){
           //修改数据
           $data = [];
           foreach ( $form->specarr['spec'] as $kk=>$vv){
               $data[$kk]['id']= $form->specarr['id'][$kk];
               $data[$kk]['plan_list_id']= $id;
               $data[$kk]['spec']= $vv;
               $data[$kk]['type']= $form->specarr['type'][$kk];
               $data[$kk]['num']= $form->specarr['num'][$kk];
           }
           batchUpdate($data,'plan_list_detail');
       }
        $plan_list->save();
    }

    /**
     * 复制首页跳转
     */
    public function copyIndex(){
       return  redirect(admin_url("plan-list"));
    }
    /**
     * dec: 复制计划单
     * @param $id
     * @param Content $content
     * author : happybean
     * date: 2020-05-21
     */
    public function copyData($id,Content $content){
        $from = request()->from?:'';
        return $content
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->body($this->formCopy($id,$from));
    }

    /**
     * dec: 复制操作
     * @param $id
     * @param $from
     * author : happybean
     * date: 2020-06-13
     */
    protected function formCopy($id,$from)
    {
        $plan_list_info = PlanList::find($id);
        $plan_list_id = $id;
        //获取尺码
        $specs = config('plan.spec');
        $types = config('plan.type');
        return Form::make(new PlanList(), function (Form $form) use($specs,$types,$plan_list_info,$from,$plan_list_id){
            $form->column(6, function (Form $form) use($from,$plan_list_info){
                $plan_list_no = getOrderNo('plan_list', '',8,'plan_list_no');
                $form->text('plan_list_no')->default($plan_list_no);
                $form->datetime('delivery_date')
                    ->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now());
                $form->text('client_order_no')->value($plan_list_info->client_order_no);
                $form->text('product_time')->value($plan_list_info->product_time);
                $form->hidden('carft_skill_name')->value($plan_list_info->carft_skill_name);
                $form->hidden('from')->value($from);
                $form->hidden('client_sole_information_id')->value($plan_list_info->client_sole_information_id);
                $form->selectResource('carft_skill_id')
                    ->path('dialog/craft-skill')// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return CarftSkill::findOrFail($v)->pluck('carft_skill_name', 'id');
                    })->required()->value($plan_list_info->carft_skill_id);
                $form->hidden('personnel_id')->value($plan_list_info->personnel_id);
                $form->text('personnel_name')->readonly()->value($plan_list_info->personnel_name) ;
            });
            $form->column(6, function (Form $form) use($plan_list_info){
                $form->hidden('client_name')->value($plan_list_info->client_name);
                $form->select('client_id')->options('api/client')
                    ->load('company_model_id','api/company-model-and-client')
                    ->required()->value($plan_list_info->client_id);
                $form->select('company_model_id')
                    ->required()->value($plan_list_info->company_model_id);
                $form->hidden('company_model')->value($plan_list_info->company_model);
                $form->select('client_model_id')->required()->value($plan_list_info->client_model_id);
                $form->hidden('client_model')->value($plan_list_info->client_model);
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('craft_color_id')->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor_form_copy_plan_list"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor_form_copy_plan_list').on('click',function (){
        layer.open({
          type: 2,
          title: '工艺颜色',
          shadeClose: true,
          shade: false,
          maxmin: true, //开启最大化最小化按钮
          area: ['700px', '600px'],
          content: '/'+envheader+'/craft-color?dialog=1&field=craft_color_id'
        });

    })

})
function change_craft_color_id(){
    var target = $('.field_craft_color_id')
    let getturl = '/'+envheader+'/api/craft-color-by-client-model'
    var client_model_id = $('select[name=client_model_id]').val()

    $.post(getturl,{_token:_token,client_model_id:client_model_id},function(data,ret) {

        data = JSON.parse(data)
       if(data.code==200&&data.data.length>0){
           let target=$('.field_craft_color_id');
           target.find("option").remove();
           target.select2({
                data: data.data,
               //默认空点选
            }).val(target.attr('data-value')).trigger('change');
       }
    })
}
</script>
EOD

                    )->value($plan_list_info->craft_color_id);
                $form->hidden('craft_color_name')->value($plan_list_info->craft_color_name);
                $form->select('product_category_id')->options('/api/product-category')
                    ->value($plan_list_info->product_category_id)->required();
                $form->hidden('product_category_name')->value($plan_list_info->product_category_name);
                $form->select('plan_category_id')->options('/api/plan-category')
                    ->value($plan_list_info->plan_category_id)->required();
                $form->hidden('plan_category_name')->value($plan_list_info->plan_category_name);
                $form->hidden('_token')->value(csrf_token());
            });
            $form->column('12',function (Form $form) use ($specs,$types,$plan_list_id,$plan_list_info){
                $form->html(function () use($specs,$types,$form,$plan_list_id,$plan_list_info){
                    $id = 0;
                    $result =  PlanListDetail::where('plan_list_id',$plan_list_id)->get();
                    // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
                    $sizes = $result->map(function (PlanListDetail $data) {
                        return ['id' => $data->id,
                                'spec' => $data->spec,
                                'type' => $data->type,
                                'num' => $data->num,
                        ];
                    });
                    $is_edit = 1;
                    $specs = json_encode($specs);
                    $types = json_encode($types);
                    $client_sole_info = ClientSoleInformation::find($plan_list_info->client_sole_information_id);
                    $start_code =$client_sole_info->start_code;
                    $end_code= $client_sole_info->end_code;
                    $envheader=getenv('ADMIN_ROUTE_PREFIX');
                    $csrf=csrf_token();
                    return  <<<EHTML
        <style>
        .spec-top{background: #487cd0;color:#fff;padding:10px 25px}
        .spec-title{position: relative;top:2px;padding-right:5px;}
        #spec-table-{$id} tr td,#total tr td{text-align: center}
        #spec-table-{$id} tr td span{
        display: inline-block;
            margin-right:10px;
        }
        .input-h{height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
        select {width:100px}
        .total{}
        #plan_order_num_{$id}{background: #fff}
        </style>
        <div class="spec-top " style="">
            <span class="spec-title">请选择规格/尺码数量</span>
            <select name="" id="plan_order_num_{$id}" class=" col-md-1 select">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
            </select>
        </div>
        <div class="spec-body" id="spec-body">
            <table id="spec-table-{$id}" class="table">

            </table>
        </div>
        <div class="total">
          <table  class="table" id="total">
               <tr>
                     <td width="48%" colspan="2" >
                   <span class="text-danger" id="show_code_field" style="display:none" >* 码数超出范围请确认</span>
                   </td>
                   <td  width="52%">合计数:<span id="total_num">0</span></td>
                </tr>
            </table>
        </div>
        <hr>
         <div  style="width:500px;margin:0 auto;text-align: center">
                <button class="btn btn-primary submit"><i class="feather icon-save"></i> 提交</button>
                <button style="margin-left:20px;" type="reset" class="btn btn-white"><i class="feather icon-rotate-ccw"></i> 取消</button>
            </div>
        <script >
     //获取尺码和规格
        var specs  ={$specs};
        var types  ={$types};
        var sizes  ={$sizes};
        var is_edit  ={$is_edit};
        var _token  ="{$csrf}";
        var typeHtml = '';
        var start_code={$start_code};
        var end_code={$end_code};
        $.each(types,function(index,data) {
          typeHtml+='<option value="'+data.type+'">'+data.text+'</option>'
        })
    $(function() {
        //初始化一行

        var hasSpec = {$sizes};
        var specHtml = '';
        $('#plan_order_num_{$id}').val(hasSpec.length)
        $.each(hasSpec,function(index,data) {
            specHtml+='<tr>' +
           '<td>'+
            '<span>规格/尺码</span>' +
             '<input name="specarr[id][]" type="hidden"  value="'+data.id+'" />' +
              '<select name="specarr[spec][]" class="input-h spec_select" value="'+data.spec+'">'+chooseSpecs(data.spec)+'</select>'+
              '<select name="specarr[type][]"  class="input-h" value="'+data.type+'">'+chooseTypes(data.type)+'</select>'+
            '</td>' +
            '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="'+data.num+'" /></td>' +
       '</tr>';
        })
        $('#spec-table-{$id}').append(specHtml)
         calcunum()
         checkoutrange(start_code,end_code)


         //切换数字，表格变化
         $(document).on('change','#plan_order_num_{$id}',function() {

              var that = this;
              var new_spec_num = $(that).val()
              var new_specs = specs[new_spec_num]
              var appendhtml = '';

              $('#spec-table-{$id}').empty();

              for(i=0;i<new_spec_num;i++){
                let showhtml = ''
                for(j=33;j<=41;j++){
                    let is_selected = new_specs[i]==j?'selected':''
                    showhtml+='<option value="'+j+'" '+is_selected+'>'+j+'</option>';
                }
                appendhtml+=oneline(showhtml)
              }

               $('#spec-table-{$id}').append(appendhtml)
                //检测是否超出范围
                checkoutrange(start_code,end_code)

         })
         //数量变化
          $(document).on('blur','.spec_num',function() {
              calcunum()
          })
          //码数select 变化
          $(document).on('change', '.spec_select', function () {
                checkoutrange(start_code,end_code)
            })

          //客户携带出业务员
            $('.field_client_id').on('change',function(e) {
                var client_id = $('select[name=client_id]').val();
                let posturl = '/'+"{$envheader}"+'/api/client-personnel'
                let token = "{$csrf}"
                $.post(posturl,{client_id:client_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       $(document).find('input[name=personnel_name]').val(res.data.personnel_name)
                      $(document).find('input[name=personnel_id]').val(res.data.personnel_id)
                   }
                })
           })
       //客户+雷力型号定位 工艺单中的客户型号
          $('.field_client_id,.field_company_model_id').on('change',function(e) {
            var client_id = $('select[name=client_id]').val();
            var company_model_id = $('select[name=company_model_id]').val();
            if(client_id>0&&company_model_id>0){

                let posturl = '/'+"{$envheader}"+'/api/craft-information-client-model'
                let token = "{$csrf}"
                $.post(posturl,{client_id:client_id,company_model_id:company_model_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       let target=$('.field_client_model_id');
                        target.find("option").remove();
                       target.select2({
                            data: res.data,
                           //默认空点选
                        }).val(target.attr('data-value')).trigger('change');
                   }
                })
            }
          })
          //客户型号变化，客户颜色跟着变化
          $('.field_client_model_id').on('change',function(e) {
            var client_model_id = $('select[name=client_model_id]').val();
            if(client_model_id>0){
                let posturl = '/'+"{$envheader}"+'/api/craft-color-by-client-model'
                let token = "{$csrf}"
                $.post(posturl,{client_model_id:client_model_id,'_token':token},function(res) {
                   res = JSON.parse(res);
                   if(res.code==200){
                       let target=$('.field_craft_color_id');
                         target.find("option").remove();
                       target.select2({
                            data: res.data,
                           //默认空点选
                        }).val(target.attr('data-value')).trigger('change');
                   }
                })
            }
          })
    //确定客户+雷力型号+客户型号+工艺颜色后定位一条客户鞋底资料
     $('.field_client_id,.field_company_model_id,.field_client_model_id,.field_craft_color_id,.field_product_category_id').on("change", function(e) {
            var field_client_id = $('select[name=client_id]').val()
            var field_company_model_id =$('select[name=company_model_id]').val()
            var field_client_model_id = $('select[name=client_model_id]').val()
            var field_craft_color_id = $('select[name=craft_color_id]').val()
            var field_product_category_id = $('select[name=product_category_id]').val()

            if(field_client_id>0&&field_company_model_id>0&&field_client_model_id>0&&field_craft_color_id>0&&field_product_category_id>0){
               //发送ajax 请求规定的数据
                 let posturl = '/'+"{$envheader}"+'/api/plan-list-load-client-sole';
                $.post(posturl,{
                client_id:field_client_id,
                company_model_id:field_company_model_id,
                client_model_id:field_client_model_id,
                product_category_id:field_product_category_id,
                craft_color_id:field_craft_color_id,
                '_token':_token},function(res) {
                     res = JSON.parse(res);
                     if(res.code==200){
                            $(document).find('input[name=client_sole_information_id]').val(res.data.id)
                            $(document).find('input[name=client_name]').val(res.data.client_name)
                            $(document).find('input[name=company_model]').val(res.data.company_model)


                            $(document).find('input[name=client_model]').val(res.data.client_model)
                            $(document).find('input[name=craft_color_name]:not(.filter_column__craft_color_name)').val(res.data.craft_color_name)
                            $(document).find('input[name=product_category_name]').val(res.data.product_category_name)

                            $(document).find('input[name=knife_mold]').val(res.data.knife_mold)
                            $(document).find('input[name=leather_piece]').val(res.data.leather_piece)
                            $(document).find('input[name=welt]').val(res.data.welt)
                            $(document).find('input[name=inject_mold_ask]').val(res.data.inject_mold_ask)
                            $(document).find('input[name=plan_describe]').val(res.data.remark)
                            $(document).find('input[name=craft_ask]').val(res.data.craft_ask)
                            $(document).find('input[name=out]').val(res.data.out)
                            start_code = res.data.start_code
                            end_code = res.data.end_code
                             checkoutrange(start_code,end_code)
                        }
                    });
                }
            })
        })

    function chooseSpecs(choose=''){
          var specHtml = '';
          var num = $('#plan_order_num_{$id}').val()
          var choose_specs = specs[num]
           for(j=33;j<=41;j++){
                let is_selected = choose==j?'selected':''
                specHtml+='<option value="'+j+'" '+is_selected+'>'+j+'</option>';
            }
         return specHtml;
     }

    function chooseTypes(choose=0){
          var typesHtml = '';
          var num = $('#plan_order_num_{$id}').val()
          var choose_types = types

          $.each(choose_types,function(index,data) {
              var is_selected = choose==data.type?'selected':''
              typesHtml+='<option value="'+data.type+'" '+is_selected+'>'+data.text+'</option>'
         })
         return typesHtml;
     }
    function oneline(guigeHtml){
         var oneline = '<tr>' +
           '<td>'+
            '<span>规格/尺码</span><select name="specarr[spec][]" class="input-h spec_select">'+guigeHtml+'</select>'+
            '&nbsp;&nbsp;&nbsp;&nbsp;<select name="specarr[type][]" class="input-h">'+typeHtml+'</select>'+
            '</td>' +
            '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="0"/></td>' +
       '</tr>';
         return oneline;
    }
   function calcunum() {
        var num=0;
        $.each($('.spec_num'),function() {
            var check_num = isNaN($(this).val())?0:$(this).val()
            num+=parseFloat(check_num);
        })
        $('#total_num').text(num)
   }
  function checkoutrange(start_code,end_code){
    var show_tag =0;
console.log(start_code,end_code)
    $('select.spec_select').each(function(index,data){
        let this_code = $(data).val()
        if(!(this_code>=start_code&&this_code<=end_code)){
            show_tag +=1;
        }
    })
    if(show_tag>0){
          $('#show_code_field').show();
    }else{
      $('#show_code_field').hide();
    }
   }
</script>
EHTML;
                },' ')->oneline('true')->width(10,1);
            });

            $form->column(12, function (Form $form) use($plan_list_info){
                $form->hidden('spec_num')->value($plan_list_info->spec_num);
                $form->text('plan_describe')->width(10,1)->value($plan_list_info->plan_describe);
                $form->text('knife_mold')->width(10,1)->value($plan_list_info->knife_mold);
                $form->text('leather_piece')->width(10,1)->value($plan_list_info->leather_piece);
                $form->text('welt')->width(10,1)->value($plan_list_info->welt);
                $form->text('out')->width(10,1)->value($plan_list_info->out);
                $form->text('inject_mold_ask')->width(10,1)->value($plan_list_info->inject_mold_ask);
                $form->text('craft_ask')->width(10,1)->value($plan_list_info->craft_ask);
                $form->text('plan_remark')->width(10,1)->value($plan_list_info->plan_remark);
                $form->image('image')->width(10,1)->value($plan_list_info->image);
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

            $form->submitted(function (Form $form) {

                $query = PlanList::query()->where('plan_list_no',
                    $form->plan_list_no);
                $no_check = $query->count();
                //检测单号
                if($no_check>0){
                    return $form->error('订单号已存在，请修改');
                }
                //检测规格
                if(isset($form->specarr['num']) && count($form->specarr['num'])){
                    $form->spec_num = array_sum($form->specarr['num']);
                }else{
                    return $form->error('请选择规格');
                }

            });
            $form->saving(function (Form $form){
                $form->deleteInput('_token');
            });
            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });
        });

    }

    /**
     * 计划单的派工详情
     * @param $id
     * @param Content $content
     * @return Content
     */
    public function dispatchDetail($id,Content $content){
        return $content
            ->title('派工详情')
            ->row(function (Row $row) use($id) {
                $row->column(12, $this->dispatchDetailGrid($id));
            });
    }
    /**
     * dec:派工详情列表数据
     * author : happybean
     * date: 2020-04-27
     */
    private function dispatchDetailGrid($id){
        $plan_list = PlanList::find($id);
        $plan_list_num = $plan_list->spec_num;
        return IFrameGrid::make( DispatchDetail::with(['dispatch_info']), function (Grid $grid) use($id,$plan_list,$plan_list_num){
            $grid->model()
                ->where('plan_list_id',$id)
                ->whereHas('dispatch_info',function ($q) use($id){
                    $q->where('plan_list_id',$id);
                })
                ->orderBy('created_at','desc');
            $grid->column('dispatch_no', '派工单号')->display(function (){
                return $this->dispatch_info['dispatch_no'];
            });
            $grid->column('updated_at', '派工时间')->display(function (){
                return $this->updated_at;
            });
            $grid->column('company_model', '雷力型号')->display(function (){
                return $this->dispatch_info['company_model'];
            });
            $grid->column('spec')->display(function (){
                return $this->spec.'码';
            });
            $grid->column('type', '派工类型')->display(function (){
                return config('plan.dispatch_type')[$this->dispatch_info['type']];
            });
            $grid->column('num', '派工数量')->display(function (){
                return $this->num;
            });
            $grid->column('status', __('派工详情'))->display(function (){
                return config('plan.plan_status_html')[$this->dispatch_info['status']];
            });
            $plan_list_no = $plan_list->plan_list_no;
            $grid->header(function ($query) use($plan_list_no,$plan_list_num,$id){
                return '  <div>
                        <label >计划编号:<span class="text-danger">'.$plan_list_no.' </span>
                        </label>&nbsp; &nbsp; <label >   计划数量:<span class="text-info">'.$plan_list_num.' </span>
                        </label>&nbsp; &nbsp;
                    </div>
                    ';
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
//                $filter->customSelect('dispatch_type',[0=>'全部',1=>'鞋底','2'=>'注塑',3=>'箱标'],function ($q){
//                    if($this->chooseTrue!='all'){
//                        $q->whereHas('dispatch_info',function ($qq){
//                            $qq->where('type',$this->chooseTrue);
//                        });
//                    }
//                },'派工类型')->width(2);
                $filter->like('spec','明细规格')->width(2);
            });
            $grid->disableCreateButton();
            $grid->withBorder();
            $grid->disableActions();
            $grid->disableRefreshButton();
            $grid->disableFilterButton();
            $grid->disableRowSelector();
            $grid->paginate(15);
        });
        return $grid;
    }
    /**
     *批量鞋底派工
     */
    public function multiPlanListDispatchPreview(Request $request,Content $content){
        $id = $request->id;
        $no =getPaperOrder('sole_dispatch_paper','',11,'no');
        //鞋底派工
        DB::beginTransaction(); //开启事务
        try{
            $makepaper = new SoleDispatchService();
            $res = $makepaper->multiDispatch($id);
            if($res['status']==='success'){
                $dispatch_ids = $res['ids'];
                $makepaper = new PaperService(DispatchDetail::class,$dispatch_ids,$no);
                $makepaper->makeSoleDispatchPaper();
            }else{
                DB::rollback();
                return [
                    'message' => $res['message'],
                    'status' => 'error',
                ];
            }
            DB::commit();

            $printer = new PrinterService();
            return $printer->rawPlanListDispatchTable($id,$no);
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }

    }

    /**
     * dec:汇总表单预览
     * author : happybean
     * date: 2020-05-09
     */
    public function gatherPage(Content $content){
        $plan_list_model= new PlanList();
        $request_data = request()->all();

        return $content
            ->title('汇总表单')
            ->body(function (Row $row) use($plan_list_model,$request_data) {

                $row->column(6, function (Column $column) use($plan_list_model,$request_data){
                    $time_start =isset($request_data['lstart']) ?date('Y-m-d H:i',strtotime($request_data['lstart'])):Carbon::today()->toDateString();
                    $time_end = isset($request_data['lend'])?date('Y-m-d H:i',strtotime($request_data['lend'])):Carbon::tomorrow()->toDateString();
                    $data = PlanList::where('created_at', '>=',
                        date('Y-m-d',strtotime(date('Y-m').'-1')))
                        ->whereDate('created_at', '<',$time_end)
                        ->get()->groupBy('client_id')->toArray();
                    $showarr = [];

                    foreach($data as $kk=>$vv){
                        $showarr[]=[
                            'client_name'=>$vv[0]['client_name'],
                            'client_id'=>$vv[0]['client_id'],
                            'num'=>$plan_list_model->planListClientNum($vv[0]['client_id'],$time_start,$time_end),
                            'TPU'=>$plan_list_model->planListTpu($vv[0]['client_id'],$time_start,$time_end),
                            'rubber'=>$plan_list_model->planListRubber($vv[0]['client_id'],$time_start,$time_end),
                            'welt'=>$plan_list_model->planListWelt($vv[0]['client_id'],$time_start,$time_end),
                        ];
                    }
                    $all['num'] = $plan_list_model->planListClientNum(0,$time_start,$time_end);
                    $all['tpu'] = $plan_list_model->planListTpu(0,$time_start,$time_end);
                    $all['rubber'] = $plan_list_model->planListRubber(0,$time_start,$time_end);
                    $all['welt'] = $plan_list_model->planListWelt(0,$time_start,$time_end);
                    $all['color'] = $plan_list_model->planListColor(0,$time_start,$time_end);

                    //成品发货信息[本月1号到当天]

                    $month_today = date('Y-m-d',time());
                    $day = Delivery::whereDate('created_at', $month_today)->sum('all_num');

                    $month = Delivery::whereDate('created_at', '>=', date('Y-m-d',
                        strtotime(date('Y-m').'-1')))
                        ->whereDate('created_at', '<=',$month_today)->sum('all_num');
                    $chengpin['day']=is_float_number($day);
                    $chengpin['month']=is_float_number($month);
                    $showarr  =arraySort($showarr,'num');
                    $column->append("<div style='position: relative;top:40px;left:-10px;z-index:99'>
    <a href=\"".admin_url('gather/left/export?lstart='.$time_start.'&lend='.$time_end)."\" target=\"_blank\" class=\"pull-right btn btn-sm btn-info\" title=\"导出excel\">
        导出excel
    </a>
</div>");
                    $column->append(Box::make('当日报表汇总表',view('admin.gather.left',
                        ['data'=>$showarr,'all'=>$all,'time'=>['lstart'=>$time_start,'lend'=>$time_end],'chengpin'=>$chengpin])));
                });
                $row->column(6, function (Column $column) use($plan_list_model,$request_data){
                    $time_start = isset($request_data['rstart'])?date('Y-m-d',strtotime($request_data['rstart'])):
                        Carbon::parse('15 days ago')->toDateString();
                    $time_end = isset($request_data['rend'])?date('Y-m-d',strtotime($request_data['rend'])):
                        Carbon::today()->toDateString();
                    $month_today = date('Y-m-d',time());
                    $data = PlanList::whereDate('created_at','<',$month_today)->get()->groupBy('client_id')->toArray();
                    $showarr = [];
                    foreach($data as $kk=>$vv){
                        $showarr[]=[
                            'client_name'=>$vv[0]['client_name'],
                            'client_id'=>$vv[0]['client_id'],
                            'num'=>$plan_list_model->planListNoCompleteClientNum($vv[0]['client_id'],
                                $time_start,$time_end),
                            'TPU'=>$plan_list_model->planListNoCompleteTpu($vv[0]['client_id'],
                                $time_start,$time_end),
                            'rubber'=>$plan_list_model->planListNoCompleteRubber($vv[0]['client_id'],$time_start,$time_end),
                            'welt'=>$plan_list_model->planListNoCompleteWelt($vv[0]['client_id'],$time_start,$time_end),
                        ];
                    }
                    $all['num'] = $plan_list_model->planListNoCompleteClientNum(0,$time_start,$time_end);
                    $all['tpu'] = $plan_list_model->planListNoCompleteTpu(0,$time_start,$time_end);
                    $all['rubber'] = $plan_list_model->planListNoCompleteRubber(0,$time_start,$time_end);
                    $all['welt'] = $plan_list_model->planListNoCompleteWelt(0,$time_start,$time_end);
                    $all['color'] = $plan_list_model->planListNoCompleteColor(0,$time_start,$time_end);

                    $showarr  =arraySort($showarr,'num');
                    $column->append("<div style='position: relative;top:40px;left:-10px;z-index:99'>
    <a href=\"".admin_url('gather/right/export?rstart='.$time_start.'&rend='.$time_end)."\" target=\"_blank\" class=\"pull-right btn btn-sm btn-info\" title=\"导出excel\">
        导出excel
    </a>
</div>");

                    $column->append(Box::make('未完成厂商汇总表',view('admin.gather.right',
                        ['data'=>$showarr,'all'=>$all,'time'=>['rstart'=>$time_start,'rend'=>$time_end],
                        ])));
                });
            });
    }

    /**
     * dec:汇总左边导出
     * author : happybean
     * date: 2020-05-23
     */
    public function gatherLeftExport(){
        $start = request()->lstart;
        $end = request()->lend;
        return Excel::download(new GatherLeftExcelExpoter($start,$end),
            '当日汇总【'.date('Y-m-d').'】.xlsx');
    }
    /**
     * dec:汇总右边导出
     * author : happybean
     * date: 2020-05-23
     */
    public function gatherRightExport(){
        $start = request()->rstart;
        $end = request()->rend;
        return Excel::download(new GatherRightExcelExpoter($start,$end),
            '未完成厂商汇总表【'.date('Y-m-d').'】.xlsx');
    }

    public function weichukuExport(Request $request){
        $start = $request->start;
        $end = $request->end;
        return Excel::download(new NoStorageOutExport($start,$end),'未出库信息【'.date('Y-m-d').'】.xlsx');
    }

    /**
     * dec: 导出计划单对应的出货票据
     * @param Content $content
     * author : happybean
     * date: 2020-05-02
     */
    public function exportDeliveryPaper(Content $content){
        return $content
            ->title('计划单对应的出货票据')
            ->row(function (Row $row)  {
                $row->column(12, $this->deliveryPaperGrid());
            });
    }
    /**
     * dec:导出计划单对应的出货票据的列表数据
     * author : happybean
     * date: 2020-04-27
     */
    public function deliveryPaperGrid(){

        return Grid::make(DeliveryPaper::with('plan_list'), function (Grid $grid) {

            $grid->model()->orderBy('created_at','desc')
                ->orderBy('plan_list_id','desc');
            $grid->column('plan_list_no','计划单号')->display(function (){
                return $this->plan_list['plan_list_no'];
            });
            $grid->column('no','成品发货票据')->display(function (){
                return $this->no;
            });
            $grid->column('company_model','雷力型号')->display(function (){
                return $this->plan_list['company_model'];
            });
            $grid->column('craft_color_name','工艺颜色')->display(function (){
                return $this->plan_list['craft_color_name'];
            });

            $grid->column('spec_num', __('订单数量'))->display(function ()  {
                return $this->plan_list['spec_num'];
            });
            $grid->column('delivery_num', __('已发数量'))->display(function () {
                //已经发货数量
                return $this->plan_list['delivery_num'];
            });
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->paginate(15);
            //导出
            $titles = [
                'plan_list_no'=>'计划单号',
                'no'=>'成品发货票据',
                'company_model' => '雷力型号',
                'craft_color_name' => '工艺颜色',
                'spec_num' => '订单数量',
                'delivery_num' => '已发数量',
            ];
            $filename = '计划单对应的出货票据'.date('Y-m-d H:i:s');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['plan_list_no'] = $row['plan_list']['plan_list_no'];
                    $row['no'] = $row['no'];
                    $row['company_model'] = $row['plan_list']['company_model'];
                    $row['craft_color_name'] = $row['plan_list']['craft_color_name'];
                    $row['spec_num'] = $row['plan_list']['spec_num'];;
                    $row['delivery_num'] = $row['plan_list']['delivery_num'];;
                }
                return $rows;
            })->xlsx();
        });
    }

}
