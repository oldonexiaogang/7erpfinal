<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\DeliveryVoid;
use App\Models\ClientModel;
use App\Models\ClientSoleInformation;
use App\Models\CraftInformation;
use App\Models\Delivery;
use App\Models\DeliveryDetail;
use App\Models\DeliveryPrice;
use App\Models\Department;
use App\Models\Dispatch;
use App\Models\Dispatch as DispatchModel;
use App\Models\DispatchDetail;
use App\Models\Personnel;
use App\Models\PlanList;
use App\Models\PlanListDetail;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Admin;
use Dcat\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class DeliveryController extends AdminController
{
    public function planIndex($id=0){
        $content = new Content();
        return $content
            ->title('成品发货记录')
            ->row(function (Row $row) use ($id){
                $row->column(12, $this->planIndexGrid($id));
            });
    }
    protected function planIndexGrid($id=0)
    {
        return IFrameGrid::make(new Delivery(), function (Grid $grid) use($id) {
            $model = new Delivery();
            if($id){
                $grid->model()->where('plan_list_id',$id)
                    ->orderBy('created_at','desc');
            }else{
                $grid->model()
                    ->orderBy('created_at','desc');
            }
            $grid->created_at;
            $grid->column('plan_list_no')->display(function (){
                return $this->plan_list_no;
            });
//            $grid->column('client_name')->display(function (){
//                return $this->client_name;
//            });
//            $grid->column('craft_color_name')->display(function (){
//                return $this->craft_color_name;
//            });
//            $grid->column('company_model')->display(function (){
//                return $this->company_model;
//            });
            $grid->column('code_33', '33')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('all_num','发货数')->display(function (){
                return is_float_number($this->all_num);
            });
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
        });
    }
    public function deliveryIndex(Content $content){
        return $content
            ->title('成品发货记录')
            ->row(function (Row $row) {
                $row->column(12, $this->deliveryIndexGrid());
            });
    }
    protected function deliveryIndexGrid()
    {
        return Grid::make(new Delivery(), function (Grid $grid) {
            $model = new Delivery();
            $grid->model()->orderBy('created_at','desc');
            $grid->column('created_at','发货时间')->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->created_at.'</span>';
                }
                return $this->created_at;
            });
            $grid->plan_list_no->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->plan_list_no.'</span>';
                }
                return $this->plan_list_no;
            });
            $grid->client_name->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->client_name.'</span>';
                }
                return $this->client_name;
            });
            $grid->company_model->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->company_model.'</span>';
                }
                return $this->company_model;
            });
            $grid->client_model->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->client_model.'</span>';
                }
                return $this->client_model;
            });
            $grid->craft_color_name->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.$this->craft_color_name.'</span>';
                }
                return $this->craft_color_name;
            });
            $grid->column('code_33', '33')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('all_num','发货数量')->display(function (){
                if($this->is_print=='1'&&$this->status=='1'){
                    return '<span class="text-success">'.is_float_number($this->all_num).'</span>';
                }
                return is_float_number($this->all_num);
            });
//            $grid->column('status','状态')->display(function (){
//                if($this->is_print=='1'&&$this->status=='1'){
//                    return '<span class="text-success">正常</span>';
//                }
//                if($this->status=='0'){
//                    return '<span class="text-danger">作废</span>';
//                }
//                return $this->status=='1'?'正常':'作废';
//            });
            $grid->column('void','状态【去作废】')->action(DeliveryVoid::class);
            $grid->column('operation','查看')
                ->dialog(function (){
                    return ['type'=>'url','url'=> admin_url('delivery/'.$this->id.'?dialog=1'),
                            'value'=>'<i class=" text-info feather icon-search grid-action-icon"></i>', 'width'=>'600px',
                            'height'=>'600px'];
                });
            $grid->header(function ($query) {
                return '
                         <a href="' . admin_url('chengpin-fahuo-log') . '" class="btn btn-sm btn-info" title="导出Excel">
                                   <span class="hidden-xs">&nbsp;&nbsp;导出Excel&nbsp;&nbsp;</span>
                          </a>
                        ';
            });
//            $grid->batchActions(
//                new DeliveryPrint('发货单打印'));
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableBatchDelete();
            $grid->disableRefreshButton();
            $grid->disableFilterButton();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->multiInput('plan_list_no',function ($qq){
                    if($this->input1){
                            $qq->orWhereHas('plan_list_no','like','%'.$this->input1.'%');

                    }
                    if($this->input2){
                        $qq->orWhereHas('plan_list_no','like','%'.$this->input2.'%');
                    }
                    if($this->input3){
                        $qq->orWhereHas('plan_list_no','like','%'.$this->input3.'%');
                    }
                    if($this->input4){
                        $qq->orWhereHas('plan_list_no','like','%'.$this->input4.'%');
                    }
                    if($this->input5){
                        $qq->orWhereHas('plan_list_no','like','%'.$this->input5.'%');
                    }
                    if($this->input6){
                        $qq->orWhereHas('plan_list_no','like','%'.$this->input6.'%');
                    }
                })->width(5);
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
                    })->width(3);

                $filter->where('client_model',function ($qq){
                    $qq->where('client_model','like', "%{$this->input}%");
                })->width(2);
                $filter->where('company_model',function ($qq){
                    $qq->where('company_model','like', "%{$this->input}%");

                })->width(2);
                $filter->between('created_at')->date()->width(3);
                $filter->where('craft_color_name',function ($qq){
                    $qq->where('craft_color_name','like', "%{$this->input}%");

                })->width(2);
                $filter->equal('is_print')->select(config('plan.print_status'))->width(2);

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
        return Grid::make(new PlanList(), function (Grid $grid) {
            $plan_list_model = new  PlanList();
            $dispatch_model = new  Dispatch();
            $grid->model()->orderBy('created_at','desc');
            $grid->column('created_at')->display(function ()  {
                return $this->created_at;
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
                    return  ['type'=>'img','img'=>$img[0], 'width'=>'600px',
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
                            <td colspan= \'2\'>'.$this->plan_list_no.'</td>
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
                        <td>数量</td>'.$data_html.'
                        </tr>
                    </table>
                ','value'=>'<span style="text-decoration: underline">'.$ordernum.'</span>'];

            });
            $grid->column('delivery_num', '已发货')->dialog(function (){
                $num = is_float_number($this->delivery_num);
                return ['type'=>'url','url'=> admin_url('delivery-log-by-plan/' . $this->id .'?dialog=1'),
                        'value'=>'<span style="text-decoration: underline">'.($num!=0?$num:'0').'</span>',
                        'width'=>'950px',
                        'height'=>'600px'];
            });
            $grid->column('wait_delivery_num', '未发货')->display(function (){
                $num = $this->delivery_num;
                $all_num = $this->spec_num;
                return is_float_number($all_num-$num);
            });
            $grid->column('delivery_operation','订制发货')
                ->if(function () use($dispatch_model){
                    $num = DeliveryDetail::where('plan_list_id', $this->id)->sum('num');
                    $all_num = $this->spec_num;
                    $wait_num =  is_float_number($all_num-$num);
                    $is_has_xiedi = $dispatch_model->hasSoleDispatch($this->id);
                    return ($is_has_xiedi&&$wait_num>0)?true:false;
                })
                ->display(function (){
                    Form::dialog('成品发货')
                        ->click('#delivery_in_'.$this->id) // 绑定点击按钮
                        ->url(admin_url('delivery/create?id='.$this->id.'?dialog=1')) // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                        ->width('900px') // 指定弹窗宽度，可填写百分比，默认 720px
                        ->height('650px') // 指定弹窗高度，可填写百分比，默认 690px
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    $id= $this->id;
                    return '<span class="text-info" style="text-decoration: underline" id="delivery_in_'.$id.'">发货</span>';
                })
                ->else()
                ->display(function (){
                    return "<span  > -</span>";
                });
            $grid->column('code_33', '33')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getNoDelivery($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('process')->display(function (){
                return config('plan.plan_process')[$this->process];
            });
            $grid->column('sole_status', __('鞋底派工'))->display(function ()  {
                return config('plan.plan_status_html')[$this->sole_status];
            });

            $grid->column('box_label_status', __('箱标派工'))->display(function (){
                return config('plan.plan_status_html')[$this->box_label_status];

            });
            $grid->column('inject_mold_status', __('注塑派工'))->display(function ()  {
                return config('plan.plan_status_html')[$this->inject_mold_status];
            });
            $grid->column('storage_out_status')->display(function () {
                return config('plan.plan_status_html')[$this->storage_out_status];
            });
            $grid->header(function ($query) {
                $all_num = PlanListDetail::sum('num');
                $fa_num = Delivery::sum('all_num');
                return '
                        <a href="' . admin_url('plan-list/create') . '" class="btn btn-sm btn-info" title="新增计划单">
                           <span class="hidden-xs">&nbsp;&nbsp;批量发货&nbsp;&nbsp;</span>
                        </a>

                        <label >计划单发货情况  </label>&nbsp; &nbsp; <label >   订单数量:<span class="text-danger">'.$all_num.'</span>双 </label>&nbsp; &nbsp;  <label >已发数量:<span class="text-danger">'.$fa_num.'</span>双</label> &nbsp; &nbsp; <label >未发数量:<span class="text-danger">'.($all_num-$fa_num).'</span>双</label>

                    ';
            });
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableActions();
            $grid->withBorder();
            $grid->tableWidth('105%');
            $grid->paginate(15);

            //搜索框
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
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
                $filter->equal('personnel_id','业务员')->select('api/personnel')->width(2);
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
                $filter->equal('plan_category_id','计划类型')->select('/api/plan-category')->width(2);
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
                $filter->equal('product_category_id','产品类型')->select('api/product-category')->width(2);
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    //计划单详情
    protected function detail($id)
    {
        $is_dialog = request()->dialog;
        $title = "发货数据查询";
        $log = Delivery::findOrFail($id);
        $length=6;
        $info=[
            [
                'label'=>'计划单编号',
                'value'=>$log->plan_list_no,
                'length'=>$length
            ],
            [
                'label'=>'记录人',
                'value'=>$log->log_user_name,
                'length'=>$length
            ],
            [
                'label'=>'鞋跟型号',
                'value'=>$log->company_model,
                'length'=>$length
            ],
            [
                'label'=>'工艺颜色',
                'value'=>$log->craft_color_name,
                'length'=>$length
            ],
            [
                'label'=>'发货数量',
                'value'=>$log->all_num,
                'length'=>$length
            ],
            [
                'label'=>'收获客户',
                'value'=>$log->client_name,
                'length'=>$length
            ],
            [
                'label'=>'单价',
                'value'=>$log->delivery_price,
                'length'=>$length
            ],
            [
                'label'=>'送货员',
                'value'=>$log->delivery_user_name,
                'length'=>$length
            ],
            [
                'label'=>'总金额',
                'value'=>$log->delivery_price*$log->all_num,
                'length'=>$length
            ],
            [
                'label'=>'出库日期',
                'value'=>$log->delivery_at,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$log->content,
                'length'=>$length
            ],

        ];
        $reback = admin_url('chengpin-fahuo-log');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }


    public function createH(Request $request, Content $content)
    {
        $id=$request->id;
        $is_print = $request->print?$request->print:'';
        return $content
            ->title('成品发货')
            ->body($this->deliveryFormH($id,$is_print));
    }
    protected function deliveryFormH($id=0,$is_print){
        $plan_list_info = PlanList::find($id);
        $plan_num = PlanListDetail::where('plan_list_id',$id)->sum('num');
        $delivery_num = DeliveryDetail::where('plan_list_id',$id)->sum('num');
        $department = Department::where('department_name','like','%送货%')->first();
        $delivery_user= Personnel::where('department_id',$department->id)->first();


        $usedata=[
            'plan_list_id'=>$plan_list_info->id,
            'plan_list_no'=>$plan_list_info->plan_list_no,
            'client_name'=>$plan_list_info->client_name,
            'client_id'=>$plan_list_info->client_id,
            'client_model'=>$plan_list_info->client_model,
            'client_model_id'=>$plan_list_info->client_model_id,
            'company_model'=>$plan_list_info->company_model,
            'company_model_id'=>$plan_list_info->company_model_id,
            'craft_color_name'=>$plan_list_info->craft_color_name,
            'craft_color_id'=>$plan_list_info->craft_color_id,
            'delivery_price_id'=>$plan_list_info->delivery_price_id,
            'delivery_price'=>$plan_list_info->delivery_price,
            'delivery_at'=>Carbon::now(),
            'log_user_id'=>Admin::user()->id,
            'log_user_name'=>Admin::user()->name,
            'all_num'=>0,
            'content'=>'',
            'delivery_user_id'=>$delivery_user?$delivery_user->id:0,
            'delivery_user_name'=>$delivery_user?$delivery_user->name:'',
        ];
        return Form::make(new Delivery(), function (Form $form) use($plan_list_info,$id,$plan_num,$delivery_num,
            $usedata,$is_print) {
            $form->column(6, function (Form $form)use($plan_list_info){
                $form->text('plan_list_no')->default($plan_list_info->plan_list_no);
                $form->hidden('plan_list_id')->default($plan_list_info->id);
                $form->text('client_model')->default($plan_list_info->client_model);
                $form->hidden('client_model_id')->default($plan_list_info->client_model_id);
                $form->text('craft_color_name')->default($plan_list_info->craft_color_name);
                $form->hidden('craft_color_id')->default($plan_list_info->craft_color_id);
            });
            $form->column(6, function (Form $form)use($plan_list_info){
                $form->text('client_name')->default($plan_list_info->client_name);
                $form->hidden('client_id')->default($plan_list_info->client_id);
                $form->text('company_model')->default($plan_list_info->company_model);
                $form->hidden('company_model_id')->default($plan_list_info->company_model_id);
                $form->selectResource('delivery_price_id')
                    ->path('dialog/delivery-price?client_id='.$plan_list_info->client_id.
                        '&company_model='.$plan_list_info->company_model.
                        '&craft_color_id='.$plan_list_info->craft_color_id.
                        '&client_model='.$plan_list_info->client_model.'&dialog=1'
                    )// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return DeliveryPrice::findOrFail($v)->pluck('price', 'id');
                    })->required();
                $form->hidden('delivery_price');
            });

                $form->column(12, function (Form $form)  use($plan_list_info,$usedata,$is_print){
                    $planListDetailInfo = PlanListDetail::where('plan_list_id',$plan_list_info->id)->get([
                        'id','spec','type','num','delivery_num'
                    ])->toArray();
                    $form->html(function () use($planListDetailInfo,$plan_list_info){
                        $count = count($planListDetailInfo);
                        $arr=[];
                        $specarr = [];

                        foreach ($planListDetailInfo as $kk=>$vv){
                            $specarr[$kk]['id']=$vv['id'];
                            $specarr[$kk]['spec']=$vv['spec'];
                            if($vv['num']-$vv['delivery_num']>0){
                                $arr[$vv['id']]['spec'] = $vv['spec'];
                                $arr[$vv['id']]['num'] = $vv['num']-$vv['delivery_num'];
                                $arr[$vv['id']]['allnum'] = $vv['num'];
                                $arr[$vv['id']]['type'] = $vv['type'];
                                $arr[$vv['id']]['id'] = $vv['id'];
                            }
                        }
                        $showid = $plan_list_info->id;
                        $dataarr = json_encode($arr);
                        $specarr = json_encode($specarr);
                        $plan_order_spec = json_encode(config('plan.type_text'));
                        return  <<<EHTML
<style>
.spec-top{background: #487cd0;color:#fff;padding:10px 25px}
.spec-title{position: relative;top:2px;padding-right:5px;}
#spec_table_{$showid}_delivery tr td,#total tr td{text-align: left}
#spec_table_{$showid}_delivery tr td span{
display: inline-block;
    margin-right:10px;
}
.input-h1{text-align:center;width:200px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
.input-h2{text-align:center;width:80px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
select {width:100px}
.total{}
#plan_order_num{background: #fff;border-radius: 3px;border:1px solid #d9d9d9;outline:none;}
</style>
<div class="spec-top " style="">
    <span class="spec-title">请选择规格/尺码数量</span>
    <input name="" id="plan_order_num" class=" col-md-1 select" value="{$count}" readonly/>
</div>
<div class="spec-body" id="spec-body">
    <table id="spec_table_{$showid}_delivery" class="table">

    </table>
</div>
<hr>
<script >
var specarr = {$specarr};
  var plan_order_spec = {$plan_order_spec};
  var arrhtml = '';
  var dataarr = {$dataarr};

   $.each(dataarr,function(index,data) {
       var optionshtml = '';
      $.each(specarr,function(index,data2) {
         optionshtml+='<option value="'+data2.spec+'" data-id="'+data2.id+'" '+(data2.spec == data.spec?'selected':'')+'>'+data2.spec+'码 </option>'
     })
     arrhtml+=' <tr>'+
      '<td><div><span>型号规格</span>' +
      '<select name="spec['+data.id+'][spec]">' +optionshtml+
        '</select>'+
        '&nbsp;&nbsp;' +
         '<select name="spec['+data.id+'][type]"><option value="'+data.type+'">'+plan_order_spec[data.type]+'</option><select/>' +
         '<input value="'+data.id+'" name="spec['+data.id+'][id]" type="hidden"></div>' +
       '<div class="text-danger" style="line-height:30px">型号规格【'+data.spec+'】  订单数：'+data.allnum+'（'+plan_order_spec[data.type]+'）  未派数量：'+data.num+'（'+plan_order_spec[data.type]+'）</div></td>'+
       '<td><span>派工数量:</span><input name="spec['+data.id+'][num]" value="'+data.num+'"  class="input-h1"></td>'+
        '</tr>';
  })
$(function() {
   $("#spec_table_{$showid}_delivery").append(arrhtml)
})
</script>
EHTML;
                    },' ')->width(11,1);
                });



            $form->column(6, function (Form $form)use($usedata){
                $form->datetime('delivery_at','发货时间')
                    ->default($usedata['delivery_at'])->format('YYYY-MM-DD HH:mm:ss');
                $form->selectResource('delivery_user_id','送货员')
                    ->path('dialog/personnel')// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Personnel::findOrFail($v)->pluck('name', 'id');
                    })->value($usedata['delivery_user_id'])->required();
                $form->hidden('delivery_user_name')->default($usedata['delivery_user_name']);
                $form->hidden('_token')->default(csrf_token());
            });
            $form->column(6, function (Form $form)use($usedata){
                $form->text('delivery_type','发货类型')
                    ->default(config('plan.delivery_type')['delivery'])->readonly();
            });
            $form->column(12, function (Form $form)use($usedata){
                $form->textarea('content','备注')->width(10,1);
            });
            $form->submitted(function (Form $form) {
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
     * dec:成品发货发货保存
     * author : happybean
     * date: 2020-05-22
     */
    public function deliverySave(Request $request){
        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            $no =getPaperOrder('delivery_paper','',11);
            $url = urlencode(admin_url('delivery/print?id='.$res['ids'].'&no='.$no)) ;
            return $form->redirect(
                admin_url('delivery/create?id='.$request->plan_list_id.'&print='.$url),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }

    protected function saveH (Request $request, $id = null)
    {

        $data = $request->all();

        DB::beginTransaction(); //开启事务
        //添加
        $plan_list_model = new PlanList();
        $plan_list_detailmodel = new PlanListDetail();
        $delivery_model = new Delivery();
        $delivery_detail_model = new DeliveryDetail();

        $plan_list_info = $plan_list_model->find($data['plan_list_id']);


        if(!($data['delivery_price_id']>0)){
            return [
                'message' => '请选择出库单价',
                'status' => 'error',
            ];
        }
        if(!($data['delivery_user_id']>0)){
            return [
                'message' => '请选择送货员',
                'status' => 'error',
            ];
        }

        try{
            $data['delivery_price'] = DeliveryPrice::find($data['delivery_price_id'])['price'];
            $data['delivery_user_name']= Personnel::find($data['delivery_user_id'])->name;
            $delivery_data = [
                'plan_list_id'=>$data['plan_list_id'],
                'plan_list_no'=>$data['plan_list_no'],
                'delivery_no'=>getOrderNo('delivery','',8,'delivery_no'),
                'client_order_no'=>$plan_list_info->client_order_no,
                'client_id'=>$plan_list_info->client_id,
                'client_name'=>$plan_list_info->client_name,
                'client_model'=>$plan_list_info->client_model,
                'client_model_id'=>$plan_list_info->client_model_id,
                'log_user_name'=>Admin::user()->name,
                'log_user_id'=>Admin::user()->id,
                'company_model'=>$plan_list_info->company_model,
                'company_model_id'=>$plan_list_info->company_model_id,
                'craft_color_name'=>$plan_list_info->craft_color_name,
                'craft_color_id'=>$plan_list_info->craft_color_id,
                'delivery_type'=>'delivery',

                'delivery_user_name'=>$data['delivery_user_name'],
                'delivery_user_id'=>$data['delivery_user_id'],
                'delivery_at'=>$data['delivery_at'],
                'content'=> $data['content'],
                'delivery_price_id'=> $data['delivery_price_id'],
                'delivery_price'=>  $data['delivery_price'] ,
                'status'=> '1',
            ];

            $delivery_info = Delivery::create($delivery_data);

            $allnum= 0;
            $now = Carbon::now();
            //dd($data);
            foreach ($data['spec'] as $k=>$v){
                if(!($v['num']>0)){
                    continue;
                }
                $planListDetail = PlanListDetail::where('id',$v['id'])->first();
                $detail_status='1';
                if(($planListDetail->num-$planListDetail->delivery_num)<$v['num']){
                    DB::rollback();
                    return [
                        'message' => '发货数量错误，刷新重试',
                        'status' => 'error',
                    ];
                }
                $allnum+=$v['num'];
                $insertData = [
                    'delivery_id'=>$delivery_info->id,
                    'plan_list_id'=>$data['plan_list_id'],
                    'plan_list_detail_id'=>$v['id'],
                    'spec'=>$v['spec'],
                    'type'=>$v['type'],
                    'num'=>$v['num'],
                    'status'=>$detail_status,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
                $this->changePlanListDetail([
                    'plan_list_detail_id'=>$v['id'],
                    'num'=>$v['num'],
                ]);
                //鞋底派工详情数据
                $ids[] = DeliveryDetail::insertGetId($insertData);
            }
            //鞋底派工数量、
            $status='1';
            $delivery_info->all_num  = $allnum;
            $delivery_of_num = PlanListDetail::where('plan_list_id',$data['plan_list_id'])
                ->sum('delivery_num');
            if($plan_list_info->spec_num ==$delivery_of_num){
                Delivery::where('plan_list_id',$data['plan_list_id'])->update([
                    'status'=>'2'
                ]);
                $status='2';
            }
            $delivery_info->save();
            //全部发货为 改变状态

            //修改计划单状态
            $plan_list_info->status = '5';
            $plan_list_info->delivery_num +=$allnum;
            $plan_list_info->process = 'delivery';
            $plan_list_info->delivery_status =  $status;
            $plan_list_info->save();

            $backid = $plan_list_model->id;
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'ids'=>implode(',',$ids),
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e,
                'status' => 'error',
            ];
        }
    }
    /**
     * 鞋底派工修改
     * @param $arr
     * @return string[]
     */
    private function changePlanListDetail($arr){
        $plan_list_detail = PlanListDetail::find($arr['plan_list_detail_id']);
        $checknum = $plan_list_detail->num-$plan_list_detail->delivery_num;
        if($checknum<$arr['num']){
            return [
                'status'=>'error',
                'message'=>'数量不足'
            ];
        }elseif($checknum==$arr['num']){
            $plan_list_detail->delivery_num += $arr['num'];
            $plan_list_detail->delivery_complete=1;

        }else{
            $plan_list_detail->delivery_num += $arr['num'];
        }
        $plan_list_detail->save();
        return [
            'status'=>'success'
        ];
    }

    /**
     * 发货统计
     */
    public function deliveryCount(Content $content){
        return $content
            ->title('成品发货统计')
            ->row(function (Row $row) {
                $row->column(12, $this->deliveryCountGrid());
            });
    }
    /**
     * dec:发货打印预览表格
     * author : happybean
     * date: 2020-04-22
     */
    protected function deliveryCountGrid()
    {
        return Grid::make(new  Delivery(), function (Grid $grid)  {
            $model = new Delivery();
            $grid->model()->with(['plan_list_info'])->orderBy('created_at','desc');
            $grid->column('created_at','发货时间');
            $grid->column('plan_list_no');
            $grid->column('client_name');
            $grid->column('client_model');
            $grid->column('company_model');
            $grid->column('craft_color_name');
            $grid->column('plan_list_num','计划单数量')->display(function (){
                return is_float_number($this->plan_list_info['spec_num']);
            });
            $grid->column('all_num','发货数量')->display(function (){
                return is_float_number($this->all_num);
            });
            $grid->column('code_33', '33')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function ()  use($model){
                $arr = $model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $all = DeliveryDetail::sum('num');
            $grid->header(function ($query) use($all) {
                return '<div>
                            <label>鞋底发货数量汇总:<span class="text-danger">'.$all.'</span></label>
                        </div>';
            });
            $grid->withBorder();
            $grid->disableActions();
            $grid->disableFilterButton();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->where('plan_list_no',function ($qq){
                    $qq->where('plan_no','like', "%{$this->input}%");;
                })->width(2);
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return ClientModel::findOrFail($v)->pluck('name', 'id');
                    })->width(3);
                $filter->where('company_model',function ($qq){
                    $qq->where('company_model','like', "%{$this->input}%");;
                })->width(2);
                $filter->between('created_at')->date()->width(3);
                $filter->where('craft_color_name',function ($qq){
                    $qq->where('craft_color_name','like', "%{$this->input}%");;
                })->width(2);
            });

        });
    }

}
