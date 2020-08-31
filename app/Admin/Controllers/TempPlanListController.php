<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\TempPlanListMultiCheck;
use App\Models\ClientSoleInformation;
use App\Models\CraftInformation;
use App\Models\TempPlanList;
use App\Models\PlanList;
use App\Models\Client;
use App\Models\CarftSkill;
use App\Models\CompanyModel;
use App\Models\ProductCategory;
use App\Models\ClientModel;
use App\Models\Personnel;
use App\Models\PlanCategory;
use App\Models\TempPlanListDetail;
use App\Models\PlanListDetail;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Carbon\Carbon;
use Dcat\Admin\Controllers\AdminController;

class TempPlanListController extends AdminController
{
    public function __construct(){
        $this->plan_status = config('plan.plan_status_simple_html');
        $this->plan_status_simple = config('plan.plan_status_simple');
        $this->plan_status_arrs = config('plan.plan_status_html');
    }
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make(new TempPlanList(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));

            $grid->column('delivery_date')->display(function ()  {
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->delivery_date.'</span>';
                }else{
                    return '<span  style="font-size:12px;">'.$this->delivery_date.'</span>';
                }
            })->width("80px");
            $grid->temp_plan_list_no->dialog(function (){
                if($this->is_check=='1'){
                    $html = '<span  class="text-gray" style="text-decoration: underline;font-size: 12px">';
                }else{
                    $html = '<span   style="text-decoration: underline;font-size: 12px">';
                }
                return ['type'=>'url',
                        'url'=>admin_url('temp-plan-list/'.$this->id.'?dialog=1'),
                        'width'=>'700px',
                        'height'=>'500px',
                        'value'=>$html.$this->temp_plan_list_no.'</span>'
                ];
            });
            $grid->column('plan_list_no','计划单编号')->display(function (){
                $showinfo = $this->plan_list_no?:'-';
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$showinfo.'</span>';
                }else{
                    return $showinfo;
                }
            });
            $grid->column('client_name')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->client_name.'</span>';
                }else{
                    return $this->client_name;
                }
            });
            $grid->column('client_order_no')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->client_order_no.'</span>';
                }else{
                    return $this->client_order_no;
                }
            });
            $grid->column('product_time')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->product_time.'</span>';
                }else{
                    return $this->product_time;
                }
            });
            $grid->column('company_model')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->company_model.'</span>';
                }else{
                    return $this->company_model;
                }
            });
            $grid->client_model->dialog(function (){
                $img = CraftInformation::where('company_model',$this->company_model)
                    ->where('client_id',$this->client_id)
                    ->where('client_model',$this->client_model)
                    ->first();
                $class=$this->is_check?'text-gray':'';
                if($img){
                    $img = $img->sole_image;
                    return  ['type'=>'img','img'=>$img?$img[0]:'', 'width'=>'600px',
                             'value'=>'<span class="'.$class.'" style="text-decoration: underline">'
                                 .$this->client_model.'</sapn>',
                             'height'=>'870px'];
                }else{
                    return  ['type'=>'text','content'=>'<h5 align=\'center\'>暂无图片</h5>',
                             'value'=>'<span  class="'.$class.'" style="text-decoration: underline">'
                                 .$this->kehu_model.'</sapn>',];
                }
            });
            $grid->column('product_category_name')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->product_category_name.'</span>';
                }else{
                    return $this->product_category_name;
                }
            });
            $grid->column('craft_color_name')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->craft_color_name.'</span>';
                }else{
                    return $this->craft_color_name;
                }
            });
            $grid->column('spec_num')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->spec_num.'</span>';
                }else{
                    return $this->spec_num;
                }
            });
            $grid->is_check->using(config('plan.mold_price_check_text'));
            $grid->column('copy','复制')->display(function (){
                $url = admin_url('temp-plan-list/copy/'.$this->id);
                Form::dialog('复制',$url)
                    ->click('#copy_form_temp_plan_list'.$this->id)
                    ->url($url)
                    ->width(config('plan.dialog.width'))
                    ->height(config('plan.dialog.height'))
                    ->success(
                        <<<JS
                    // 保存成功之后刷新页面
                    Dcat.reload();
JS
                    );
                return "<a class='text-info' id='copy_form_temp_plan_list".$this->id."' >
 <i class=\"feather icon-copy grid-action-icon\"></i></a>";
            });
            $grid->column('oprateion','操作')->display(function (){
                if($this->status==0){
                    $url= admin_url('temp-plan-list/'.$this->id.'/edit');
                    Form::dialog('修改',$url)
                        ->click('#temp_plan_list_edit_form_'.$this->id)
                        ->url($url)
                        ->width('900px')
                        ->height('650px')
                        ->success(
                        <<<JS
                    // 保存成功之后刷新页面
                    Dcat.reload();
JS
                    );
                    return "<a class='text-info' id='temp_plan_list_edit_form_".$this->id."' >
<i class=\"feather icon-edit grid-action-icon\"></i></a>";
                }else{
                    return '-';
                }
            });
            $grid->column('delete_operation','删除')->display(function (){
                if($this->is_check=='0'){
                    return '<a href="javascript:void(0);" data-url="'.admin_url('temp-plan-list/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });
            $plan_list_model = new TempPlanList();
            $grid->column('logger_name','录单员')->display(function (){
                if($this->is_check=='1'){
                    return '<span class="text-gray" style="font-size:12px;">'.$this->logger_name.'</span>';
                }else{
                    return $this->logger_name;
                }
            });
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

            $grid->disableActions();
            $grid->disableBatchDelete();
            $grid->disableDeleteButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->paginate(15);
            $grid->tableWidth('125%');
            $grid->toolsWithOutline(false);
            $grid->tools(
                new TempPlanListMultiCheck('批量验收计划')
            );
            $grid->header(function ($query) {
                $all_num = TempPlanList::sum('spec_num');
                return '
                    <div style="position: absolute;left:260px;top:-23px;">
                        <label >临时计划单合计数量:<span class="text-danger">'.$all_num.'</span>双 </label>

                    </div>
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
                $filter->multiInput('temp_plan_list_no',function ($qq){
                    if($this->input1){
                        $qq->orWhere('temp_plan_list_no','like','%'.$this->input1.'%');
                    }
                    if($this->input2){
                        $qq->orWhere('temp_plan_list_no','like','%'.$this->input2.'%');
                    }
                    if($this->input3){
                        $qq->orWhere('temp_plan_list_no','like','%'.$this->input3.'%');
                    }
                    if($this->input4){
                        $qq->orWhere('temp_plan_list_no','like','%'.$this->input4.'%');
                    }
                })->width(5);
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
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
            //导出
            $titles = [
                'created_at'=>'计划时间',
                'temp_plan_list_no'=>'临时编号',
                'plan_list_no'=>'计划编号',
                'client_name'=>'客户名称',
                'client_order_no' => '客户计划单号',
                'product_time' => '生产周期',
                'company_model' => '雷力型号',
                'client_model' => '客户型号',
                'product_category_name' => '产品类型',
                'craft_color_name' => '工艺颜色',
                'spec_num' => '订单数量',
                'is_check'=>'是否验收',
                'logger_name' => '录单员',
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
                $plan_list_model = new TempPlanList();
                foreach ($rows as $index => &$row) {
                    for ($i=33;$i<=41;$i++){
                        $arr = $plan_list_model->getDetailNum($row['id'],''.$i);
                        if($arr['left']>0||$arr['right']>0){
                            $row[''.$i] =  $arr['left'].'/'.$arr['right'];
                        }else{
                            $row[''.$i] =   $arr['all'];
                        }
                    }
                    $row['is_check'] = config('plan.paper_check')[$row['is_check']];
                }
                return $rows;
            })->xlsx();
        });
    }

    /**
     * 详情
     */
    protected function detail($id)
    {
        $is_dialog = request()->dialog?:0;
        $title = "临时计划单";
        $order = TempPlanList::findOrFail($id);
        $length=6;
        $info=[
            [
                'label'=>'临时编号',
                'value'=>$order->temp_plan_list_no,
                'length'=>$length
            ],
            [
                'label'=>'计划编号',
                'value'=>$order->plan_list_no?:'-',
                'length'=>$length
            ],
            [
                'label'=>'是否验收',
                'value'=>config('plan.paper_check')[$order->is_check],
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
     * 表格
     */
    protected function form()
    {
        $specs = config('plan.spec');
        $types = config('plan.type');
        return Form::make(new TempPlanList(), function (Form $form) use($specs,$types){
            $form->column(6, function (Form $form) {
                $temp_plan_list_no = getOrderNo('temp_plan_list', 'TMP',11,'temp_plan_list_no');
                $form->text('temp_plan_list_no')->default($temp_plan_list_no);
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
                $form->hidden('client_name');
                $form->select('client_id')->options('api/client')
                    ->load('company_model_id','api/company-model-and-client')
                    ->required();
                $form->select('company_model_id')
                    ->required();
                $form->hidden('company_model');
                $form->select('client_model_id')->required();
                $form->hidden('client_model');
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('craft_color_id')->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor_tmp_form"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor_tmp_form').on('click',function (){
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
                        $result =  TempPlanListDetail::where('plan_list_id',$id)->get();
                        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
                        $sizes = $result->map(function (TempPlanListDetail $data) {
                            return ['id' => $data->id,
                                    'spec' => $data->spec,
                                    'type' => $data->type,
                                    'num' => $data->num,
                            ];
                        });
                        $sizes = json_encode($sizes);
                    }else{
                        $id=0;
                        $sizes=json_encode([]);
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
                   <td width="20%">&nbsp;</td>
                   <td  width="28%">&nbsp;</td>
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
                  '<select name="specarr[spec][]" class="input-h" value="'+data.spec+'">'+chooseSpecs(data.spec)+'</select>'+
                  '<select name="specarr[type][]"  class="input-h" value="'+data.type+'">'+chooseTypes(data.type)+'</select>'+
                '</td>' +
                '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="'+data.num+'" /></td>' +
           '</tr>';
            })
            $('#spec-table-{$id}').append(specHtml)
             calcunum()
        }else{
        var id={$id}
            $('#spec-table-{$id}').append(oneline('<option value="'+specs[1][0]+'">'+specs[1][0]+'</option>'))
             calcunum()
        }
         //切换数字，表格变化
         $(document).on('change','#plan_order_num_{$id}',function() {

              var that = this;
              var new_spec_num = $(that).val()
              var new_specs = specs[new_spec_num]
              var appendhtml = '';

              $('#spec-table-{$id}').empty();

              for(i=0;i<new_spec_num;i++){
                  appendhtml+=oneline('<option value="'+new_specs[i]+'">'+new_specs[i]+'</option>')
              }

               $('#spec-table-{$id}').append(appendhtml)
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

          $.each(choose_specs,function(index,data) {
               var is_selected = choose==data?'selected':''
               specHtml+='<option value="'+data+'" '+is_selected+'>'+data+'码</option>'
            })

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
            '<span>规格/尺码</span><select name="specarr[spec][]" class="input-h">'+guigeHtml+'</select>'+
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
        $plan_list = TempPlanList::find($id);
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
            TempPlanListDetail::insert($data);
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
            batchUpdate($data,'temp_plan_list_detail');
        }
        if($form->isCreating()){
            $plan_list->logger_id = Admin::user()->id;
            $plan_list->logger_name = Admin::user()->name;
        }
        $plan_list->save();
    }

    /**
     * 复制首页跳转
     */
    public function copyIndex(){
        return  redirect(admin_url("temp-plan-list"));
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
        $plan_list_info = TempPlanList::find($id);
        $plan_list_id = $id;
        //获取尺码
        $specs = config('plan.spec');
        $types = config('plan.type');
        return Form::make(new PlanList(), function (Form $form) use($specs,$types,$plan_list_info,$from,$plan_list_id){
            $form->column(6, function (Form $form) use($from,$plan_list_info){
                $temp_plan_list_no= getOrderNo('temp_plan_list', 'TMP',11,'temp_plan_list_no');
                $form->text('temp_plan_list_no')->default($temp_plan_list_no)->value($temp_plan_list_no);
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
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor_tmp_formCopy"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor_tmp_formCopy').on('click',function (){
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
            $form->column('12',function (Form $form) use ($specs,$types,$plan_list_id){
                $form->html(function () use($specs,$types,$form,$plan_list_id){
                    $id = 0;
                    $result =  TempPlanListDetail::where('plan_list_id',$plan_list_id)->get();
                    // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
                    $sizes = $result->map(function (TempPlanListDetail $data) {
                        return ['id' => $data->id,
                                'spec' => $data->spec,
                                'type' => $data->type,
                                'num' => $data->num,
                        ];
                    });
                    $is_edit = 1;
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
                   <td width="20%">&nbsp;</td>
                   <td  width="28%">&nbsp;</td>
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
                  '<select name="specarr[spec][]" class="input-h" value="'+data.spec+'">'+chooseSpecs(data.spec)+'</select>'+
                  '<select name="specarr[type][]"  class="input-h" value="'+data.type+'">'+chooseTypes(data.type)+'</select>'+
                '</td>' +
                '<td><span>订单数</span><input name="specarr[num][]" type="text" class="input-h spec_num" value="'+data.num+'" /></td>' +
           '</tr>';
            })
            $('#spec-table-{$id}').append(specHtml)
             calcunum()
        }else{
        var id={$id}
            $('#spec-table-{$id}').append(oneline('<option value="'+specs[1][0]+'">'+specs[1][0]+'</option>'))
             calcunum()
        }
         //切换数字，表格变化
         $(document).on('change','#plan_order_num_{$id}',function() {

              var that = this;
              var new_spec_num = $(that).val()
              var new_specs = specs[new_spec_num]
              var appendhtml = '';

              $('#spec-table-{$id}').empty();

              for(i=0;i<new_spec_num;i++){
                  appendhtml+=oneline('<option value="'+new_specs[i]+'">'+new_specs[i]+'</option>')
              }

               $('#spec-table-{$id}').append(appendhtml)
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

                        }
                    });
                }
            })
        })

    function chooseSpecs(choose=''){
          var specHtml = '';
          var num = $('#plan_order_num_{$id}').val()
          var choose_specs = specs[num]

          $.each(choose_specs,function(index,data) {
               var is_selected = choose==data?'selected':''
               specHtml+='<option value="'+data+'" '+is_selected+'>'+data+'码</option>'
            })

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
            '<span>规格/尺码</span><select name="specarr[spec][]" class="input-h">'+guigeHtml+'</select>'+
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

                $query = TempPlanList::query()->where('temp_plan_list_no',
                    $form->temp_plan_list_no);
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
}
