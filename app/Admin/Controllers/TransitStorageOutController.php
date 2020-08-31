<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\TransitStorageOutVoid;
use App\Models\Department;
use App\Models\Dispatch;
use App\Models\DispatchDetail;
use App\Models\Personnel;
use App\Models\PlanList;
use App\Models\TransitStorage;
use App\Models\TransitStorageIn;
use App\Models\TransitStorageLog;
use App\Models\TransitStorageOut;
use App\Models\TransitStorageOutDetail;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Faker\Factory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class TransitStorageOutController extends AdminController
{
    protected function grid()
    {
        $plan_list_model = new PlanList();
        return Grid::make(new TransitStorageOut(), function (Grid $grid) use($plan_list_model){
            $grid->model()->with('plan_list')->orderBy('created_at','desc');
            $grid->out_date;
            $grid->plan_list_no;
            $grid->dispatch_no;
            $grid->column('client_name','客户名称')->display(function (){
                return $this->plan_list['client_name'];
            });
            $grid->company_model;
            $grid->column('client_model','客户型号')->display(function (){
                return $this->plan_list['client_model'];
            });
            $grid->column('craft_color_name','工艺颜色')->display(function (){
                return $this->plan_list['craft_color_name'];
            });
            $grid->column('sole_material_name','材料用料')->display(function () use($plan_list_model){
                $name = $plan_list_model->getSoleMaterialName($this->plan_list['client_sole_information_id']);
                return $name;
            });
            $grid->column('product_category_name','产品类型')->display(function (){
                return $this->plan_list['product_category_name'];
            });
            $grid->column('num')->display(function (){
                return is_float_number($this->num);
            });
            $all = TransitStorageOut::sum('num');
            $grid->header(function ($query) use ($all){
                return '
                    <label>出库合计:'.$all.'</label>';
            });
            $grid->column('void','状态【去作废】')->action(TransitStorageOutVoid::class);
            $grid->disableActions();
//            $grid->disableDeleteButton();
//            $grid->disableEditButton();
//            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('plan_list_no','计划单号')->width(2);
                $filter->equal('storage_type','库存类型')->select(config('plan.transit_storage_type'))->width(2);
                $filter->like('dispatch_no')->width(2);
                $filter->equal('style','入库方式')->select(config('plan.transit_storage_in_style'))->width(2);
                $filter->like('company_model')->width(2);
                $filter->like('personnel_name')->width(2);
                $filter->between('storage_in_date')->date()->width(4);
            });
            //导出
            $titles = [
                'storage_in_date' => '入库时间', 'dispatch_no' => '派工单号',
                'company_model' => '鞋底型号', 'spec' => '明细规格',
                'storage_type' => '库存类型',
                'all_num' => '入库数量',
            ];
            $filename = 'ZZCRK_'.date('YmdHis');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                foreach ($rows as $index => &$row) {
                    $row['storage_type'] = config('plan.transit_storage_type')[$row['storage_type']];
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * 添加页面
     * @param Content $content
     * @return mixed
     */
    public function createH($id=0,Content $content)
    {
        return $content
            ->header('添加')
            ->description($this->title)
            ->body($this->storageOutForm('create',$id));
    }
    /**
     * 修改页面
     * @param $id
     * @param Content $content
     * @return mixed
     */
    public function editH($id, Content $content)
    {
        return $content
            ->header('编辑')
            ->description($this->title)
            ->body($this->storageOutForm('update',$id));
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function storageOutForm($type,$id=0,$is_dialog=0,$is_close=0,$is_print=0)
    {
        $dispatchInfo = Dispatch::find($id);
        $plan_list_detail_info = DispatchDetail::where('dispatch_id',$id)->get([
            'id','spec','type','num','storage_out','plan_list_detail_id'
        ])->toArray();
        $usedata=[
            'dispatch_id'=>$dispatchInfo->id,
            'dispatch_no'=>$dispatchInfo->dispatch_no,
            'plan_list_id'=>$dispatchInfo->plan_list_id,
            'plan_list_no'=>$dispatchInfo->plan_list_no,
            'type'=>'zcpck',
            'style'=>'in',
            'storage_type'=>'sole',
            'out_date'=>Carbon::now(),
            'log_user_id'=>Admin::user()->id,
            'log_user_name'=>Admin::user()->name,
            'department_id'=>0,
            'department_name'=>'',
            'personnel_id'=>'',
            'personnel_name'=>'',
            'company_model'=>$dispatchInfo->company_model,
            'company_model_id'=>$dispatchInfo->company_model_id,
        ];

        return Form::make(new TransitStorageOut(), function (Form $form) use($usedata,$plan_list_detail_info,$is_dialog,$is_close,$is_print){
            $form->column(6, function (Form $form) use($usedata,$is_dialog){
                $form->hidden('is_dialog')->default($is_dialog);
                $form->text('dispatch_no')->default($usedata['dispatch_no']);
                $form->hidden('dispatch_id')->default($usedata['dispatch_id']);
                $form->radio('style')->options(config('plan.transit_storage_out_style'))
                    ->default($usedata['style']);
                $form->text('company_model')->readonly()->default($usedata['company_model']);
                $form->hidden('company_model_id')->default($usedata['company_model_id']);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->select('type')->options(config('plan.transit_storage_out_type'))->default($usedata['type']);
                $form->select('storage_type')->options(config('plan.transit_storage_type'))
                    ->default($usedata['storage_type']);
                $form->datetime('out_date')->format('YYYY-MM-DD HH:mm:ss')->default($usedata['out_date']);

            });
            $form->column(12, function (Form $form) use($usedata,$plan_list_detail_info){
                $form->html(function () use($plan_list_detail_info,$usedata){
                    $count = count($plan_list_detail_info);
                    $arr=[];
                    $specarr = [];

                    foreach ($plan_list_detail_info as $kk=>$vv){
                        $specarr[$kk]['id']=$vv['id'];
                        $specarr[$kk]['spec']=$vv['spec'];
                        if($vv['num']-$vv['storage_out']>0){
                            $arr[$vv['id']]['spec'] = $vv['spec'];
                            $arr[$vv['id']]['num'] = $vv['num']-$vv['storage_out'];
                            $arr[$vv['id']]['allnum'] = $vv['num'];
                            $arr[$vv['id']]['type'] = $vv['type'];
                            $arr[$vv['id']]['dispatch_detail_id'] = $vv['id'];
                            $arr[$vv['id']]['plan_list_detail_id'] = $vv['plan_list_detail_id'];
                        }
                    }
                    $showid = $usedata['dispatch_id'];
                    $dataarr = json_encode($arr);
                    $specarr = json_encode($specarr);
                    $types = json_encode(config('plan.type_text'));
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
#plan_order_num_{$showid}{background: #fff;border-radius: 3px;border:1px solid #d9d9d9;outline:none;}
</style>
<div class="spec-top " style="">
    <span class="spec-title">请选择规格/尺码数量</span>
    <input name="" id="plan_order_num_{$showid}" class=" col-md-1 select" value="{$count}" readonly/>
</div>
<div class="spec-body" id="spec-body">
    <table id="spec_table_{$showid}" class="table">

    </table>
</div>
<hr>
<script >
$(function() {
  var specarr = {$specarr};
  var types = {$types};
  var arrhtml = '';
  var dataarr = {$dataarr};
  $.each(dataarr,function(index,data) {
       var optionshtml = '';
      $.each(specarr,function(index,data2) {
         optionshtml+='<option value="'+data2.spec+'" data-id="'+data2.id+'" '+(data2.spec == data.spec?'selected':'')+'>'+data2.spec+'码</option>'
     })
     arrhtml+=' <tr>'+
      '<td><div><span>型号规格</span>' +
      '<select name="spec['+data.dispatch_detail_id+'][spec]">' +optionshtml+
        '</select>'+
        '&nbsp;&nbsp;' +
         '<select name="spec['+data.dispatch_detail_id+'][type]"><option value="'+data.type+'">'+types[data.type]+'</option><select/>' +
         '<input value="'+data.dispatch_detail_id+'" name="spec['+data.dispatch_detail_id+'][id]" type="hidden">' +
         '<input value="'+data.spec+'" name="spec['+data.dispatch_detail_id+'][spec]" type="hidden">' +
          '<input value="'+data.plan_list_detail_id+'" name="spec['+data.dispatch_detail_id+'][plan_order_detail_id]" type="hidden"></div>' +
       '<div class="text-danger" style="line-height:30px">型号规格【'+data.spec+'】  派工数：'+data.allnum+'（'+types[data.type]+'）  未出库数量：'+data.num+'（'+types[data.type]+'）</div></td>'+
       '<td><span>出库数量:</span><input name="spec['+data.dispatch_detail_id+'][num]" value="'+data.num+'"  class="input-h1"></td>'+
        '</tr>';
  })
   $('#spec_table_{$showid}').append(arrhtml)
})
</script>
EHTML;
                },' ')->oneline(true)->width(11,1);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->select('department_id')->options('api/department')
                    ->load('personnel_id','api/department/to/personnel');
                $form->text('log_user_name')->default($usedata['log_user_name'])->required()->readonly();
                $form->hidden('log_user_id')->default($usedata['log_user_id']);
            });
            $form->column(6, function (Form $form) use($usedata){
                $form->select('personnel_id');
            });
            $form->column(12, function (Form $form) use($usedata){
                $form->textarea('remark')->oneline(true)->width(10,1);
                $form->hidden('_token')->default(csrf_token());
            });

            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->submitted(function (Form $form) {
                $form->deleteInput('_token');
            });
        });
    }
    public function storeH(Request $request)
    {
        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('transit-storage-out'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }

    }
    public function updateH($id,Request $request)
    {
        $res = $this->saveH($request, $id);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('transit-storage-out'),
                trans('admin.update_succeeded')
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
        $storage_out_model = new TransitStorageOut();
        $storage_out_detail_model = new TransitStorageOutDetail();
        $storage_model = new TransitStorage();
        $storage_logModel = new TransitStorageLog();
        $dispatch_model = new Dispatch();
        $dispatch_detail_model = new DispatchDetail();
        try{
            $dispatch_info = $dispatch_model->find($data['dispatch_id']);
            $data['personnel_name']= $data['personnel_id']>0?Personnel::find($data['personnel_id'])->name:'';
            $data['department_name']= $data['department_id']>0?Department::find($data['department_id'])->department_name:'';
            $plan_list_info = PlanList::find($dispatch_info->plan_list_id);
            $wait_dispatch_all_num  =DispatchDetail::where('dispatch_id',$data['dispatch_id'])
                ->where('status','neq','0')
                ->sum('storage_out');
            $now = Carbon::now();
            $transit_storage_out_data = [
                'plan_list_id'=>$dispatch_info->plan_list_id,
                'plan_list_no'=>$dispatch_info->plan_list_no,
                'dispatch_id'=>$dispatch_info->id,
                'dispatch_no'=>$dispatch_info->dispatch_no,
                'type'=>$data['type'],
                'style'=>$data['style'],
                'storage_type'=>$data['storage_type'],
                'company_model'=>$dispatch_info->company_model,
                'company_model_id'=>$dispatch_info->company_model_id,
                'out_date'=>$data['out_date'],
                'log_user_id'=>Admin::user()->id,
                'log_user_name'=>Admin::user()->name,
                'department_id'=> $data['department_id'],
                'department_name'=> $data['department_name'],
                'personnel_id'=> $data['personnel_id'],
                'personnel_name'=> $data['personnel_name'],
                'remark'=> $data['remark'],
                'status'=> '1',
                'is_void'=> '0',
                'created_at'=>$now,
                'updated_at'=>$now,
            ];

            $transit_storage_out_info_id = $storage_out_model->insertGetId($transit_storage_out_data);
            $transit_storage_out_info =$storage_out_model->find($transit_storage_out_info_id);
            $allunm= 0;
            if(isset($data['spec'])&&$data['spec']){
                foreach ($data['spec'] as $k=>$v){
                    if(!($v['num']>0)){
                        continue;
                    }
                    $allunm+=$v['num'];
                    $insertdata = [
                        'trandit_storage_out_id'=>$transit_storage_out_info->id,
                        'dispatch_detail_id'=>$v['id'],
                        'spec'=>$v['spec'],
                        'type'=>$v['type'],
                        'num'=>$v['num'],
                        'is_print'=>'0',
                        'status'=>'1',
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                    $ids[] = $storage_out_detail_model->insertGetId($insertdata);
                    //鞋底派工详情中完成数增加
                    $dispatch_detail_model->where('id',$v['id'])->increment('storage_out',$v['num']);
                }
            }

            //出库信息变化
            $transit_storage_out_info->num = $allunm;
            $transit_storage_out_info->status = $wait_dispatch_all_num==$allunm?'2':'1';
            $transit_storage_out_info->save();
            //鞋底派工变化
            $nowallnum = $dispatch_model->storage_out+$allunm;

            if($dispatch_info->storage_out_status=='0'&&$dispatch_info->all_num!=$nowallnum){
                $dispatch_model->where('id',$dispatch_info->id)->update([
                    'storage_out_status'=>'1',
                ]);
            }elseif($dispatch_info->all_num==$nowallnum){
                $dispatch_model->where('id',$dispatch_info->id)->update([
                    'storage_out_status'=>'2',
                ]);
            }
            //planorder 对应的出库单的所有信息
            $plan_list_num = TransitStorageOut::where('plan_list_id',$plan_list_info->id)
                ->sum('num');
            $status = $plan_list_num==$plan_list_info->spec_num?'2':'1';
            $plan_list_info->storage_out_status=$status;
            $plan_list_info->storage_out_num=$plan_list_info->storage_out_num+$allunm;
            $plan_list_info->save();

            //仓库数量变化
            /*$storageInfo = $storage_model->where('company_model',$data['company_model'])
                ->where('spec','like',"%".$data['spec']."%")
                ->where('type',$data['storage_type'])
                ->first();

            if($storageInfo){
                $storageInfo->out_num=$storageInfo->out_num+$data['num'];
                $storageInfo->check_at = Carbon::now();
                $storageInfo->save();
            }else{
                DB::rollback();
                return [
                    'message' => '暂无该型号入库，没得出库',
                    'status' => 'error',
                ];
            }
            //记录
            $storage_logModel->create([
                'transit_storage_id'=>$storageInfo->id,
                'log_user_id'=>Admin::user()->id,
                'log_user_name'=>Admin::user()->name,
                'company_model_id'=>$data['company_model_id'],
                'company_model'=>$data['company_model'],
                'spec'=>$storageInfo->spec,
                'spec_id'=>$storageInfo->spec_id,
                'from'=>'2',
                'out_num'=>$data['num'],
                'in_num'=>0,
                'type'=>$data['storage_type'],
                'store'=>-$storageInfo['out_num'],
            ]);*/

            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
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
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $title = "中转仓出库查看";
        $data = TransitStorageOut::find($id);
        $is_dialog = request()->dialog;
        $length=6;
        $info=[
            [
                'label'=>'派工单号',
                'value'=>$data->plan_list_no,
                'length'=>$length
            ],
            [
                'label'=>'出库类型',
                'value'=>config('plan.transit_storage_out_type')[$data->type],
                'length'=>$length
            ],
            [
                'label'=>'出库方式',
                'value'=>config('plan.transit_storage_out_style')[$data->style],
                'length'=>$length
            ],
            [
                'label'=>'库存类型',
                'value'=>config('plan.transit_storage_type')[$data->storage_type],
                'length'=>$length
            ],
            [
                'label'=>'雷力型号',
                'value'=>$data->company_model,
                'length'=>$length
            ],
            [
                'label'=>'出库时间',
                'value'=>$data->out_date,
                'length'=>$length
            ],
            [
                'label'=>'出库数量',
                'value'=>$data->num,
                'length'=>$length
            ],

            [
                'label'=>'领用部门',
                'value'=>$data->department_name,
                'length'=>$length
            ],
            [
                'label'=>'记录人',
                'value'=>$data->log_user_name,
                'length'=>$length
            ],
            [
                'label'=>'领用员工',
                'value'=>$data->personnel_name,
                'length'=>$length
            ],
            [
                'label'=>'描述',
                'value'=>$data->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('zhongzhuan-chuku-list');

        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

}
