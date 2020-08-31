<?php

namespace App\Admin\Controllers;

use App\Models\InjectMoldPrice;
use App\Models\Personnel;
use App\Models\PlanList;
use App\Models\TransitStorage;
use App\Models\TransitStorageIn;
use App\Models\DispatchDetail;
use App\Models\TransitStorageLog;
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

class TransitStorageInController extends AdminController
{

    /**
     * 添加页面
     * @param Content $content
     * @return mixed
     */
    public function createH($id=0,Content $content)
    {
        $is_dialog = request()->is_dialog;
        return $content
            ->header('添加')
            ->description($this->title)
            ->body($this->storageInForm('create',$id,$is_dialog));
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
            ->body($this->storageInForm('update',$id));
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function storageInForm($type,$id=null,$is_dialog=0)
    {
        //计量单位
        if($type=='create'){
            $usedata=[
                'type'=>'inject_mold_inner',
                'style'=>'in',
                'storage_type'=>'sole',
                'count_type'=>'inject_mold',
                'personnel_id'=>0,
                'personnel_name'=>'',
                'inject_mold_price_id'=>0,
                'inject_mold_price'=>0,
                'storage_in_date'=>Carbon::now(),
                'log_user_id'=>Admin::user()->id,
                'log_user_name'=>Admin::user()->name,
                'remark'=>'',
            ];
            if($id>0){
                $dsipatch_detail_info = DispatchDetail::with('dispatch_info')->find($id);
                $usedata['plan_list_no']=$dsipatch_detail_info->dispatch_info->plan_list_no;
                $usedata['plan_list_id']=$dsipatch_detail_info->dispatch_info->plan_list_id;
                $usedata['dispatch_id']=$dsipatch_detail_info->dispatch_id;
                $usedata['dispatch_no']=$dsipatch_detail_info->dispatch_info->dispatch_no;
                $usedata['dispatch_detail_id']=$id;
                $usedata['company_model_id']=$dsipatch_detail_info->dispatch_info->company_model_id;
                $usedata['company_model']=$dsipatch_detail_info->dispatch_info->company_model;
                $usedata['spec']=$dsipatch_detail_info->spec;
                $usedata['spec_id']=$dsipatch_detail_info->spec_id;
                $usedata['all_num']=$dsipatch_detail_info->num-$dsipatch_detail_info->storage_in;
            }else{
                $usedata['plan_list_no']='';
                $usedata['plan_list_id']=0;
                $usedata['dispatch_id']=0;
                $usedata['dispatch_no']='';
                $usedata['dispatch_detail_id']=$id;
                $usedata['company_model_id']=0;
                $usedata['company_model']='';
                $usedata['spec']='';
                $usedata['spec_id']=0;
                $usedata['all_num']=0;
            }
        }
        else{
            $storage_in_data = TransitStorageIn::find($id);
            $usedata=[
                'plan_list_no'=>$storage_in_data->plan_list_no,
                'plan_list_id'=>$storage_in_data->plan_list_id,
                'dispatch_no'=>$storage_in_data->dispatch_no,
                'dispatch_id'=>$storage_in_data->dispatch_id,
                'dispatch_detail_id'=>$storage_in_data->dispatch_detail_id,
                'type'=>$storage_in_data->type,
                'style'=>$storage_in_data->style,
                'storage_type'=>$storage_in_data->storage_type,
                'count_type'=>$storage_in_data->count_type,
                'company_model'=>$storage_in_data->company_model,
                'company_model_id'=>$storage_in_data->company_model_id,
                'spec'=>$storage_in_data->spec,
                'spec_id'=>$storage_in_data->spec_id,
                'storage_in_date'=>$storage_in_data->storage_in_date,
                'personnel_id'=>$storage_in_data->personnel_id,
                'personnel_name'=>$storage_in_data->personnel_name,
                'inject_mold_price'=>$storage_in_data->inject_mold_price,
                'inject_mold_price_id'=>$storage_in_data->inject_mold_price_id,
                'log_user_id'=>$storage_in_data->log_user_id,
                'log_user_name'=>$storage_in_data->log_user_name,
                'all_num'=>$storage_in_data->all_num,
                'remark'=>$storage_in_data->remark,
            ];
        }
        return Form::make(new TransitStorageIn(), function (Form $form) use($usedata,$is_dialog){
            $form->column(6, function (Form $form) use($usedata){
                if($usedata['plan_list_no']){
                    $form->text('plan_list_no')->default($usedata['plan_list_no'])->readOnly();
                }else{
                    $form->selectResource('plan_list_no')
                        ->path('dialog/data/zhusupaigong')// 设置表格页面链接
                        ->required();
                }
                $form->radio('style')->options(config('plan.transit_storage_in_style'))
                    ->default($usedata['style']);
                $form->text('company_model')->readonly()->default($usedata['company_model']);
                $form->hidden('company_model_id')->default($usedata['company_model_id']);
                $form->text('all_num')->default(is_float_number($usedata['all_num']));
                $form->selectResource('personnel_id')
                    ->path('dialog/personnel')// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Personnel::findOrFail($v)->pluck('name', 'id');
                    })->required()->value($usedata['personnel_id']);
                $form->select('count_type')->options(config('plan.transit_storage_count_type'))->default($usedata['count_type']);

            });
            $form->column(6, function (Form $form)use($usedata,$is_dialog){
                $form->select('type')->options(config('plan.transit_storage_in_type'))->default($usedata['type']);
                $form->select('storage_type')->options(config('plan.transit_storage_type'))->default($usedata['storage_type']);
                $form->text('spec')->readonly()->default($usedata['spec']);
                $form->hidden('spec_id')->default($usedata['spec_id']);
                $form->datetime('storage_in_date')->format('YYYY-MM-DD HH:mm:ss')->default($usedata['storage_in_date']);
                $form->text('log_user_name')->default($usedata['log_user_name'])->readonly();
                $form->hidden('log_user_id')->default($usedata['log_user_id']);
                $form->selectResource('inject_mold_price_id')
                    ->path('dialog/inject-mold-price')// 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return InjectMoldPrice::findOrFail($v)->pluck('price', 'id');
                    })->required()
                    ->value($usedata['inject_mold_price_id']);
                $form->hidden('inject_mold_price');
            });
            $form->column(12, function (Form $form)use($usedata,$is_dialog){
                $form->textarea('remark')->oneline(true)->width(10,1)
                    ->default($usedata['remark']);
                $form->hidden('plan_list_id')->default($usedata['plan_list_id']);
                $form->hidden('dispatch_id')->default($usedata['dispatch_id']);
                $form->hidden('dispatch_no')->default($usedata['dispatch_no']);
                $form->hidden('dispatch_detail_id')->default($usedata['dispatch_detail_id']);
                $form->hidden('is_dialog')->default($is_dialog);
                $form->hidden('_token')->default(csrf_token());
                $form->html(function (){
                    return '<script>function selectSourceChange() {
  return false;
}</script>';
                });
            });
            $form->submitted(function (Form $form) {
                $form->deleteInput('_token');
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
            $form->disableListButton();
            $form->disableDeleteButton();
        });
    }
    public function storeH(Request $request)
    {
        $res = $this->saveH($request);
        $form=new Form();
        $is_dialog = $request->is_dialog;
        if($is_dialog){
            if($res['status']=='success'){
                return $form->redirect(
                    admin_url('transit-storage-in/create/'.$res['backid'].'?dialog=1'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }else{
            if($res['status']=='success'){
                return $form->redirect(
                    admin_url('transit-storage-in'),
                    trans('admin.save_succeeded')
                );
            }else{
                return $form->error($res['message']);
            }
        }

    }
    public function updateH($id,Request $request)
    {
        $res = $this->saveH($request, $id);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('transit-storage-in'),
                trans('admin.update_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
    protected function saveH (Request $request, $id = null)
    {
        $data = $request->only(['dispatch_id','dispatch_detail_id','dispatch_no','plan_list_id',
            'plan_list_no', 'type','style','storage_type','count_type','all_num',
            'log_user_id','log_user_name','company_model_id','company_model','spec_id','spec',
            'personnel_id','personnel_name','remark','storage_in_date','inject_mold_price_id']);

        DB::beginTransaction(); //开启事务
        //添加
        $storageInModel = new TransitStorageIn();
        $storageModel = new TransitStorage();
        $storageLogModel = new TransitStorageLog();
        $injectMoldPriceModel = new InjectMoldPrice();

        $inject_mold_price = $injectMoldPriceModel->find($data['inject_mold_price_id'])->price;
        $data['inject_mold_price']=$inject_mold_price;
        $data['personnel_name']= Personnel::find($data['personnel_id'])->name;

        try{
            $dispatch_detail_info = DispatchDetail::with('dispatch_info')
                ->find($data['dispatch_detail_id']);

            $data['plan_list_id'] = $dispatch_detail_info->dispatch_info->plan_list_id;
            $data['plan_list_no'] = $dispatch_detail_info->dispatch_info->plan_list_no;

            if(empty($id)){
                //数量不能超过
                $wait_storage_in_num = $dispatch_detail_info->num-$dispatch_detail_info->storage_in;
                if($wait_storage_in_num<$data['all_num']){
                    return [
                        'message' => '数量超过限制',
                        'status' => 'error',
                    ];
                }
                $storageInModel->create($data);
                $dispatch_detail_info->storage_in = $dispatch_detail_info->storage_in+$data['all_num'];
                $dispatch_detail_info->save();
                $changenum=$data['all_num'];
            }else{
                //数量不能超过
                $oldstorageIn = $storageInModel->where('id',$id)->first();
                if(($dispatch_detail_info->num-$oldstorageIn->all_num)<$data['all_num']){
                    return [
                        'message' => '数量超过限制',
                        'status' => 'error',
                    ];
                }
                $changenum = $data['all_num']-$oldstorageIn->all_num ;

                $dispatch_detail_info->storage_in = $dispatch_detail_info->storage_in+$changenum;
                $dispatch_detail_info->save();

                $storageInModel->where('id',$id)->update($data);
            }
            //仓库数量变化
            $storageInfo = $storageModel->where('company_model',$data['company_model'])
                ->where('company_model_id',$data['company_model_id'])
                ->where('spec',$data['spec'])
                ->where('type',$data['storage_type'])
                ->first();
            if($storageInfo){
                $storageInfo->in_num=$storageInfo->in_num+$changenum;
                $storageInfo->check_at = Carbon::now();
                $storageInfo->save();
            }else{
                $storageInfo = $storageModel->create([
                    'company_model'=>$data['company_model'],
                    'company_model_id'=>$data['company_model_id'],
                    'spec'=>$data['spec'],
                    'spec_id'=>$data['spec_id'],
                    'type'=>$data['storage_type'],
                    'in_num'=>$data['all_num'],
                    'check_at'=>Carbon::now(),
                    'price'=>$data['inject_mold_price'],
                ]);
            }
            //记录

            $storageLogModel->create([
                'transit_storage_id'=>$storageInfo->id,
                'log_user_id'=>Admin::user()->id,
                'log_user_name'=>Admin::user()->name,
                'company_model'=>$data['company_model'],
                'company_model_id'=>$data['company_model_id'],
                'spec'=>$data['spec'],
                'spec_id'=>$data['spec_id'],
                'from'=>'1',
                'in_num'=>$changenum,
                'out_num'=>0,
                'type'=>$data['storage_type'],
                'storage'=>$storageInfo->in_num,
            ]);
            //修改注塑派工
            if($dispatch_detail_info->num == $dispatch_detail_info->storage_in){
                $dispatch_detail_info->storage_in_status='2';
                $dispatch_detail_info->status='2';
            }else{
                $dispatch_detail_info->storage_in_status='1';
                $dispatch_detail_info->status='1';
            }
            $dispatch_detail_info->save();

            $planListInfo = PlanList::where('id',$dispatch_detail_info->plan_list_id)->first();
            $planListInfo->storage_in_status='1';
            $planListInfo->save();

            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'backId'=>$data['dispatch_detail_id']
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
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TransitStorageIn(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->storage_in_date;
            $grid->dispatch_no;
            $grid->company_model;
            $grid->spec;
            $grid->personnel_name;
            $grid->storage_type->using(config('plan.transit_storage_type'));
            $grid->style->using(config('plan.transit_storage_in_style'));
            $grid->column('all_num')->display(function (){
                return is_float_number($this->all_num);
            });
            $grid->column('inject_mold_price','工价')->display(function (){
                return $this->inject_mold_price;
            });
            $grid->column('all_price','加工费')->display(function (){
                return $this->all_num*$this->inject_mold_price;
            });

            $grid->column('operation','查看')
                ->dialog(function (){
                    return ['type'=>'url','url'=> admin_url('transit-storage-in/'.$this->id.'?dialog=1'),
                            'value'=>'<i class=" text-info feather icon-search grid-action-icon"></i>', 'width'=>'600px',
                            'height'=>'330px'];
                });
            $grid->column('edit','修改')
                ->display(function (){
                    Form::dialog('中转入库信息修改')
                        ->click('#transit_storage_in_change'.$this->id) // 绑定点击按钮
                        ->url(admin_url('transit-storage-in/'.$this->id.'/edit?dialog=1')) // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                        ->width('900px') // 指定弹窗宽度，可填写百分比，默认 720px
                        ->height('650px') // 指定弹窗高度，可填写百分比，默认 690px
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    $id= $this->id;
                    return '<i class=" text-info feather icon-edit grid-action-icon" id="transit_storage_in_change'.$id.'"></i>';
                });
            $all = TransitStorageIn::sum('all_num');
            $grid->header(function ($query) use ($all){
                return '

                        <a href="' . admin_url('transit-storage-in') . '" class="btn btn-sm btn-info" title="转到入库管理">
                           <span class="hidden-xs">&nbsp;&nbsp;转到入库管理&nbsp;&nbsp;</span>
                        </a>

                        <a href="' . admin_url('transit-storage-in') . '" class="btn btn-sm btn-info" title="打印入库单">
                           <span class="hidden-xs">&nbsp;&nbsp;打印入库单&nbsp;&nbsp;</span>
                        </a>

                    <label>入库合计:'.$all.'</label>';
            });
            $grid->disableActions();
//            $grid->disableDeleteButton();
//            $grid->disableEditButton();
//            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
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

    protected function detail($id)
    {
        $is_dialog = request()->dialog;
        $title = "中转仓入库查看";
        $data = TransitStorageIn::find($id);

        $length=6;
        $info=[
            [
                'label'=>'派工单号',
                'value'=>$data->dispatch_no,
                'length'=>$length
            ],
            [
                'label'=>'入库类型',
                'value'=>config('plan.transit_storage_in_type')[$data->type],
                'length'=>$length
            ],
            [
                'label'=>'入库方式',
                'value'=>config('plan.transit_storage_in_style')[$data->style],
                'length'=>$length
            ],
            [
                'label'=>'库存类型',
                'value'=>config('plan.transit_storage_type')[$data->storage_type],
                'length'=>$length
            ],
            [
                'label'=>'鞋跟型号',
                'value'=>$data->company_model,
                'length'=>$length
            ],
            [
                'label'=>'明细规格',
                'value'=>$data->spec,
                'length'=>$length
            ],
            [
                'label'=>'入库数量',
                'value'=>$data->all_num,
                'length'=>$length
            ],
            [
                'label'=>'入库时间',
                'value'=>$data->storage_in_date,
                'length'=>$length
            ],
            [
                'label'=>'员工加工',
                'value'=>$data->personnel_name,
                'length'=>$length
            ],
            [
                'label'=>'记录人',
                'value'=>$data->log_user_name,
                'length'=>$length
            ],
            [
                'label'=>'计件类型',
                'value'=>config('plan.transit_storage_count_type')[$data->count_type],
                'length'=>$length
            ],
            [
                'label'=>'工价',
                'value'=>$data->inject_mold_price.'元',
                'length'=>12
            ],
            [
                'label'=>'描述',
                'value'=>$data->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('transit-storage');

        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }
}
