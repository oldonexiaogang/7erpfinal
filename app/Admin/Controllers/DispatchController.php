<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\SoleDispatchVoid;
use App\Admin\Extensions\Grid\RowAction\BoxLabelDispatchVoid;
use App\Admin\Extensions\Tools\PlanListDispatchMultiPrint;
use App\Models\BoxLabelDispatchPaper;
use App\Models\CarftSkill;
use App\Models\ClientModel;
use App\Models\ClientSoleInformation;
use App\Models\CompanyModel;
use App\Models\CraftInformation;
use App\Models\Dispatch;
use App\Models\InjectMoldDispatchPaper;
use App\Models\Personnel;
use App\Models\PlanCategory;
use App\Models\PlanList;
use App\Models\Client;
use App\Models\Dispatch as DispatchModel;
use App\Models\DispatchDetail;
use App\Models\PlanListDetail;
use App\Models\ProductCategory;
use App\Models\SoleDispatchPaper;
use App\Models\SoleMaterial;
use App\Models\StandardDetail;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class DispatchController extends AdminController
{
    protected $status;
    protected $dispatch_status;

    public function __construct()
    {
        $this->status = config('plan.status');
        $this->dispatch_status = config('plan.dsipatch_status');
    }

    /**
     * 点击定制派工弹出的小页面
     */
    public function planListDetail($id,Content $content){
        return $content
            ->title('生产计划单详细数据')
            ->row(function (Row $row) use($id) {
                $row->column(12, $this->planListDetailGrid($id));
            });
    }
    /**
     * 点击定制派工弹出的小页面-详情
     * @param $id
     * @return IFrameGrid
     */
    private function planListDetailGrid($id){
        $plan_list = PlanList::find($id);
        return IFrameGrid::make( PlanListDetail::with(['plan_list']), function (Grid $grid) use($id,$plan_list){
            $dispatch_model = new DispatchModel();
            $grid->model()->where('plan_list_id',$id)->orderBy('created_at','desc');
            $grid->column('spec', '尺码')->display(function (){
                return $this->spec.'(码)'.config('plan.type_text')[$this->type];
            });
            $grid->column('delivery_date', '交货时间')->display(function (){
                return date('Y年m月d日',strtotime($this->plan_list['delivery_date']));
            });
            $grid->column('num', '计划数量')->display(function (){
                return is_float_number($this->num);
            });
            $grid->column('delivery_num', '已发')->display(function () use ($id){
                return is_float_number($this->delivery_num);
            });
            $grid->column('xiegen_material', '鞋跟用料')->display(function (){
                return $this->plan_list['craft_color_name'];
            });
            $grid->column('jihua_jindu', __('计划进度'))->display(function (){
                //又一个派工就是处理中
                if($this->sole_dispatch_num>0||$this->inject_mold_dispatch_num>0||$this->box_label_dispatch_num>0){
                    return '处理中';
                }elseif($this->sole_dispatch_num==0&&$this->inject_mold_dispatch_num==0&&$this->box_label_dispatch_num==0){
                    return '未处理';
                }elseif($this->sole_dispatch_num==$this->num&&$this->inject_mold_dispatch_num==$this->num&&$this->box_label_dispatch_num==$this->num){
                    return '已完成';
                }
            });
            $grid->column('dispatch_info', __('派工详情'))->display(function () use($id){
                //又一个派工就是处理中
                $plan_list_dispacth_num_sole = DispatchDetail::where('plan_list_id',$id)
                    ->where('type','sole')
                    ->where('status','1')
                    ->sum('num');
                $plan_list_dispacth_num_inject_mold = DispatchDetail::where('plan_list_id',$id)
                    ->where('type','inject_mold')
                    ->where('status','1')
                    ->sum('num');
                $plan_list_dispacth_num_box_label = DispatchDetail::where('plan_list_id',$id)
                    ->where('type','box_label')
                    ->where('status','1')
                    ->sum('num');
                $return_text = '';
                if($plan_list_dispacth_num_sole>0){
                    $return_text.= '<label>鞋底加工:'.$plan_list_dispacth_num_sole.'</label><br>';
                }
                if($plan_list_dispacth_num_inject_mold>0){
                    $return_text.= '<label>箱标加工:'.$plan_list_dispacth_num_inject_mold.'</label><br>';
                }
                if($plan_list_dispacth_num_box_label>0){
                    $return_text.= '<label>注塑加工</label><br>';
                }
                return $return_text;
            });
            $grid->column('box_label','注塑派工')->display(function () use($id,$dispatch_model){
                $is_has_sole_dispatch = $dispatch_model->hasSoleDispatch($id);
                $showid = $this->id;
                return ' <a href="' . admin_url('zhusu-paigong/create/'.$this->plan_list_id.'?dialog=1') . '" class="btn btn-sm btn-info" title="鞋底派工">
                           <span class="hidden-xs">&nbsp;&nbsp;派工&nbsp;&nbsp;</span>
                        </a>

                        ';
            });
            $plan_list_no = $plan_list->plan_list_no;
            $plan_list_id = $plan_list->id;
            $client_name = $plan_list->client_name;
            $company_model = $plan_list->company_model;
            $grid->header(function ($query) use($plan_list_no,$company_model,$id,$plan_list_id,$client_name){
                $width = config('plan.dialog.width');
                $height = config('plan.dialog.height');
                return '  <div><div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                        <a id="'.$plan_list_id.'_to_sole_dispatch" href="javascript:void(0)" data-url="' . admin_url('sole-dispatch/create/'.$plan_list_id.'?dialog=1') . '" class="btn btn-sm btn-info" title="鞋底派工">
                           <span class="hidden-xs">&nbsp;&nbsp;鞋底派工&nbsp;&nbsp;</span>
                        </a>
                    </div>
                    </div>
                    <br>
                    <br>
                    <div>
                        <label >计划编号:'.$plan_list_no.'  </label>&nbsp; &nbsp; <label >   鞋跟型号:'.$company_model.' </label>&nbsp; &nbsp;  <label >客户:'.$client_name.'</label> &nbsp; &nbsp;
                           <a id="toPlanList" data-url="'.admin_url('plan-list/'.$id).'" href="javascript:viod(0)">*点击这里(查看计划详细信息)</a>
                    </div>
                    <script >
                    let plan_list_id = '.$plan_list_id.';
                     $("#"+plan_list_id+"_to_sole_dispatch").on("click",function (){
                        let url = $(this).attr("data-url")
                        layer.closeAll();
                         parent.layer.open({
                          type: 2,
                          title: "鞋底派工",
                          shadeClose: true,
                          shade: false,
                          maxmin: true, //开启最大化最小化按钮
                           area: ["'.$width.'", "'.$height.'"],
                          content: url
                        });
                    })
                    $("#toPlanList").on("click",function (){
                        let url = $(this).attr("data-url")
                         parent.layer.open({
                          type: 2,
                          title: "计划单详情",
                          shadeClose: true,
                          shade: false,
                          maxmin: true, //开启最大化最小化按钮
                          area: ["'.$width.'", "'.$height.'"],
                          content: url
                        });
                    })

</script>
                    ';
            });
            $grid->disableFilter();
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRefreshButton();
            $grid->disableRowSelector();
            $grid->withBorder();
            $grid->paginate(15);
        });
        return $grid;
    }

    /**
     * 鞋底派工单
     */
    public function soleIndex(Content $content){
        return $content
            ->title('鞋底派工')
            ->row(function (Row $row)  {
                $row->column(12, $this->soleGrid());
            });
    }
    /**
     * 鞋底派工单数据
     */
    protected function soleGrid()
    {
        $plan_status_arr =config('plan.plan_status_html');
        $plan_status =config('plan.plan_status_simple_html');
        return Grid::make(new PlanList(), function (Grid $grid) use($plan_status_arr,$plan_status){
            $plan_list_model = new  PlanList();
            $dispatch_model = new DispatchModel();
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->model()->where('is_void','0')
                ->orderBy('created_at','desc');
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

            $grid->column('dispatch_num', '已派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'sole');
                return  $arr['dispatch_num'];
            });
            $grid->column('wait_num', '未派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'sole');
                return  $arr['wait_num'];
            });
            $grid->column('code_33', '33')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'33','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'34','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'35','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'36','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'37','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'38','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'39','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'40','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'41','sole');
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

            $grid->column('box_label_dispatch','订制箱标')
                ->if(function ($column) use($dispatch_model){
                    $is_has_sole = $dispatch_model->hasSoleDispatch($this->id);
                    return $is_has_sole;
                })
                ->dialog(function (){
                    return ['type'=>'url','url'=> admin_url('box-label-dispatch/create/'.$this->id.'?dialog=1&keep=1'),
                            'value'=>'<span style="text-decoration: underline">订制箱标</span>', 'width'=>'900px',
                            'height'=>'650px'];
                })
                ->else()
                ->display(function(){
                    return '订制箱标';
                });

            $grid->column('dispatch_detail', '派工详情')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('plan-list-diapatch/' . $this->id .'?dialog=1'),
                        'value'=>'<span style="text-decoration: underline">派工详情</span>', 'width'=>'900px',
                        'height'=>'600px'];
            });
            $grid->column('storage_out_status')->display(function () {
                return config('plan.plan_status_html')[$this->storage_out_status];
            });

            $all_num = PlanList::where('is_void','0')->sum('spec_num');
            $delivery_num = PlanList::where('is_void','0')->sum('delivery_num');
            $grid->tools(
                new PlanListDispatchMultiPrint('批量派工打印')
            );
            $grid->header(function ($query) use($delivery_num,$all_num){

                $wait_num =is_float_number( $all_num-$delivery_num);
                $delivery_num =is_float_number( $delivery_num);
                $all_num =is_float_number( $all_num);
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $width=config("plan.dialog.width");
                $height= config("plan.dialog.height");
                Form::dialog('新增计划单')
                    ->click('.create-plan-list-from-sole-form-dialog') // 绑定点击按钮
                    ->url('plan-list/create?from=sole') // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                    ->width(config('plan.dialog.width')) // 指定弹窗宽度，可填写百分比，默认 720px
                    ->height(config('plan.dialog.height')) // 指定弹窗高度，可填写百分比，默认 690px
                    ->success('Dcat.reload()'); // 新增成功后刷新页面
                return <<<ERD
                        <div style="position: absolute;left:95px;top:-30px;">
                          <button
class='create-plan-list-from-sole-form-dialog btn btn-primary btn-mini btn-sm'>新增</button>
                          <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                            <a href="javascript:void(0)" id="export_no_storage_out" class="btn btn-sm btn-info"  title="导出未出库信息（请先选择时间)">
                               <span class="hidden-xs">&nbsp;&nbsp;导出未出库信息（请先选择时间）&nbsp;&nbsp;</span>
                            </a>
                        </div>
                         <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                            <a href="' . admin_url('temp-plan-list') . '" class="btn btn-sm btn-info" title="临时输单">
                               <span class="hidden-xs">&nbsp;&nbsp;临时输单&nbsp;&nbsp;</span>
                            </a>
                        </div>
                          <div class="btn-group pull-left grid-create-btn" style="margin-right: 10px">
                            <a href="' . admin_url('plan-list-delivery-paper?_export_=all') . '" class="btn btn-sm btn-info" target="_blank" title="导出计划单对应的出货票据">
                               <span class="hidden-xs">&nbsp;&nbsp;导出计划单对应的出货票据&nbsp;&nbsp;</span>
                            </a>
                        </div>

                        </div>
                        </div>
                        <div style="position: relative;top:5px;">
                            <label style="margin-left:0px;">计划单发货情况  </label>&nbsp; &nbsp; <label >
                             订单数量:<span class="text-danger">{$all_num}</span>双 </label>
                            &nbsp; &nbsp;  <label >已发数量:<span class="text-danger">{$delivery_num}</span>双</label>
                            &nbsp; &nbsp; <label >未发数量:<span class="text-danger">{$wait_num}</span>双</label>
                        </div>
ERD;
            });
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->disableActions();
            $grid->toolsWithOutline(false);
            $grid->tableWidth("180%");

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id','客户名称')
                    ->selectResource('dialog/client')
                    ->options(function ($v) {
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->like('company_model')->width(2);
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
                })->width(4);

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
                })->width(4);
                $filter->like('client_order_no')->width(2);
                //计划类型
                $filter->equal('plan_category_id','计划类型')->select('/api/plan-category')->width(2);
                $filter->customSelect('need_to_deliver',["0"=>'全部','1'=>'是','2'=>'否'],function ($q){
                    if($this->chooseTrue==1){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->where('delivery_at','>=',$yesterday)
                            ->where('delivery_at','<=',$tomorrow);
                    }elseif($this->chooseTrue==2){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->orWhere('delivery_at','<',$yesterday)
                            ->orWhere('delivery_at','>',$tomorrow);
                    }
                },'急需发货')->width(2);
                $filter->equal('status')->select(config('plan.plan_status_simple'))->width(2);
                $filter->equal('product_category_id','产品类型')->select('api/product-category')->width(2);
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
            //导出
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
                        $arr = $plan_list_model->getWaitDispatchDetailNum($row['id'],$i,'sole')['all'];
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
     * 注塑派工单
     */
    public function injectMoldIndex(Content $content){
        return $content
            ->title('注塑派工')
            ->row(function (Row $row)  {
                $row->column(12, $this->injectMoldGrid());
            });
    }
    /**
     * 注塑派工单数据
     */
    protected function injectMoldGrid()
    {
        $plan_status_arr =config('plan.plan_status_html');
        $plan_status =config('plan.plan_status_simple_html');
        return Grid::make(new PlanList(), function (Grid $grid) use($plan_status_arr,$plan_status){
            $plan_list_model = new  PlanList();
            $dispatch_model = new DispatchModel();
            $grid->model()->where('is_void','0')->orderBy('created_at','desc');
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
                $img = ClientSoleInformation::where('company_model',$this->company_model)
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
            $grid->column('inject_mold_dispatch','订制注塑')
                ->if(function ($column) use($dispatch_model){
                    $is_has_sole = $dispatch_model->hasSoleDispatch($this->id);
                    return $is_has_sole;
                })
                ->dialog(function (){
                    return ['type'=>'url','url'=> admin_url('inject-mold-dispatch/create/'.$this->id.'?dialog=1&keep=1'),
                            'value'=>'<span style="text-decoration: underline">订制注塑</span>', 'width'=>'900px',
                            'height'=>'650px'];
                })
                ->else()
                ->display(function(){
                    return '订制注塑';
                });
            $grid->column('dispatch_num', '已派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'sole');
                return  $arr['dispatch_num'];
            });
            $grid->column('wait_num', '未派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'sole');
                return  $arr['wait_num'];
            });
            $grid->column('code_33', '33')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'33','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'34','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'35','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'36','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'37','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'38','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'39','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'40','sole');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'41','sole');
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
            $grid->column('dispatch_detail', '派工详情')->dialog(function (){
                return ['type'=>'url','url'=> admin_url('plan-list-diapatch/' . $this->id .'?dialog=1'),
                        'value'=>'<span style="text-decoration: underline">派工详情</span>', 'width'=>'900px',
                        'height'=>'600px'];
            });
            $grid->column('storage_out_status')->display(function () {
                return config('plan.plan_status_html')[$this->storage_out_status];
            });

            $all_num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('is_void','0');
            })->sum('num');
            $fa_num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('is_void','0');
            })->sum('inject_mold_dispatch_num');

            $grid->header(function ($query) use($fa_num,$all_num){
                return '
                    <div style="position: absolute;top:-25px;left:100px;">
                        <label style="margin-left:0px;">计划单发货情况  </label>&nbsp; &nbsp;
                         <label >订单数量:<span class="text-danger">'.is_float_number($all_num).'</span>双 </label>
                         <label >已派工数量:<span class="text-danger">'.is_float_number($fa_num).'</span>双</label>
                         <label >未派工数量:<span class="text-danger">'.is_float_number($all_num-$fa_num).'</span>双</label>
                    </div>
                    ';
            });

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->disableActions();
            $grid->toolsWithOutline(false);
            $grid->tableWidth("150%");
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id','客户名称')
                    ->selectResource('dialog/client')
                    ->options(function ($v) {
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->like('company_model')->width(2);
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
                })->width(4);

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
                })->width(4);
                $filter->like('client_order_no')->width(2);
                //计划类型
                $filter->equal('plan_category_id','计划类型')->select('/api/plan-category')->width(2);
                $filter->customSelect('need_to_deliver',["0"=>'全部','1'=>'是','2'=>'否'],function ($q){
                    if($this->chooseTrue==1){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->where('delivery_at','>=',$yesterday)
                            ->where('delivery_at','<=',$tomorrow);
                    }elseif($this->chooseTrue==2){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->orWhere('delivery_at','<',$yesterday)
                            ->orWhere('delivery_at','>',$tomorrow);
                    }
                },'急需发货')->width(2);
                $filter->equal('status')->select(config('plan.plan_status_simple'))->width(2);
                $filter->equal('product_category_id','产品类型')->select('api/product-category')->width(2);
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
            //导出
            $titles = [
                'created_at'=>'订制时间',
                'no'=>'计划编号',
                'kehu_name'=>'客户',
                'kehu_order_dno' => '客户计划单号',
                'product_times' => '生产周期',
                'yewuyuan' => '业务员',
                'product_category_name' => '产品类型',
                'company_model' => '型号',
                'carft_color_name' => '工艺颜色',
                'spec_num' => '订单数',
                'delivery_num' => '已发数量',
                'wait_num' => '未发数量',
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
            $filename = '注塑派工'.date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                $plan_list_model = new PlanList();
                foreach ($rows as $index => &$row) {
                    $row['spec_num'] = $row->spec_num;
                    $row['delivery_num'] = $row->delivery_num;
                    $row['wait_num'] = $row->spec_num-$row->delivery_num;
                    $row['33'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],33,'sole')['all'];
                    $row['34'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],34,'sole')['all'];
                    $row['35'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],35,'sole')['all'];
                    $row['36'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],36,'sole')['all'];
                    $row['37'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],37,'sole')['all'];
                    $row['38'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],38,'sole')['all'];
                    $row['39'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],39,'sole')['all'];
                    $row['40'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],40,'sole')['all'];
                    $row['41'] = $plan_list_model->getWaitDispatchDetailNum($row['id'],41,'sole')['all'];
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 箱标派工单
     */
    public function boxLabelIndex(Content $content){
        return $content
            ->title('箱标派工')
            ->row(function (Row $row)  {
                $row->column(12, $this->boxLabelGrid());
            });
    }
    /**
     * 箱标派工单数据
     */
    protected function boxLabelGrid()
    {
        $plan_status_arr =config('plan.plan_status_html');
        $plan_status =config('plan.plan_status_simple_html');
        return Grid::make(new PlanList(), function (Grid $grid) use($plan_status_arr,$plan_status){
            $plan_list_model = new  PlanList();
            $dispatch_model = new DispatchModel();
            $grid->model()->where('is_void','0')->orderBy('created_at','desc');
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
                $img = ClientSoleInformation::where('company_model',$this->company_model)
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
            $grid->column('dispatch_num', '已派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'box_label');
                return  $arr['dispatch_num'];
            });

            $grid->column('box_label_dispatch','订制箱标')
                ->if(function ($column) use($dispatch_model){
                    $is_has_sole = $dispatch_model->hasSoleDispatch($this->id);
                    return $is_has_sole;
                })
                ->dialog(function (){
                    return ['type'=>'url','url'=> admin_url('box-label-dispatch/create/'.$this->id.'?dialog=1&keep=1'),
                            'value'=>'<span style="text-decoration: underline">订制箱标</span>', 'width'=>'900px',
                            'height'=>'650px'];
                })
                ->else()
                ->display(function(){
                    return '订制箱标';
                });
            $grid->column('box_label_status', __('箱标派工'))->display(function (){
                return config('plan.plan_status_html')[$this->box_label_status];
            });
            $grid->column('wait_num', '未派工')->display(function ()  use($plan_list_model){
                $arr = $plan_list_model->getDispatchAllNum($this->id,'box_label');
                return  $arr['wait_num'];
            });
            $grid->column('code_33', '33')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'33','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'34','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'35','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'36','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'37','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'38','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'39','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'40','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($plan_list_model){
                $arr = $plan_list_model->getWaitDispatchDetailNum($this->id,'41','box_label');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'<span class="text-danger">/</span>'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");

            $all_num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('is_void','0');
            })->sum('num');
            $grid->header(function ($query) use($all_num){
                return '
                    <div>
                         <label >计划数量:<span class="text-danger">'.$all_num.'</span>双
                    </div>
                    ';
            });
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->disableActions();
            $grid->tableWidth("140%");
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id','客户名称')
                    ->selectResource('dialog/client')
                    ->options(function ($v) {
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->like('company_model')->width(2);
                $filter->multiInput('client_model',function ($qq){
                    if($this->input1){
                        $qq->orWhere('client_model','like','%'.$this->input1.'%');
                    }
                },'',1)->width(2);
                $filter->like('craft_color_name')->width(2);
                $filter->like('client_order_no')->width(2);
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
                },'',4)->width(4);
                $filter->like('client_order_no')->width(2);
                //计划类型
                $filter->equal('plan_category_id','计划类型')->select('/api/plan-category')->width(2);
                $filter->customSelect('need_to_deliver',["0"=>'全部','1'=>'是','2'=>'否'],function ($q){
                    if($this->chooseTrue==1){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->where('delivery_at','>=',$yesterday)
                            ->where('delivery_at','<=',$tomorrow);
                    }elseif($this->chooseTrue==2){
                        $yesterday = Carbon::yesterday();
                        $tomorrow = Carbon::tomorrow();
                        $today = Carbon::today();
                        $q->orWhere('delivery_at','<',$yesterday)
                            ->orWhere('delivery_at','>',$tomorrow);
                    }
                },'急需发货')->width(2);
                $filter->equal('status')->select(config('plan.plan_status_simple'))->width(2);
                $filter->equal('product_category_id','产品类型')->select('api/product-category')->width(2);
                $filter->between('created_at', '计划时间')->date()->width(3);
            });
        });
    }
    /**
     * 鞋底派工单记录
     */
    public function soleLogIndex(Content $content){
        return $content
            ->title('鞋底派工单记录')
            ->row(function (Row $row)  {
                $row->column(12, $this->soleLogGrid());
            });
    }

    /**
     * 鞋底派工单记录---详情
     */
    protected function soleLogGrid()
    {
        return Grid::make(DispatchModel::with(['plan_list']), function (Grid $grid) {
            $dispatch_status = $this->dispatch_status;
            $status = $this->status;
            $dispatch_model = new DispatchModel();
            $grid->model()->where('type','sole')
                ->orderBy('created_at','desc');
            $grid->column('created_at','派工日期')->width("80px");
            $grid->column('plan_list_no')->display(function (){
                return $this->plan_list_no;
            });
            $grid->column('dispatch_no');
            $grid->column('sole_dispatch_paper','单据号')->display(function (){
                $paper = SoleDispatchPaper::where('dispatch_id',$this->id)->first();
                if($paper){
                    return $paper->no;
                }else{
                    return '-';
                }
            });
            $grid->column('client_name');
            $grid->column('client_model');
            $grid->column('craft_color_name')->width("120px");
            $grid->column('product_category_name');
            $grid->column('sole_material_name');
            $grid->column('company_model');
            $grid->column('spec_num','计划单总数')->display(function (){
                return is_float_number($this->plan_list['spec_num']);
            });
            $grid->column('all_num','派工总数')->display(function (){
                return is_float_number($this->all_num);
            });
            $grid->column('void','状态')->action(SoleDispatchVoid::class);
            $grid->column('code_33', '33')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('process_workshop')->display(function(){
                return config('plan.dispatch_process_workshop')[$this->process_workshop];
            });
            $grid->column('paigongchuku', __('中转出库'))->display(function () use($dispatch_status){
                return config('plan.plan_status_html')[$this->storage_out_status];
            });

            $grid->column('status', __('派工状态'))->display(function () use($status){
                return config('plan.plan_status_html')[$this->status];
            });

            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableActions();
            $grid->withBorder();
            $grid->tableWidth('135%');
            $grid->paginate(15);
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id','客户')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->where('company_model',function ($query){
                    $query->where('company_model','like',"%{$this->input}%");
                })->width(2);
                $filter->where('client_model',function ($query){
                    $query->where('client_model','like',"%{$this->input}%");
                })->width(2);
                $filter->where('craft_color_name',function ($query){
                    $query->where('craft_color_name','like',"%{$this->input}%");
                })->width(2);
                $filter->equal('sole_material_id','鞋底用料')
                    ->selectResource('dialog/sole-material')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return SoleMaterial::find($v)->pluck('sole_material_name', 'id');
                    })->width(2);

                $filter->equal('product_category_id')->select('api/product-category')->width(2);
                $filter->equal('status')->select($this->dispatch_status)->width(2);
                $filter->equal('is_void','状态')->select(['0'=>'禁用','1'=>'正常'])->width(1);
                $filter->equal('is_print','是否打印')->select(['0'=>'未打印','1'=>'已打印'])->width(2);

                $filter->multiInput('plan_list_no',function ($qq){
                    if($this->input1){
                        $qq->orwhereHas('plan_list',function ($q){
                            $q->orWhere('plan_list_no','like','%'.$this->input1.'%');
                        });
                    }
                    if($this->input2){
                        $qq->orwhereHas('plan_list',function ($q){
                            $q->orWhere('plan_list_no','like','%'.$this->input2.'%');
                        });
                    }
                    if($this->input3){
                        $qq->orwhereHas('plan_list',function ($q){
                            $q->orWhere('plan_list_no','like','%'.$this->input3.'%');
                        });
                    }
                },'',3)->width(4);
                $filter->between('created_at','派工时间')->date()->width(3);

            });
            $grid->header(function ($query){
                $arr = $query->toArray();
                $ids = array_column($arr,'plan_list_id');

                $all= PlanList::whereIn('id',$ids)->sum('spec_num');
                $today = DispatchDetail::whereDate('created_at',date("Y-m-d"))->sum('num');
                return '
                        <div style="position: absolute;top:-25px;left:100px;">
                       <label class="text-danger"> 计划单派工数量:'.is_float_number($all).' /双 </label>&nbsp;&nbsp;
                       <label class="text-danger"> 今日派工数量:'.is_float_number($today).' /双 </label>
                        </div>
                    ';
            });
            $grid->disableCreateButton();
            $grid->disableActions();
            //批量操作
//            $grid->batchActions(function ($batch) {
//                $batch->add(new XiedipaigongMultiPrint('批量打印'));
//            });
            //导出
            $titles = [
                'plan_list_no' => '计划单号', 'dispatch_no' => '派工单号',
                'client_name' => '客户名称', 'company_model' => '雷力型号',
                'craft_color_name' => '工艺颜色',
                'sole_material_name' => '材料用料',
                'carft_skill_name' => '工艺类型',
                'dispatch_type' => '派工类型',
                'all_num' => '派工数量',

            ];
            $filename = '鞋底派工记录'.date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['dispatch_type'] = config('plan.dispatch_process_workshop')['sole'];

                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 鞋底派工单记录
     */
    public function injectMoldLogIndex(Content $content){
        return $content
            ->title('注塑派工单记录')
            ->row(function (Row $row)  {
                $row->column(12, $this->injectMoldLogGrid());
            });
    }

    /**
     * 注塑派工单记录---详情
     */
    protected function injectMoldLogGrid()
    {
        return Grid::make(DispatchDetail::with(['dispatch_info','plan_list']), function (Grid $grid) {
            $dispatch_status = $this->dispatch_status;
            $status = $this->status;
            $dispatch_detail_model = new DispatchDetail();
            $grid->model()->whereHas('dispatch_info',function ($q){
                $q->where('type','inject_mold');
            })->orderBy('created_at','desc');
            $grid->column('created_at','派工日期')->width("80px");
            $grid->column('plan_list_no')->display(function (){
                return $this->plan_list['plan_list_no'];
            });
            $grid->column('dispatch_no')->display(function (){
                return $this->dispatch_info['dispatch_no'];
            });
            $grid->column('paper_no','单据号')->display(function (){
               $paper =  InjectMoldDispatchPaper::where('dispatch_id',$this->dispatch_id)->first();
               if($paper){
                   return $paper->no;
               }else{
                   return '-';
               }
            });
            $grid->column('client_name')->display(function (){
                return $this->dispatch_info['client_name'];
            });
            $grid->column('company_model')->display(function (){
                return $this->dispatch_info['company_model'];
            });
            $grid->column('spec')->display(function (){
                return $this->spec;
            });
            $grid->column('craft_color_name')->display(function (){
                return $this->dispatch_info['craft_color_name'];
            })->width("120px");
            $grid->column('sole_material_name')->display(function (){
                return $this->dispatch_info['sole_material_name'];
            });
            $grid->column('carft_skill_name')->display(function (){
                return $this->dispatch_info['carft_skill_name'];
            });
            $grid->column('type')->display(function (){
                return config('plan.dispatch_process_workshop')['inject_mold'];
            });
            $grid->column('num','派工数量');
            $grid->column('storage_in','完成数量');
            $grid->column('dispatch_status','派工情况')->display(function (){
               return  config('plan.status')[$this->storage_in_status];
            });
            $grid->column('is_print','是否打印')->display(function (){
                return  config('plan.print_status')[$this->is_print];
            });
            $grid->column('to_print','打印')->display(function (){
                if($this->is_print=='1'){
                    return '-';
                }else{
                    $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                    $_token= csrf_token();
                    $width=config("plan.dialog.width");
                    $height= config("plan.dialog.height");
                    $url= admin_url('mold-information/'.$this->id.'/edit?dialog=1');
                    $no  =getPaperOrder('inject_mold_dispatch_paper','',11,'no');
                    return '<a  id="inject_mold_dispatch_codeprint_'.$this->id.'" data-id="'.$this->id.'" ><i class="fa fa-print "></i></a>
<script >
$(function() {
 var envheader ="'.$env_prefix.'";
var width ="'.$width.'";
var height ="'.$height.'";
var no ="'.$no.'";

  $("#inject_mold_dispatch_codeprint_'.$this->id.'").on("click",function() {
    layer.open({
              type: 2,
              title: "注塑派工打印",
              shadeClose: true,
              shade: false,
              maxmin: true, //开启最大化最小化按钮
              area: [width, height],
              content: "/"+envheader+"/inject-mold-dispatch/print?no="+no+"&id="+'.$this->id.'
            });

  })
})
</script>
';
                }
            });
            $grid->column('is_void','状态')->display(function (){
                return config('plan.paper_void')[$this->dispatch_info['is_void']];
            });

            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableActions();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->where('company_model',function ($query){
                    $query->whereHas('dispatch_info',function ($q){
                        $q->where('company_model','like',"%{$this->input}%");
                    });
                })->width(2);
                $filter->where('sole_material_name',function ($query){
                    $query->whereHas('dispatch_info',function ($q){
                        $q->where('sole_material_name','like',"%{$this->input}%");
                    });
                })->width(2);
                $filter->where('craft_color_name',function ($query){
                    $query->whereHas('dispatch_info',function ($q){
                        $q->where('craft_color_name','like',"%{$this->input}%");
                    });
                })->width(2);
                $filter->where('dispatch_no',function ($query){
                    $query->whereHas('dispatch_info',function ($q){
                        $q->where('dispatch_no','like',"%{$this->input}%");
                    });
                })->width(2);
                $filter->equal('storage_in_status','派工状态')->select($this->dispatch_status)->width(2);
                $filter->equal('is_print','是否打印')->select(['0'=>'未打印','1'=>'已打印'])->width(2);
            });

            $grid->header(function ($query){
                $all_num= DispatchDetail::whereHas('dispatch_info',function ($q){
                    $q->where('is_void','0');
                })->sum('num');
                $complate_num = DispatchDetail::whereHas('dispatch_info',function ($q){
                    $q->where('is_void','0');
                })->sum('storage_in');
                $complate_num = is_float_number($complate_num);
                $all_num = is_float_number($all_num);
//                Form::dialog('新增注塑派工')
//                    ->click('.create-inject-mold-from-log-form-dialog') // 绑定点击按钮
//                    ->url('inject-mold-dispatch/create?dialog=1&keep=1') // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
//                    ->width(config('plan.dialog.width')) // 指定弹窗宽度，可填写百分比，默认 720px
//                    ->height(config('plan.dialog.height')) // 指定弹窗高度，可填写百分比，默认 690px
//                    ->success('Dcat.reload()'); // 新增成功后刷新页面
//                <button
//class='create-inject-mold-from-log-form-dialog btn btn-primary btn-mini btn-sm'>新增</button>
                return <<<ERD
                        <div style="position: absolute;left:95px;top:-30px;">

                        <label class="text-danger"> 派工数量汇总:{$all_num} /双 </label>&nbsp;&nbsp;
                        <label class="text-danger"> 完成数量汇总:{$complate_num} /双 </label>
                        </div>


ERD;
            });
            $grid->disableCreateButton();
            $grid->disableActions();
            //导出
            $titles = [
                'plan_list_no' => '计划单号', 'dispatch_no' => '派工单号',
                'client_name' => '客户名称', 'company_model' => '鞋跟型号',
                // 'purchase_spec_name' => '明细规格',
                'craft_color_name' => '工艺颜色',
                'material_name' => '材料用料',
                'carft_name' => '工艺类型',
                'paigong_type' => '派工类型',
                'paidan_num' => '派工数量',
//                'complete_num' => '完成数量',
//                'paidan_status' => '派工情况',
//                'is_print' => '是否打印'
            ];
            $filename = 'Xiedi_'.date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['plan_no'] = $row['plan_no'];
                    $row['no'] = $row['no'];
                    $row['kehu_name'] = $row['kehu_name'];
                    $row['company_model'] = $row['company_model'];
                    $row['carft_color_name'] = $row['carft_color_name'];
                    $row['material_name'] = $row['material_name'];
                    $row['carft_name'] = $row['carft_name'];
                    $row['paigong_type'] = $row['paigong_type'];
//                    $row['paidan_status'] = $this->paidan_status[$row['paidan_status']];
//                    $row['is_print'] = $row['is_print']>0?'已打印':'未打印';
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 箱标派工单记录
     */
    public function boxLabelLogIndex(Content $content){
        return $content
            ->title('箱标派工单记录')
            ->row(function (Row $row)  {
                $row->column(12, $this->boxLabelLogGrid());
            });
    }

    /**
     * 鞋底派工单记录---详情
     */
    protected function boxLabelLogGrid()
    {
        return Grid::make(DispatchModel::with(['plan_list']), function (Grid $grid) {
            $dispatch_status = $this->dispatch_status;
            $status = $this->status;
            $dispatch_model = new DispatchModel();
            $grid->model()->where('type','box_label')
                ->orderBy('created_at','desc');
            $grid->column('created_at','派工日期')->width("80px");
            $grid->column('plan_list_no')->display(function (){
                return $this->plan_list_no;
            });
            $grid->column('dispatch_no');
            $grid->column('box_label_dispatch_paper','单据号')->display(function (){
                $paper = BoxLabelDispatchPaper::where('dispatch_id',$this->id)->first();
                if($paper){
                    return $paper->no;
                }else{
                    return '-';
                }
            });
            $grid->column('client_name');
            $grid->column('client_model');
            $grid->column('craft_color_name')->width("120px");
            $grid->column('product_category_name');
            $grid->column('company_model');
            $grid->column('spec_num','计划单总数')->display(function (){
                return $this->plan_list['spec_num'];
            });
            $grid->column('all_num','派工总数');
            $grid->column('void','状态')->action(BoxLabelDispatchVoid::class);
            $grid->column('code_33', '33')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'33');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_34', '34')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'34');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_35', '35')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'35');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_36', '36')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'36');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_37', '37')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'37');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_38', '38')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'38');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_39', '39')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'39');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_40', '40')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'40');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->column('code_41', '41')->display(function () use($dispatch_model){
                $arr = $dispatch_model->getDetailNum($this->id,'41');
                if($arr['left']>0||$arr['right']>0){
                    return $arr['left'].'/'.$arr['right'];
                }else{
                    return  $arr['all'];
                }
            })->width("50px");
            $grid->disableBatchDelete();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableActions();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->paginate(15);
            $grid->tableWidth('115%');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('client_name')->width(2);
                $filter->like('company_model')->width(2);
                $filter->like('client_model')->width(2);
                $filter->like('sole_material_name')->width(2);
                $filter->like('craft_color_name')->width(2);
                $filter->like('plan_list_no')->width(2);
                $filter->like('dispatch_no')->width(2);
            });
            $grid->header(function ($query){
                $arr = $query->toArray();
                $ids = array_column($arr,'plan_list_id');

                $all= DispatchDetail::whereHas('dispatch_info',function ($q){
                    $q->where('is_void','0');
                })->sum('num');
                return '
                       <label class="text-danger"> 派工数量汇总:'.is_float_number($all).' /双 </label>
                    ';
            });
            $grid->disableCreateButton();
            $grid->disableActions();
            $titles = [
                'plan_list_no' => '计划单号', 'dispatch_no' => '派工单号',
                'client_name' => '客户名称', 'company_model' => '雷力型号',
                'craft_color_name' => '工艺颜色',
                'sole_material_name' => '材料用料',
                'carft_skill_name' => '工艺类型',
                'dispatch_type' => '派工类型',
                'all_num' => '派工数量',

            ];
            $filename = '鞋底派工记录'.date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['dispatch_type'] = config('plan.dispatch_process_workshop')['box_label'];
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 鞋底派工 添加页面
     * @param Content $content
     * @return mixed
     */
    public function soleCreateH($id=0,Content $content,Request $request)
    {
        $is_dialog = $request->dialog?$request->dialog:0;
        $is_close = $request->close?$request->close:0;
        $is_print = $request->print?$request->print:0;
        $plan_id=$id?:0;
        return $content
            ->header('创建')
            ->body($this->soleFormH('create',$plan_id,$is_dialog,$is_close,$is_print));
    }
    /**
     * 注塑派工 添加页面
     * @param Content $content
     * @return mixed
     */
    public function injectMoldCreateH($id=0,Content $content,Request $request)
    {
        $is_dialog = $request->dialog?$request->dialog:0;
        $is_close = $request->close?$request->close:0;
        $is_print = $request->print?$request->print:0;
        $is_keep = $request->keep?$request->keep:0;
        $plan_id=$id?:0;
        return $content
            ->header('创建')
            ->body($this->injectMoldFormH('create',$plan_id,$is_keep,$is_print,$is_dialog,$is_close));
    }
    /**
     * 箱标派工 添加页面
     * @param Content $content
     * @return mixed
     */
    public function boxLabelCreateH($id=0,Content $content,Request $request)
    {
        $is_keep = $request->keep?$request->keep:0;
        $is_dialog = $request->dialog?$request->dialog:0;
        $is_print = $request->print?$request->print:'';
        $is_close = $request->close?$request->close:0;
        $plan_id=$id?:0;
        return $content
            ->header('创建')
            ->body($this->boxLabelFormH('create',$plan_id,$is_keep,$is_print,$is_dialog,$is_close));
    }

    /**
     * 鞋底派工form
     * @param $type
     * @param int $id
     * @param $is_dialog
     * @param $is_close
     * @param $is_print
     * @return Form
     */
    protected function soleFormH($type,$id=0,$is_dialog,$is_close,$is_print)
    {
        if($type=='create'){
            if($id>0){
                $plan_list_info = PlanList::find($id);
                if ($plan_list_info->is_void=='1') { // 验证逻辑
                    return Form::make(new DispatchModel(), function (Form $form) {
                        $form->html(function (){
                            return "<h3 align='center'>'计划单已作废,不可以操作~'</h3>";
                        });
                    });
                }
                $client_sole_information_info = ClientSoleInformation::find($plan_list_info->client_sole_information_id);
                $planListDetailInfo = PlanListDetail::where('plan_list_id',$id)->get([
                    'id','spec','type','num','sole_dispatch_num'
                ])->toArray();
                $usedata=[
                    'plan_list_id'=>$plan_list_info->id,
                    'plan_list_no'=>$plan_list_info->plan_list_no,
                    'client_order_no'=>$plan_list_info->client_order_no,
                    'client_name'=>$plan_list_info->client_name,
                    'client_id'=>$plan_list_info->client_id,
                    'client_model_id'=>$plan_list_info->client_model_id,
                    'client_model'=>$plan_list_info->client_model,
                    'carft_skill_id'=>$plan_list_info->carft_skill_id,
                    'carft_skill_name'=>$plan_list_info->carft_skill_name,
                    'company_model_id'=>$plan_list_info->company_model_id,
                    'company_model'=>$plan_list_info->company_model,
                    'sole_material_id'=>$client_sole_information_info->sole_material_id,
                    'sole_material_name'=>$client_sole_information_info->sole_material_name,
                    'craft_color_name'=>$plan_list_info->craft_color_name,
                    'craft_color_id'=>$plan_list_info->craft_color_id,
                    'product_category_id'=>$plan_list_info->product_category_id,
                    'product_category_name'=>$plan_list_info->product_category_name,
                    'type'=>config('plan.dispatch_type')['sole'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['sole'],
                    'process_department'=>config('plan.dispatch_process_department')['sole'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>$plan_list_info->plan_remark,
                    'inject_mold_ask'=>$client_sole_information_info->inject_mold_ask?:'',
                ];
                $detailarr = [];
            }
            else{
                $material_name_default='';
                $usedata=[
                    'plan_list_id'=>0,
                    'plan_list_no'=>'',
                    'client_name'=>'',
                    'client_id'=>0,
                    'sole_material_id'=>0,
                    'sole_material_name'=>'',
                    'company_model_id'=>0,
                    'company_model'=>'',
                    'carft_skill_id'=>0,
                    'carft_skill_name'=>'',
                    'client_model'=>'',
                    'craft_color_name'=>'',
                    'type'=>config('plan.dispatch_type')['sole'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['sole'],
                    'process_department'=>config('plan.dispatch_process_department')['sole'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>'',
                    'inject_mold_ask'=>'',
                    'client_order_no'=>'',

                ];
                $planListDetailInfo = [];
            }
        }

        return Form::make(new DispatchModel(), function (Form $form) use($usedata,
            $planListDetailInfo,$is_dialog,$is_close,$is_print){

            $form->column(12, function (Form $form)  use($usedata,$is_dialog,$is_close,$is_print){

                $form->hidden('is_dialog')->default($is_dialog);
                $form->hidden('type')->default('sole');
                $form->html(function () use($is_dialog,$is_close,$is_print){
                    $hidden_header='';
                    $close_layer='';
                    $scriptinfo='';
                    if($is_dialog){
                        $hidden_header = '<style>.box-header.with-border.mb-1{display: none!important}</style>';
                    }
                    if($is_close){
                        $close_layer = '<script>$(function() {
})</script>';
                    }
                    if($is_print){
                        $scriptinfo = '<script>
$(function() {
  window.open("'.urldecode($is_print).'")
})
</script>';
                    }
                    return $scriptinfo.$close_layer.$hidden_header.'*请先选择制定的计划单号进行鞋底派工';
                })->width(10,1);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->hidden('plan_list_id')->default($usedata['plan_list_id']);
                $form->text('plan_list_no')->default($usedata['plan_list_no'])->readOnly();
                $form->text('company_model')->default($usedata['company_model'])->readOnly();
                $form->hidden('company_model_id')->default($usedata['company_model_id']);
                $form->hidden('client_order_no')->default($usedata['client_order_no']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('client_name')->default($usedata['client_name'])->readOnly();
                $form->text('client_model')->default($usedata['client_model'])->readOnly();
                $form->hidden('client_id')->default($usedata['client_id']);
                $form->hidden('client_model_id')->default($usedata['client_model_id']);
            });
            $form->column(12, function (Form $form)  use($usedata,$planListDetailInfo){
                $form->html(function () use($planListDetailInfo){
                    $count = count($planListDetailInfo);
                    $arr=[];
                    $specarr = [];

                    foreach ($planListDetailInfo as $kk=>$vv){
                        $specarr[$kk]['id']=$vv['id'];
                        $specarr[$kk]['spec']=$vv['spec'];
                        if($vv['num']-$vv['sole_dispatch_num']>0){
                            $arr[$vv['id']]['spec'] = $vv['spec'];
                            $arr[$vv['id']]['num'] = $vv['num']-$vv['sole_dispatch_num'];
                            $arr[$vv['id']]['allnum'] = $vv['num'];
                            $arr[$vv['id']]['type'] = $vv['type'];
                            $arr[$vv['id']]['id'] = $vv['id'];
                        }
                    }
                    $dataarr = json_encode($arr);
                    $specarr = json_encode($specarr);
                    $plan_order_spec = json_encode(config('plan.type_text'));
                    return  <<<EHTML
<style>
.spec-top{background: #487cd0;color:#fff;padding:10px 25px}
.spec-title{position: relative;top:2px;padding-right:5px;}
#spec-table tr td,#total tr td{text-align: left}
#spec-table tr td span{
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
    <table id="spec-table" class="table">

    </table>
</div>
<div class="total">
  <table  class="table" id="total">
       <tr>
            <td width="70%" colspan="2" >&nbsp;</td>
           <td  width="30%" >合计数:<span id="sole_dispatch_total_num" style="text-align: center">0</span></td>
        </tr>
    </table>
</div>
<hr>
<script >
$(function() {
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
       '<td><span>派工数量:</span><input name="spec['+data.id+'][num]" value="'+data.num+'"  class="sole_dispatch_spec_num input-h1"></td>'+
        '</tr>';
  })
   $('#spec-table').append(arrhtml)
   calcunum();
  $(document).on('change', '.sole_dispatch_spec_num', function () {
       calcunum();
    })

})
   function calcunum() {
        var num=0;
        $.each($('.sole_dispatch_spec_num'),function() {
            var check_num = isNaN($(this).val())?0:$(this).val()
            num+=parseFloat(check_num);
        })
        num =num>0?num:0;
        $('#sole_dispatch_total_num').text(num)
   }
</script>
EHTML;
                },' ')->width(11,1);
            });

            $form->column(6, function (Form $form)  use($usedata){
                $form->hidden('carft_skill_id')->default($usedata['carft_skill_id'])->readOnly();
                $form->text('carft_skill_name')->default($usedata['carft_skill_name'])->readOnly();
                $form->hidden('sole_material_id')->default($usedata['sole_material_id']);
                $form->text('sole_material_name')->default($usedata['sole_material_name'])->readOnly();
                $form->text('dispatch_user_name')->default($usedata['dispatch_user_name'])->readOnly();
                $form->hidden('dispatch_user_id')->default($usedata['dispatch_user_id']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('craft_color_name')->default($usedata['craft_color_name'])->readOnly();
                $form->hidden('craft_color_id')->default($usedata['craft_color_id']);
                $form->hidden('product_category_id')->default($usedata['product_category_id']);
                $form->hidden('product_category_name')->default($usedata['product_category_name']);
                $form->html(function () use ($usedata){
                    return '<style>select{height:36px;}</style><select class="select_h" style="width:100px"><option value="">'.$usedata['type'].'</option></select>'.
                        '<select class="select_h" style="width:120px"><option value="">'.$usedata['process_workshop'].'</option></select>';
                },'派工类型');
                $form->text('process_department')->default($usedata['process_department'])->readOnly();
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->text('inject_mold_ask','注塑要求')->width(10,1)->default($usedata['inject_mold_ask']);
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->textarea('plan_remark')->width(10,1)->default($usedata['plan_remark']);
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
     * @param $type
     * @param int $id
     * @param $is_keep
     * @param $is_print
     * @param $is_dialog
     * @param $is_close
     * @return Form
     */
    protected function injectMoldFormH($type,$id=0,$is_keep,$is_print,$is_dialog,$is_close)
    {
        if($type=='create'){
            if($id>0){
                $plan_list_info = PlanList::find($id);
                if ($plan_list_info->is_void=='1') { // 验证逻辑
                    return Form::make(new DispatchModel(), function (Form $form) {
                        $form->html(function (){
                            return "<h3 align='center'>'计划单已作废,不可以操作~'</h3>";
                        });
                    });
                }
                $client_sole_information_info = ClientSoleInformation::find($plan_list_info->client_sole_information_id);
                $planListDetailInfo = PlanListDetail::where('plan_list_id',$id)->get([
                    'id','spec','type','num','inject_mold_dispatch_num'
                ])->toArray();

                $standardDetail = StandardDetail::where('company_model_id',$plan_list_info->company_model_id)->get([
                    'id','standard_detail_name'
                ]);
                $usedata=[
                    'plan_list_id'=>$plan_list_info->id,
                    'plan_list_no'=>$plan_list_info->plan_list_no,
                    'client_order_no'=>$plan_list_info->client_order_no,
                    'client_name'=>$plan_list_info->client_name,
                    'client_id'=>$plan_list_info->client_id,
                    'client_model_id'=>$plan_list_info->client_model_id,
                    'client_model'=>$plan_list_info->client_model,
                    'carft_skill_id'=>$plan_list_info->carft_skill_id,
                    'carft_skill_name'=>$plan_list_info->carft_skill_name,
                    'company_model_id'=>$plan_list_info->company_model_id,
                    'company_model'=>$plan_list_info->company_model,
                    'sole_material_id'=>$client_sole_information_info->sole_material_id,
                    'sole_material_name'=>$client_sole_information_info->sole_material_name,
                    'craft_color_name'=>$plan_list_info->craft_color_name,
                    'craft_color_id'=>$plan_list_info->craft_color_id,
                    'product_category_id'=>$plan_list_info->product_category_id,
                    'product_category_name'=>$plan_list_info->product_category_name,
                    'type'=>config('plan.dispatch_type')['inject_mold'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['inject_mold'],
                    'process_department'=>config('plan.dispatch_process_department')['inject_mold'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>$plan_list_info->plan_remark,
                    'inject_mold_ask'=>$client_sole_information_info->inject_mold_ask?:'',
                ];
                $detailarr = [];
            }
            else{
                $usedata=[
                    'plan_list_id'=>0,
                    'plan_list_no'=>'',
                    'client_name'=>'',
                    'client_id'=>0,
                    'sole_material_id'=>0,
                    'sole_material_name'=>'',
                    'company_model_id'=>0,
                    'company_model'=>'',
                    'carft_skill_id'=>0,
                    'carft_skill_name'=>'',
                    'client_model'=>'',
                    'craft_color_name'=>'',
                    'type'=>config('plan.dispatch_type')['inject_mold'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['inject_mold'],
                    'process_department'=>config('plan.dispatch_process_department')['inject_mold'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>'',
                    'inject_mold_ask'=>'',
                    'client_order_no'=>'',

                ];
                $planListDetailInfo = [];
            }
        }
        return Form::make(new DispatchModel(), function (Form $form) use($usedata,
            $standardDetail,$is_dialog,$is_close,$is_print,$is_keep){
            $form->column(12, function (Form $form)  use($usedata,$is_dialog,$is_close,$is_print,$is_keep){

                $form->hidden('is_dialog')->default($is_dialog);
                $form->hidden('is_keep')->default($is_keep);
                $form->hidden('type')->default('inject_mold');
                $form->html(function () use($is_dialog,$is_close,$is_print){
                    $hidden_header='';
                    $close_layer='';
                    $scriptinfo='';
                    if($is_dialog){
                        $hidden_header = '<style>.box-header.with-border.mb-1{display: none!important}</style>';
                    }
                    if($is_close){
                        $close_layer = '<script>$(function() {
})</script>';
                    }
                    if($is_print){
                        $scriptinfo = '<script>
$(function() {
   // printerPage();
   // window.open("'.urldecode($is_print).'")
})
function printerPage(){
            let url = "'.urldecode($is_print).'";
            layer.closeAll();
             parent.layer.open({
              type: 2,
              title: "注塑派工票据",
              shadeClose: true,
              shade: false,
              maxmin: true, //开启最大化最小化按钮
              area: ["800px", "800px"],
              content: url
            });
        }
</script>';
                    }
                    return $scriptinfo.$close_layer.$hidden_header.'*请先选择制定的计划单号进行注塑派工';
                })->width(10,1);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->hidden('plan_list_id')->default($usedata['plan_list_id']);
                $form->text('plan_list_no')->default($usedata['plan_list_no'])->readOnly();
                $form->text('company_model')->default($usedata['company_model'])->readOnly();
                $form->hidden('company_model_id')->default($usedata['company_model_id']);
                $form->hidden('client_order_no')->default($usedata['client_order_no']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('client_name')->default($usedata['client_name'])->readOnly();
                $form->text('client_model')->default($usedata['client_model'])->readOnly();
                $form->hidden('client_id')->default($usedata['client_id']);
                $form->hidden('client_model_id')->default($usedata['client_model_id']);
            });
            $form->column(12, function (Form $form)  use($usedata,$standardDetail){
                $form->html(function () use($standardDetail,$usedata){
                    $count = count($standardDetail);
                    $arr=[];
                    $specarr = [];

                    foreach ($standardDetail as $kk=>$vv){
                        $specarr[$kk]['id']=$vv['id'];
                        $specarr[$kk]['spec']=$vv['standard_detail_name'];
                    }
                    $specarr = json_encode($specarr);
                    $plan_list_id = $usedata['plan_list_id'];
                    $plan_order_spec = json_encode(config('plan.type_text'));
                    return  <<<EHTML
<style>
.spec-top{background: #487cd0;color:#fff;padding:10px 25px}
.spec-title{position: relative;top:2px;padding-right:5px;}
#spec-table tr td,#total tr td{text-align: left}
#spec-table tr td span{
display: inline-block;
    margin-right:10px;
}
.input-h1{text-align:center;width:200px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
.input-h2{text-align:center;width:80px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
select {width:100px}
.total{}
table tr td{
text-align:center
}
#plan_order_num{background: #fff;border-radius: 3px;border:1px solid #d9d9d9;outline:none;}
</style>
<div class="spec-top " style="">
    <span class="spec-title">请选择规格/尺码数量</span>
    <select name="" id="plan_list_num_zhusu_{$plan_list_id}" class=" col-md-1 select">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
    </select>
</div>
<div class="spec-body" id="spec-body">
    <table id="spec_table_inject_mold_{$plan_list_id}" class="table">

    </table>
</div>
<div class="total">
<table  class="table" id="total">
   <tr>
        <td width="70%" colspan="2" >&nbsp;</td>
       <td  width="30%" >合计数:<span id="inject_mold_dispatch_total_num" style="text-align: center">0</span></td>
    </tr>
</table>
</div>
<hr>
<script >
var specarr = {$specarr};
  var plan_order_spec = {$plan_order_spec};
  var arrhtml = '';
  var id={$plan_list_id}

   var optionshtml = '';
   $.each(specarr,function(index,data2) {
         optionshtml+='<option value="'+data2.id+'" data-id="'+data2.id+'" >'+data2.spec+'</option>'
     })
var oneline = ' <tr>'+
'<td width="50%"><div><span>明细规格</span>' +
'<select class="select-h" name="spec[spec][]">' +optionshtml+
'</select>'+
'<td width="50%"><span>派工数量:</span><input name="spec[num][]" value=""  class="input-h1 inject_mold_dispatch_spec_num"></td>'+
'</tr>';
$(function() {

   $('#spec_table_inject_mold_{$plan_list_id}').append(oneline)
   $(document).on('change','#plan_list_num_zhusu_{$plan_list_id}',function() {
      var that = this;
      var new_spec_num = $(that).val()
      var old_spec_num = $('#spec_table_inject_mold_{$plan_list_id} tr').length
      if(old_spec_num<new_spec_num){
          //增加
          var removenum = new_spec_num-old_spec_num

          var appendhtml = '';
          for(i=0;i<removenum;i++){
              appendhtml+=oneline
          }
           $('#spec_table_inject_mold_{$plan_list_id}').append(appendhtml)

      }else if(old_spec_num>new_spec_num){
          //删除
           $('#spec_table_inject_mold_{$plan_list_id}').find("tr:nth-child("+new_spec_num+")").nextAll().remove();
      }
  })
   calcunum();
   $(document).on('change', '.inject_mold_dispatch_spec_num', function () {
       calcunum();
    })
})
   function calcunum() {
        var num=0;
        $.each($('.inject_mold_dispatch_spec_num'),function() {
            var check_num = isNaN($(this).val())?0:$(this).val()
            num+=parseFloat(check_num);
        })
        num = num>0?num:0;
        $('#inject_mold_dispatch_total_num').text(num)
   }
</script>
EHTML;
                },' ')->width(11,1);
            });

            $form->column(6, function (Form $form)  use($usedata){
                $form->hidden('carft_skill_id')->default($usedata['carft_skill_id'])->readOnly();
                $form->text('carft_skill_name')->default($usedata['carft_skill_name'])->readOnly();
                $form->hidden('sole_material_id')->default($usedata['sole_material_id']);
                $form->text('sole_material_name')->default($usedata['sole_material_name'])->readOnly();
                $form->text('dispatch_user_name')->default($usedata['dispatch_user_name'])->readOnly();
                $form->hidden('dispatch_user_id')->default($usedata['dispatch_user_id']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('craft_color_name')->default($usedata['craft_color_name'])->readOnly();
                $form->hidden('craft_color_id')->default($usedata['craft_color_id']);
                $form->hidden('product_category_id')->default($usedata['product_category_id']);
                $form->hidden('product_category_name')->default($usedata['product_category_name']);
                $form->html(function () use ($usedata){
                    return '<style>select{height:36px;}</style><select class="select_h" style="width:100px"><option value="">'.$usedata['type'].'</option></select>'.
                        '<select class="select_h" style="width:120px"><option value="">'.$usedata['process_workshop'].'</option></select>';
                },'派工类型');
                $form->text('process_department')->default($usedata['process_department'])->readOnly();
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->text('inject_mold_ask','注塑要求')->width(10,1)->default($usedata['inject_mold_ask']);
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->textarea('plan_remark')->width(10,1)->default($usedata['plan_remark']);
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
     * 箱标派工 form
     * @param $type
     * @param int $id
     * @param $is_dialog
     * @param $is_close
     * @param $is_print
     * @param $is_keep
     * @return Form
     */
    protected function boxLabelFormH($type,$id=0,$is_keep,$is_print,$is_dialog,$is_close)
    {
        if($type=='create'){
            if($id>0){
                $plan_list_info = PlanList::find($id);
                if ($plan_list_info->is_void=='1') { // 验证逻辑
                    return Form::make(new DispatchModel(), function (Form $form) {
                        $form->html(function (){
                            return "<h3 align='center'>'计划单已作废,不可以操作~'</h3>";
                        });
                    });
                }
                $client_sole_information_info = ClientSoleInformation::find($plan_list_info->client_sole_information_id);
                $planListDetailInfo = PlanListDetail::where('plan_list_id',$id)->get([
                    'id','spec','type','num','box_label_dispatch_num'
                ])->toArray();
                $usedata=[
                    'plan_list_id'=>$plan_list_info->id,
                    'plan_list_no'=>$plan_list_info->plan_list_no,
                    'client_order_no'=>$plan_list_info->client_order_no,
                    'client_name'=>$plan_list_info->client_name,
                    'client_id'=>$plan_list_info->client_id,
                    'client_model_id'=>$plan_list_info->client_model_id,
                    'client_model'=>$plan_list_info->client_model,
                    'carft_skill_id'=>$plan_list_info->carft_skill_id,
                    'carft_skill_name'=>$plan_list_info->carft_skill_name,
                    'company_model_id'=>$plan_list_info->company_model_id,
                    'company_model'=>$plan_list_info->company_model,
                    'sole_material_id'=>$client_sole_information_info->sole_material_id,
                    'sole_material_name'=>$client_sole_information_info->sole_material_name,
                    'craft_color_name'=>$plan_list_info->craft_color_name,
                    'craft_color_id'=>$plan_list_info->craft_color_id,
                    'product_category_id'=>$plan_list_info->product_category_id,
                    'product_category_name'=>$plan_list_info->product_category_name,
                    'type'=>config('plan.dispatch_type')['box_label'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['box_label'],
                    'process_department'=>config('plan.dispatch_process_department')['box_label'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>$plan_list_info->plan_remark,
                    'inject_mold_ask'=>$client_sole_information_info->inject_mold_ask?:'',
                ];
                $detailarr = [];
            }
            else{
                $usedata=[
                    'plan_list_id'=>0,
                    'plan_list_no'=>'',
                    'client_name'=>'',
                    'client_id'=>0,
                    'sole_material_id'=>0,
                    'sole_material_name'=>'',
                    'company_model_id'=>0,
                    'company_model'=>'',
                    'carft_skill_id'=>0,
                    'carft_skill_name'=>'',
                    'client_model'=>'',
                    'craft_color_name'=>'',
                    'type'=>config('plan.dispatch_type')['box_label'],
                    'process_workshop'=>config('plan.dispatch_process_workshop')['box_label'],
                    'process_department'=>config('plan.dispatch_process_department')['box_label'],
                    'dispatch_user_name'=>Admin::user()->name,
                    'dispatch_user_id'=>Admin::user()->id,
                    'plan_remark'=>'',
                    'inject_mold_ask'=>'',
                    'client_order_no'=>'',

                ];
                $planListDetailInfo = [];
            }
        }
        return Form::make(new DispatchModel(), function (Form $form) use($usedata,
            $planListDetailInfo,$is_dialog,$is_close,$is_print,$is_keep){
            $form->column(12, function (Form $form)  use($usedata,$is_dialog,$is_close,$is_print,$is_keep){

                $form->hidden('is_dialog')->default($is_dialog);
                $form->hidden('type')->default('box_label');
                $form->html(function () use($is_dialog,$is_close,$is_print){
                    $hidden_header='';
                    $close_layer='';
                    $scriptinfo='';
                    if($is_dialog){
                        $hidden_header = '<style>.box-header.with-border.mb-1{display: none!important}</style>';
                    }
                    if($is_close){
                        $close_layer = '<script>$(function() {
})</script>';
                    }
                    if($is_print){
                        $scriptinfo = '<script>
$(function() {
   // printerPage();
    window.open("'.urldecode($is_print).'")
})
function printerPage(){
            let url = "'.urldecode($is_print).'";
            layer.closeAll();
             parent.layer.open({
              type: 2,
              title: "箱标派工票据",
              shadeClose: true,
              shade: false,
              maxmin: true, //开启最大化最小化按钮
              area: ["800px", "800px"],
              content: url
            });
        }
</script>';
                    }
                    return $scriptinfo.$close_layer.$hidden_header.'*请先选择制定的计划单号进行箱标派工';
                })->width(10,1);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->hidden('plan_list_id')->default($usedata['plan_list_id']);
                $form->text('plan_list_no')->default($usedata['plan_list_no'])->readOnly();
                $form->text('company_model')->default($usedata['company_model'])->readOnly();
                $form->hidden('company_model_id')->default($usedata['company_model_id']);
                $form->hidden('client_order_no')->default($usedata['client_order_no']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('client_name')->default($usedata['client_name'])->readOnly();
                $form->text('client_model')->default($usedata['client_model'])->readOnly();
                $form->hidden('client_id')->default($usedata['client_id']);
                $form->hidden('client_model_id')->default($usedata['client_model_id']);
            });
            $form->column(12, function (Form $form)  use($usedata,$planListDetailInfo){
                $form->html(function () use($planListDetailInfo){
                    $count = count($planListDetailInfo);
                    $arr=[];
                    $specarr = [];

                    foreach ($planListDetailInfo as $kk=>$vv){
                        $specarr[$kk]['id']=$vv['id'];
                        $specarr[$kk]['spec']=$vv['spec'];
                        if($vv['num']-$vv['box_label_dispatch_num']>0){
                            $arr[$vv['id']]['spec'] = $vv['spec'];
                            $arr[$vv['id']]['num'] = $vv['num']-$vv['box_label_dispatch_num'];
                            $arr[$vv['id']]['allnum'] = $vv['num'];
                            $arr[$vv['id']]['type'] = $vv['type'];
                            $arr[$vv['id']]['id'] = $vv['id'];
                        }
                    }
                    $dataarr = json_encode($arr);
                    $specarr = json_encode($specarr);
                    $plan_order_spec = json_encode(config('plan.type_text'));
                    return  <<<EHTML
<style>
.spec-top{background: #487cd0;color:#fff;padding:10px 25px}
.spec-title{position: relative;top:2px;padding-right:5px;}
#spec-table tr td,#total tr td{text-align: left}
#spec-table tr td span{
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
    <table id="spec-table" class="table">

    </table>
</div>
<div class="total">
<table  class="table" id="total">
   <tr>
        <td width="70%" colspan="2" >&nbsp;</td>
       <td  width="30%" >合计数:<span id="box_lable_dispatch_total_num" style="text-align: center">0</span></td>
    </tr>
</table>
</div>

<hr>
<script >
$(function() {
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
       '<td><span>派工数量:</span><input name="spec['+data.id+'][num]" value="'+data.num+'"  class="input-h1 box_lable_dispatch_spec_num"></td>'+
        '</tr>';
  })
   $('#spec-table').append(arrhtml)

   $(document).on('change', '.box_lable_dispatch_spec_num', function () {
       calcunum();
    })
    calcunum();
})
function calcunum() {
        var num=0;
        $.each($('.box_lable_dispatch_spec_num'),function() {
            var check_num = isNaN($(this).val())?0:$(this).val()
            num+=parseFloat(check_num);
        })
        num = num>0?num:0;
        $('#box_lable_dispatch_total_num').text(num)
   }
</script>
EHTML;
                },' ')->width(11,1);
            });

            $form->column(6, function (Form $form)  use($usedata){
                $form->hidden('carft_skill_id')->default($usedata['carft_skill_id'])->readOnly();
                $form->text('carft_skill_name')->default($usedata['carft_skill_name'])->readOnly();
                $form->hidden('sole_material_id')->default($usedata['sole_material_id']);
                $form->text('sole_material_name')->default($usedata['sole_material_name'])->readOnly();
                $form->text('dispatch_user_name')->default($usedata['dispatch_user_name'])->readOnly();
                $form->hidden('dispatch_user_id')->default($usedata['dispatch_user_id']);
            });
            $form->column(6, function (Form $form)  use($usedata){
                $form->text('craft_color_name')->default($usedata['craft_color_name'])->readOnly();
                $form->hidden('craft_color_id')->default($usedata['craft_color_id']);
                $form->hidden('product_category_id')->default($usedata['product_category_id']);
                $form->hidden('product_category_name')->default($usedata['product_category_name']);
                $form->html(function () use ($usedata){
                    return '<style>select{height:36px;}</style><select class="select_h" style="width:100px"><option value="">'.$usedata['type'].'</option></select>'.
                        '<select class="select_h" style="width:120px"><option value="">'.$usedata['process_workshop'].'</option></select>';
                },'派工类型');
                $form->text('process_department')->default($usedata['process_department'])->readOnly();
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->text('inject_mold_ask','注塑要求')->width(10,1)->default($usedata['inject_mold_ask']);
            });
            $form->column(12, function (Form $form)  use($usedata){
                $form->textarea('plan_remark')->width(10,1)->default($usedata['plan_remark']);
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
     * 保存页面
     * @param ProductRequest $request
     * @return mixed
     */
    public function soleStoreH(Request $request)
    {
        $res = $this->soleSaveH($request);
        $is_dialog = $request->is_dialog;
        $form=new Form();
        if($is_dialog){
            if($res['status']=='success'){
                $no =getPaperOrder('sole_dispatch_paper','',11,'no');
                $url = urlencode(admin_url('sole-dispatch/print?id='.$res['ids'].'&no='.$no)) ;
                return $form->redirect(
                    admin_url('sole-dispatch/create/'.$res['backid'].'?dialog=1&print='.$url),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }else{
            if($res['status']=='success'){
                return $form->redirect(
                    admin_url('sole-dispatch'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }
    }

    public function injectMoldStoreH(Request $request)
    {
        $is_keep = $request->is_keep;
        $is_dialog = $request->is_dialog;
        $plan_list_id= $request->plan_list_id;
        $res = $this->injectMoldSaveH($request);
        $form=new Form();
        if($is_dialog){
            if($res['status']=='success'){
                if($is_keep){
                    return $form->redirect(
                        admin_url('inject-mold-dispatch/create/'.$plan_list_id.'?dialog=1&keep=1'),
                        trans('admin.save_succeeded')
                    );
                }
                return $form->redirect(
                    admin_url('dispatch/'.$plan_list_id.'?dialog=1'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }else{
            if($res['status']=='success'){
                return $form->redirect(
                    admin_url('inject-mold-dispatch'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }
    }
    public function boxLabelStoreH(Request $request)
    {
        $keep = $request->is_keep;
        $is_dialog = $request->is_dialog;
        $plan_list_id= $request->plan_list_id;
        $res = $this->boxLabelSaveH($request);
        $form=new Form();
        if($is_dialog){
            if($res['status']=='success'){
                $no =getPaperOrder('box_label_dispatch_paper','',11,'no');
                $url = urlencode(admin_url('box-label-dispatch/print?id='.$res['ids'].'&no='.$no)) ;
                return $form->redirect(
                    admin_url('box-label-dispatch/create/'.$res['backid'].'?dialog=1&print='.$url),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }else{
            if($res['status']=='success'){
                return $form->redirect(
                    admin_url('box-label-dispatch'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }
    }
    /**
     * 鞋底派工保存
     * @param Request $request
     * @param null $id
     * @return \App\Models\WorkshopPurchase
     */
    protected function soleSaveH(Request $request, $id = null)
    {

        $data = $request->post();
        $model= new DispatchModel();
        $planListModel = PlanList::find($data['plan_list_id']);
        if ($planListModel->is_void=='1') { // 验证逻辑
            return [
                'message'=>'划单已作废,不可以操作',
                'status'=>'error',
            ];
        }
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            $status='1';
            $savedata=[
                'plan_list_id'=>$data['plan_list_id'],
                'plan_list_no'=>$data['plan_list_no'],
                'client_order_no'=>$data['client_order_no'],
                'client_name'=>$data['client_name'],
                'client_id'=>$data['client_id'],
                'client_model_id'=>$data['client_model_id'],
                'client_model'=>$data['client_model'],
                'carft_skill_id'=>$data['carft_skill_id'],
                'carft_skill_name'=>$data['carft_skill_name'],
                'sole_material_id'=>$data['sole_material_id'],
                'sole_material_name'=>$data['sole_material_name'],
                'company_model_id'=>$data['company_model_id'],
                'company_model'=>$data['company_model'],
                'craft_color_id'=>$data['craft_color_id'],
                'craft_color_name'=>$data['craft_color_name'],
                'product_category_id'=>$data['product_category_id'],
                'product_category_name'=>$data['product_category_name'],
                'type'=>$data['type'],
                'process_workshop'=>$data['type'],
                'process_department'=>$data['type'],
                'inject_mold_ask'=>$data['inject_mold_ask'],
                'plan_remark'=>$data['plan_remark'],
                'dispatch_user_id'=>$data['dispatch_user_id'],
                'dispatch_user_name'=>$data['dispatch_user_name'],
                'dispatch_no'=>getOrderNo('dispatches','P_XD',12,'dispatch_no'),
                'status'=>$status,
            ];
            if(empty($id)){
                //鞋底派工添加
                $sole_dispatch_info =  $model->create($savedata);
                $all_num  =0;
                foreach($data['spec'] as $kk=>$vv){
                    //检测数量
                    if($vv['num']<=0){
                        continue;
                    }
                    $planListDetail = PlanListDetail::where('id',$vv['id'])->first();
                    $detail_status='1';
                    if(($planListDetail->num-$planListDetail->sole_dispatch_num)<$vv['num']){
                        DB::rollback();
                        return [
                            'message' => '派单数量错误，刷新重试',
                            'status' => 'error',
                        ];
                    }
                    $all_num+=$vv['num'];

                    $insertData = [
                        'dispatch_id'=>$sole_dispatch_info->id,
                        'plan_list_id'=>$data['plan_list_id'],
                        'plan_list_detail_id'=>$vv['id'],
                        'spec'=>$vv['spec'],
                        'type'=>$vv['type'],
                        'num'=>$vv['num'],
                        'status'=>$detail_status,
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                    $this->changePlanListDetail([
                        'plan_list_detail_id'=>$vv['id'],
                        'num'=>$vv['num'],
                    ]);
                    //鞋底派工详情数据
                    $ids[] = DispatchDetail::insertGetId($insertData);
                }
                //鞋底派工数量
                $sole_dispatch_info->all_num  = $all_num;
                $dispatch_num = PlanListDetail::where('plan_list_id',$data['plan_list_id'])->sum('sole_dispatch_num');
                if($planListModel->spec_num ==$dispatch_num){
                    DispatchModel::where('plan_list_id',$data['plan_list_id'])->update([
                        'status'=>'2'
                    ]);
                    $status='2';
                }
                $sole_dispatch_info->save();
                //修改计划单状态
                $planListModel->status = '1';
                $planListModel->process = 'sole';
                $planListModel->sole_status =  $status;
                $planListModel->save();

                $backid = $planListModel->id;
            }
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'backid'=>$backid,
                'ids'=>implode(',',$ids),
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }

    protected function injectMoldSaveH(Request $request, $id = null)
    {

        $data = $request->post();
        $model= new DispatchModel();
        $planListModel = PlanList::find($data['plan_list_id']);
        if ($planListModel->is_void=='1') { // 验证逻辑
            return [
                'message'=>'划单已作废,不可以操作',
                'status'=>'error',
            ];
        }
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            $status='1';
            if(empty($id)){

                $all_num  =0;
                foreach($data['spec']['num'] as $kk=>$vv){
                    if($vv<=0){
                        continue;
                    }
                    //箱标派工添加
                    $savedata=[
                        'plan_list_id'=>$data['plan_list_id'],
                        'plan_list_no'=>$data['plan_list_no'],
                        'client_order_no'=>$data['client_order_no'],
                        'client_name'=>$data['client_name'],
                        'client_id'=>$data['client_id'],
                        'client_model_id'=>$data['client_model_id'],
                        'client_model'=>$data['client_model'],
                        'carft_skill_id'=>$data['carft_skill_id'],
                        'carft_skill_name'=>$data['carft_skill_name'],
                        'sole_material_id'=>$data['sole_material_id'],
                        'sole_material_name'=>$data['sole_material_name'],
                        'company_model_id'=>$data['company_model_id'],
                        'company_model'=>$data['company_model'],
                        'craft_color_id'=>$data['craft_color_id'],
                        'craft_color_name'=>$data['craft_color_name'],
                        'product_category_id'=>$data['product_category_id'],
                        'product_category_name'=>$data['product_category_name'],
                        'type'=>$data['type'],
                        'process_workshop'=>$data['type'],
                        'process_department'=>$data['type'],
                        'inject_mold_ask'=>$data['inject_mold_ask'],
                        'plan_remark'=>$data['plan_remark'],
                        'all_num'=>$vv,
                        'dispatch_user_id'=>$data['dispatch_user_id'],
                        'dispatch_user_name'=>$data['dispatch_user_name'],
                        'created_at'=>$now,
                        'updated_at'=>$now,
                        'dispatch_no'=>getOrderNo('dispatches','P_ZS',12,'dispatch_no'),
                        'status'=>$status,
                    ];
                    $inject_mold_dispatch_id =  $model->insertGetId($savedata);
                    $insertData = [
                        'dispatch_id'=>$inject_mold_dispatch_id,
                        'plan_list_id'=>$data['plan_list_id'],
                        'plan_list_detail_id'=>0,
                        'spec'=>StandardDetail::find($data['spec']['spec'][$kk])->standard_detail_name,
                        'spec_id'=>$data['spec']['spec'][$kk],
                        'type'=>'none',
                        'num'=>$vv,
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                    $all_num+=$vv;
                    $ids[] =  DispatchDetail::insertGetId($insertData);
                }
                //修改计划单状态
                $planListModel->status = '2';
                $planListModel->process = 'inject_mold';
                $planListModel->inject_mold_status =  $status;
                $planListModel->save();

                $backid = $planListModel->id;
            }
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'backid'=>$backid,
                'ids'=>implode(',',$ids),
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }
    /**
     * 箱标派工保存
     * @param Request $request
     * @param null $id
     * @return \App\Models\WorkshopPurchase
     */
    protected function boxLabelSaveH(Request $request, $id = null)
    {

        $data = $request->post();
        $model= new DispatchModel();
        $planListModel = PlanList::find($data['plan_list_id']);
        if ($planListModel->is_void=='1') { // 验证逻辑
            return [
                'message'=>'划单已作废,不可以操作',
                'status'=>'error',
            ];
        }
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            $status='1';
            $savedata=[
                'plan_list_id'=>$data['plan_list_id'],
                'plan_list_no'=>$data['plan_list_no'],
                'client_order_no'=>$data['client_order_no'],
                'client_name'=>$data['client_name'],
                'client_id'=>$data['client_id'],
                'client_model_id'=>$data['client_model_id'],
                'client_model'=>$data['client_model'],
                'carft_skill_id'=>$data['carft_skill_id'],
                'carft_skill_name'=>$data['carft_skill_name'],
                'sole_material_id'=>$data['sole_material_id'],
                'sole_material_name'=>$data['sole_material_name'],
                'company_model_id'=>$data['company_model_id'],
                'company_model'=>$data['company_model'],
                'craft_color_id'=>$data['craft_color_id'],
                'craft_color_name'=>$data['craft_color_name'],
                'product_category_id'=>$data['product_category_id'],
                'product_category_name'=>$data['product_category_name'],
                'type'=>$data['type'],
                'process_workshop'=>$data['type'],
                'process_department'=>$data['type'],
                'inject_mold_ask'=>$data['inject_mold_ask'],
                'plan_remark'=>$data['plan_remark'],
                'dispatch_user_id'=>$data['dispatch_user_id'],
                'dispatch_user_name'=>$data['dispatch_user_name'],
                'dispatch_no'=>getOrderNo('dispatches','P_XB',12,'dispatch_no'),
                'status'=>$status,
            ];
            if(empty($id)){
                //箱标派工添加
                $box_label_dispatch_info =  $model->create($savedata);
                $all_num  =0;
                foreach($data['spec'] as $kk=>$vv){
                    //检测数量
                    if($vv['num']<=0){
                        continue;
                    }
                    $planListDetail = PlanListDetail::where('id',$vv['id'])->first();
                    $detail_status='1';
                    if(($planListDetail->num-$planListDetail->box_label_dispatch_num)<$vv['num']){
                        DB::rollback();
                        return [
                            'message' => '派单数量错误，刷新重试',
                            'status' => 'error',
                        ];
                    }
                    $all_num+=$vv['num'];

                    $insertData = [
                        'dispatch_id'=>$box_label_dispatch_info->id,
                        'plan_list_id'=>$data['plan_list_id'],
                        'plan_list_detail_id'=>$vv['id'],
                        'spec'=>$vv['spec'],
                        'type'=>$vv['type'],
                        'num'=>$vv['num'],
                        'status'=>$detail_status,
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                    $this->changeBoxLabelPlanListDetail([
                        'plan_list_detail_id'=>$vv['id'],
                        'num'=>$vv['num'],
                    ]);
                    //鞋底派工详情数据
                    $ids[] = DispatchDetail::insertGetId($insertData);
                }
                //鞋底派工数量
                $box_label_dispatch_info->all_num  = $all_num;
                $dispatch_num = PlanListDetail::where('plan_list_id',$data['plan_list_id'])
                    ->sum('box_label_dispatch_num');
                if($planListModel->spec_num ==$dispatch_num){
                    DispatchModel::where('plan_list_id',$data['plan_list_id'])->update([
                        'status'=>'2'
                    ]);
                    $status='2';
                }
                $box_label_dispatch_info->save();
                //修改计划单状态
                $planListModel->status = '1';
                $planListModel->process = 'box_label';
                $planListModel->box_label_status =  $status;
                $planListModel->save();

                $backid = $planListModel->id;
            }
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'backid'=>$backid,
                'ids'=>implode(',',$ids),
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
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
        $checknum = $plan_list_detail->num-$plan_list_detail->sole_dispatch_num;
        if($checknum<$arr['num']){
            return [
                'status'=>'error',
                'message'=>'数量不足'
            ];
        }elseif($checknum==$arr['num']){
            $plan_list_detail->sole_dispatch_num += $arr['num'];
            $plan_list_detail->sole_dispatch_complete=1;

        }else{
            $plan_list_detail->sole_dispatch_num += $arr['num'];
        }
        $plan_list_detail->save();
        return [
            'status'=>'success'
        ];
    }

    /**
     * 箱标派工修改
     * @param $arr
     * @return string[]
     */
    private function changeBoxLabelPlanListDetail($arr){
        $plan_list_detail = PlanListDetail::find($arr['plan_list_detail_id']);
        $checknum = $plan_list_detail->num-$plan_list_detail->box_label_dispatch_num;
        if($checknum<$arr['num']){
            return [
                'status'=>'error',
                'message'=>'数量不足'
            ];
        }elseif($checknum==$arr['num']){
            $plan_list_detail->box_label_dispatch_num += $arr['num'];
            $plan_list_detail->box_label_dispatch_complete=1;

        }else{
            $plan_list_detail->box_label_dispatch_num += $arr['num'];
        }
        $plan_list_detail->save();
        return [
            'status'=>'success'
        ];
    }
}
