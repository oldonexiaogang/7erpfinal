<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\MoldInformationVoidChange;
use App\Admin\Extensions\Tools\MoldInformationMultiPrint;
use App\Models\MoldInformation;
use App\Models\Client;
use App\Models\Personnel;
use App\Models\MoldMaker;
use App\Models\MoldCategory;
use App\Models\CompanyModel;
use App\Models\CraftInformation;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Services\PrinterService;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Form\NestedForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class MoldInformationController extends AdminController
{

    public function __construct(){
        $this->property_arr = config('plan.mold_information_property');
    }
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make(new MoldInformation(), function (Grid $grid) {
            $grid->model()->with(['mold_category_parent','mold_category_child'])->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->fixColumns(2,0);
            $grid->column('date_at','开模日期')->display(function (){
                return date('Y-m-d',strtotime($this->date_at));
            })->width('78px');
            $grid->column('check_at')->display(function (){
                return $this->check_at?date('Y-m-d',strtotime($this->check_at)):'';
            })->width('78px');
            $grid->mold_information_no;
            $grid->column('mold_information_no')->display(function (){
                $id = $this->id;
                return '<a href="javascript:void(0)" id="'.$id.'_mold_information_paper"
                style="text-decoration: underline"
                data-url="'.admin_url('mold-information/print?id='.$this->id).'" >'.
                    $this->mold_information_no.'</a>
<script >
     $("#'.$id.'_mold_information_paper").on("click",function (){
                        let url = $(this).attr("data-url")
                        layer.closeAll();
                         parent.layer.open({
                          type: 2,
                          title: "模具开发通知单",
                          shadeClose: true,
                          shade: false,
                          maxmin: true, //开启最大化最小化按钮
                          area: ["800px", "800px"],
                          content: url
                        });
                    })
</script>
';
            });

            $grid->client_name;
            $grid->company_model;
            $grid->column('mold_category_parent_id')->display(function (){
                return $this->mold_category_parent['mold_category_name'];
            });
            $grid->column('mold_category_child_id')->display(function (){
                return $this->mold_category_child['mold_category_name'];
            });
            $grid->mold_make_detail_standard;
            $grid->column('sole_count');
            $grid->actual_size;
            $grid->settle_size;
            $grid->price;
            $grid->column('total_price');
            $grid->column('chakan','查看')->dialog(function (){
                return ['type'=>'url',
                        'url'=>admin_url('mold-information/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<i class="feather icon-search grid-action-icon"></i>'
                ];
            });
            $grid->column('operation','操作')->display(function (){
                if($this->check==1){
                    return '-';
                }else{
                    $url= admin_url('mold-information/'.$this->id.'/edit?dialog=1');
                    Form::dialog('修改',$url)
                        ->click('#update_mold_information_form_'.$this->id)
                        ->url($url)
                        ->width(config('plan.dialog.width'))
                        ->height(config('plan.dialog.height'))
                        ->saved(
                            <<<JS
JS
                        );
                    return "<a href='javascript:void(0)' id='update_mold_information_form_".$this->id."'>
<i  class=\"feather icon-edit grid-action-icon\"></i></a>";
                }

            });
            $grid->column('is_void','是否废除')->action(MoldInformationVoidChange::class);
//            $grid->column('delete','删除')->display(function (){
//                return '<a href="javascript:void(0);" data-url="'.admin_url('mold-information/'.$this->id).'" data-action="delete">
//                            <i class="feather icon-trash grid-action-icon"></i>
//                        </a>';
//            });
            $grid->properties->using($this->property_arr);
            $grid->personnel_name;
            $grid->mold_maker_name;
//            $grid->tools([
//                new MoldInformationMultiPrint('批量打印'),
//            ]);
            $all_count = MoldInformation::where('is_void','0')->sum('sole_count');
            $settle_size_count = MoldInformation::where('is_void','0')->sum('settle_size');
            $actual_size_count = MoldInformation::where('is_void','0')->sum('actual_size');
            $all_price = MoldInformation::where('is_void','0')->sum('total_price');

            $grid->header(function ($query) use($all_count,$all_price,$actual_size_count,$settle_size_count){
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $width=config("plan.dialog.width");
                $height= config("plan.dialog.height");
                Form::dialog('新增模具资料')
                    ->click('.create-mold-information-form-dialog') // 绑定点击按钮
                    ->url('mold-information/create') // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换。。
                    ->width(config('plan.dialog.width')) // 指定弹窗宽度，可填写百分比，默认 720px
                    ->height(config('plan.dialog.height')) // 指定弹窗高度，可填写百分比，默认 690px
                    ->success('Dcat.reload()'); // 新增成功后刷新页面
                return <<<ERD
                        <div style="position: absolute;left:95px;top:-30px;">
                          <button id="multiPrint" type="button"class="btn btn-primary btn-sm btn-mini">批量打印</button>
                          <button
class='create-mold-information-form-dialog btn btn-primary btn-mini btn-sm'>新增</button>
                          <div style="position: relative;top:-22px;left:150px;">
                               <label>总计模具付数:<span class="text-danger">{$all_count}</span></label>
                               <label>总金额:<span class="text-danger">{$all_price}</span></label>
                               <label>实际码数:<span class="text-danger">{$actual_size_count}</span></label>
                               <label>结算码数:<span class="text-danger">{$settle_size_count}</span></label>
                            </div>
                        </div>
<script >
var envheader ="{$env_prefix}";
var width ="{$width}";
var height ="{$height}";

$(function (){
    $('#multiPrint').on('click',function (){
        //获取选中的id
        var ids=[];
        $('input.grid-row-checkbox:checked').each(function (index,data){
            ids.push($(data).attr('data-id'));
        })
        if(ids.length<=0){
            toastr.warning('请先选择数据');
           return
        }else{
            let idstxt = ids.join(',');
            layer.open({
              type: 2,
              title: '模具资料开发通知单',
              shadeClose: true,
              shade: false,
              maxmin: true, //开启最大化最小化按钮
              area: [width, height],
              content: '/'+envheader+'/mold-information/print?id='+idstxt
            });
        }
    })

})
</script>
ERD;
            });
            $grid->disableCreateButton();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->disableEditButton();
            $grid->disableQuickEditButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();

            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->paginate(15);
            //搜索
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
                    })->width(2);
                $filter->like('mold_information_no')->width(2);
                $filter->equal('mold_category_parent_id')->select('api/mold-category-parent')->width(2);
                $filter->equal('mold_category_child_id')->select('api/mold-category-child')->width(3);
                $filter->equal('properties')->select($this->property_arr)->width(2);
                $filter->equal('personnel_name')
                    ->selectResource('dialog/personnel')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Personnel::findOrFail($v)->pluck('name', 'id');
                    })->width(2);
                $filter->between('date_at')->date()->width(3);
                $filter->between('check_at')->date()->width(3);
                $filter->equal('mold_maker_id')
                    ->selectResource('dialog/mold-maker')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return MoldMaker::findOrFail($v)->pluck('mold_maker_name', 'id');
                    })->width(2);

            });
            //导出
            $titles = [
                'date_at' => '开模日期',
                'check_at' => '验收日期',
                'mold_information_no' => '单号',
                'client_name' => '客户',
                'company_model' => '公司型号',
                'mold_category_parent_name' => '模具类型',
                'mold_category_child_name' => '模具产品类型',
                'mold_make_detail_standard' => '模具生产规格',
                'sole_count' => '总付数',
                'actual_size' => '码数',
                'price' => '单价',
                'total_price' => '金额',
                'money_from' => '备注(金额来源)',
                'properties' => '所属性质',
                'personnel_name' => '业务员',
                'mold_maker_name' => '模具生产商'
            ];
            $filename = '模具资料'.date('Y-m-d H:i:s');
            $grid->export($titles)->filename($filename)->rows(function (array $rows){
                foreach ($rows as $index => &$row) {
                    $row['mold_category_parent_name'] = MoldCategory::find($row['mold_category_parent_id'])->mold_category_name;
                    $row['mold_category_child_name'] = MoldCategory::find($row['mold_category_child_id'])->mold_category_name;
                    $row['properties'] = config('plan.mold_information_property')[$row['properties']];
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
        $title = "模具资料";
        $mold = MoldInformation::with(['mold_category_parent','mold_category_child'])->findOrFail($id);
        $length=6;
        $info=[
            [
                'label'=>'日期',
                'value'=>$mold->date_at,
                'length'=>$length
            ],
            [
                'label'=>'验收日期',
                'value'=>$mold->check_at,
                'length'=>$length
            ],
            [
                'label'=>'客户',
                'value'=>$mold->client_name,
                'length'=>$length
            ],
            [
                'label'=>'单号',
                'value'=>$mold->mold_information_no,
                'length'=>$length
            ],
            [
                'label'=>'所属性质',
                'value'=>$this->property_arr[$mold->properties],
                'length'=>$length
            ],
            [
                'label'=>'雷力型号',
                'value'=>$mold->company_model,
                'length'=>$length
            ],
            [
                'label'=>'业务员',
                'value'=>$mold->personnel_name,
                'length'=>$length
            ],
            [
                'label'=>'模具生产商',
                'value'=>$mold->mold_maker_name,
                'length'=>$length
            ],
            [
                'label'=>'模具类型',
                'value'=>$mold->mold_category_parent['mold_category_name'],
                'length'=>$length
            ],
            [
                'label'=>'模具产品类型',
                'value'=>$mold->mold_category_child['mold_category_name'],
                'length'=>$length
            ],
            [
                'label'=>'鞋底',
                'value'=>explode(',',$mold->mold_make_detail_standard),
                'length'=>'xiedimoju'
            ],
            [
                'label'=>'金额来源',
                'value'=>$mold->money_from,
                'length'=>12
            ],
            [
                'label'=>'鞋底（合计）',
                'value'=>$mold->sole_count,
                'length'=>$length
            ],
            [
                'label'=>'实际码数',
                'value'=>$mold->actual_size,
                'length'=>$length
            ],
            [
                'label'=>'单价/元',
                'value'=>$mold->price,
                'length'=>$length
            ],
            [
                'label'=>'金额/元',
                'value'=>$mold->total_price,
                'length'=>$length
            ],
            [
                'label'=>'备注',
                'value'=>$mold->remark,
                'length'=>12
            ],
        ];
        $reback = admin_url('mold-information');
        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }
    /**
     * 表单
     */
    protected function form()
    {
        $property_arr= $this->property_arr;
        return Form::make(new MoldInformation(), function (Form $form) use($property_arr){
            $now = Carbon::now();
            $id = $form->getKey();
            $form->column(6, function (Form $form) use($now){
                $form->datetime('date_at')->format('YYYY-MM-DD HH:mm:ss')->default($now);
                $form->select('client_id')->options('api/client')
                    ->required();
                //关联调用
//                $form->select('client_id')->options('api/client')
//                    ->load('company_model_id','api/company-model-and-client');

                $form->hidden('company_model');
                $form->hidden('client_name');
                $mold_information_no = getOrderNo('mold_information','MJ-',3,'mold_information_no');
                $form->text('mold_information_no')->required()->rules(function ($form) {
                    // 如果不是编辑状态，则添加字段唯一验证
                    if (!$id = $form->model()->id) {
                        return 'unique:mold_information,mold_information_no';
                    }
                })->default($mold_information_no);;
                $form->hidden('personnel_id');
                $form->text('personnel_name')->required()->readonly();
                $form->select('mold_category_parent_id')->options('api/mold-category-parent')->load('mold_category_child_id','api/mold-category-child');
            });
            $form->column(6, function (Form $form) use($property_arr){
                $form->date('check_at')->format('YYYY-MM-DD HH:mm:ss');
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('company_model_id')->options('api/company-model')->required()
                ->append(
                    <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info
fa fa-plus"
id="toChangeCraftInfo"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftInfo').on('click',function (){
        layer.open({
          type: 2,
          title: '工艺资料',
          shadeClose: true,
          shade: false,
          maxmin: true, //开启最大化最小化按钮
          area: ['700px', '600px'],
          content: '/'+envheader+'/craft-information?dialog=1&field=craft_info'
        });

    })

})
function change_craft_info(){

    var target = $('.field_company_model_id')
    let getturl = '/'+envheader+'/api/company-model'

    $.post(getturl,{_token:_token},function(data,ret) {
       if(ret=='success'){
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
                $form->select('properties')->options($property_arr)->required();
                $form->select('mold_maker_id')->options('api/mold-maker')->required();
                $form->hidden('mold_maker_name');
                $form->hidden('mold_make_detail_standard');
                $form->select('mold_category_child_id');
                $form->hidden('_token')->value(csrf_token());
            });
            $form->column(12, function (Form $form) use($id){
                $form->html(function () use($id){
                    if($id>0){
                        $mold_make_detail_standard = MoldInformation::find($id)->mold_make_detail_standard;
                    }else{
                        $mold_make_detail_standard='';
                    }
                    $csrf=csrf_token();
                    $envheader=getenv('ADMIN_ROUTE_PREFIX');
                    return <<<EDO
<style>
     .input-h{width:95px;height:36px;text-align:center;margin-right:10px;margin-bottom:5px;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;outline: none;border:1px solid #d9d9d9}
</style>
<script >

$(function() {
    var routeheader = "{$envheader}";
     //选择客户后调出业务员
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
   //选择模具生产资料后调出模具价格
   $('.field_mold_maker_id,.field_mold_category_parent_id,.field_mold_category_child_id').on('change',function(e) {
        var mold_maker_id = $('select[name=mold_maker_id]').val();

        var mold_category_parent_id = $($('select[name=mold_category_parent_id]')[1]).val();
        var mold_category_child_id = $($('select[name=mold_category_child_id ]')[1]).val();

        if(mold_maker_id>0&&mold_category_parent_id>0&&mold_category_child_id>0){
            let posturl = '/'+"{$envheader}"+'/api/mold-price-search'
            let token = "{$csrf}"
            $.post(posturl,{mold_maker_id:mold_maker_id,
            mold_category_parent_id:mold_category_parent_id,mold_category_child_id:mold_category_child_id,
            '_token':token},function(res) {
               res = JSON.parse(res);
               if(res.code==200){
                   $(document).find('input[name=price]').val(res.data.price)
                   calcuTotalPrice()
               }
            })
        }
      })

  var mold_make_detail_standard_text = "{$mold_make_detail_standard}"
  var mold_make_detail_standard = mold_make_detail_standard_text.split(',')
  var inputhtml1 = ''
  var inputhtml2 = ''

  if(mold_make_detail_standard.length>0){
      $.each(mold_make_detail_standard,function(index,data) {
        if(index>=8){
            inputhtml2+='<input name="mold_make_detail_standard[]" class="input-h" type="text" value="'+data+'">'
        }else{
            inputhtml1+='<input name="mold_make_detail_standard[]" class="input-h" type="text" value="'+data+'">'
        }
      })
      var changeNum = Math.abs(mold_make_detail_standard.length-8);

      if(mold_make_detail_standard.length>8){
           inputhtml2+=getInput(16-mold_make_detail_standard.length)
      }else{
          inputhtml1+=getInput(changeNum)
          inputhtml2+=getInput(8)
      }
  } else{
    inputhtml2 = inputhtml1 = getInput(8);
  }

  $('#line1').append(inputhtml1)
  $('#line2').append(inputhtml2)
    $('input[name="mold_make_detail_standard[]"]').on('blur',function(res) {
      //汇总数量
      var num = 0;
      $('input[name="mold_make_detail_standard[]"]').each(function() {
        if($(this).val()){
            num+=1;
        }
      })
        $(document).find('input[name=sole_count]').val(num)
        $(document).find('input[name=settle_size]').val(num)
        $(document).find('input[name=actual_size]').val(num)
        calcuTotalPrice()
    })
     //金额或数量变化，总金额变化
      $('input[name="settle_size"]').on('blur',function(res) {
        calcuTotalPrice($(this).val())
    })
})

 function calcuTotalPrice(send_num){
      let init_num = $('input[name=settle_size]').val();
      let init_price = $('input[name=price]').val();
      let price = init_price>0?init_price:0;
      let num = send_num>0?send_num:(init_num>0?init_num:0);
      let total_price = (parseFloat(num)* parseFloat(price)).toFixed(2)
       $(document).find('input[name=total_price]').val(total_price)
   }
function getInput(num) {
    var inputhtml1 = '';
   for (var i=0;i<num;i++) {
        inputhtml1+='<input name="mold_make_detail_standard[]" class="input-h" type="text">'
    }
   return inputhtml1;
}
</script>
<table>
    <tr>
      <td>模具明细规格</td>
        <td id="line1">

        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td id="line2">

        </td>
    </tr>
</table>
EDO;

                },'鞋底')->oneline(true)->width(10,1);
            });
            $form->column(6, function (Form $form) {

            });
            $form->column(6, function (Form $form) {
                $form->text('sole_count')->append("<label style='padding:10px 10px 0 10px;'>付</label>");
            });
            $form->column(6, function (Form $form) {
                $form->hidden('money_from')->default('手工录入');
                $form->text('actual_size')->append("<label style='padding:10px 10px 0 10px;'>双</label>");
                $form->text('price')->required();
            });
            $form->column(6, function (Form $form) {
                $form->text('settle_size')->append("<label style='padding:10px 10px 0 10px;'>双</label>");
                $form->text('total_price')->append("<label style='padding:10px 10px 0 10px;'>元</label>");
            });
            $form->column(12, function (Form $form) {
                $form->textarea('remark')->width(10,1);
                $form->image('image','图片')
                    ->autoUpload()->uniqueName()->width(10,1);
            });
            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                if($form->mold_make_detail_standard){
                    $form->mold_make_detail_standard =implode(',',array_filter($form->mold_make_detail_standard));

                }

            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                $id = $form->getKey();
                $mold = MoldInformation::find($id);
                if($form->client_id){
                    $client = Client::find($form->client_id);
                    $mold->client_name = $client->client_name;
                }
                if($form->personnel_id){
                    $psersonnel = Personnel::find($form->personnel_id);
                    $mold->personnel_name =$psersonnel->name;
                }
                if($form->mold_maker_id){
                    $mold_maker= MoldMaker::find($form->mold_maker_id);
                    $mold->mold_maker_name =$mold_maker->mold_maker_name;
                }
                if($form->company_model_id){
                    $companymodeldata= CompanyModel::where('id',$form->company_model_id)
                        ->first();
                    $mold->company_model =$companymodeldata->company_model_name;
                }
                $mold->save();

            });
        });
    }

    /**
     * 打印
     */
    public function moldInformationPrinter(Request $request){
        $id = $request->id;
        $printer = new PrinterService();
        return $printer->moldInformationTable($id);
    }
}
