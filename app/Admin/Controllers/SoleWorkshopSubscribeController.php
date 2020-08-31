<?php

namespace App\Admin\Controllers;

use App\Models\SoleWorkshopSubscribe;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\PurchaseStandard;
use App\Models\Supplier;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Tab;
use Faker\Factory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class SoleWorkshopSubscribeController extends AdminController
{

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $purchase_standard = PurchaseStandard::get(['id','purchase_standard_name']);

        return Form::make(new SoleWorkshopSubscribe(), function (Form $form) use($purchase_standard){
            $no = getOrderNo('sole_workshop_subscribe','DC',10,'sole_workshop_subscribe_no');
            $form->column(6, function (Form $form) use($no){
                $form->text('sole_workshop_subscribe_no')->required()->default($no);
                $form->hidden('raw_material_category_id');
                $form->text('raw_material_category_name')->required()->readonly();
                $form->selectResource('supplier_id')
                    ->path('dialog/supplier') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Supplier::findOrFail($v)->pluck('supplier_name', 'id');
                    })->required();
                $form->hidden('supplier_name');
                $form->hidden('color_id');
                $form->hidden('unit');
                $form->hidden('unit_id');
                $form->hidden('change_coefficient');
                $form->text('color')->readonly()->required();
            });
            $form->column(6, function (Form $form) {
                $form->selectResource('raw_material_product_information_id','原材料编号')
                    ->path('dialog/raw-material-product-information') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return RawMaterialProductInformationNo::findOrFail($v)->pluck('raw_material_product_information_no', 'id');
                    })->required();
                $form->hidden('raw_material_product_information_no');
                $form->text('raw_material_product_information_name')->readonly()->required();
                $form->text('price')->append('<span style="position: relative;top:8px;">&nbsp;&nbsp;元</span>')->required()->readonly();
            });
            $form->column(12, function (Form $form) use($purchase_standard){
                $form->html(function () use($purchase_standard){
                    $purchase_standard = json_encode($purchase_standard);
                    return  <<<EHTML
<style>
.spec-top{background: #487cd0;color:#fff;padding:10px 25px}
.spec-title{position: relative;top:2px;padding-right:5px;}
#spec-table tr td,#total tr td{text-align: center}
#spec-table tr td span{
display: inline-block;
    margin-right:10px;
}
.input-h1{text-align:center;width:200px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
.input-h2{text-align:center;width:80px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}
.input-h3{text-align:center;width:120px;height:36px;outline:none;border:1px solid #d9d9d9;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;}

select {width:120px;height:36px;}
.total{}
#plan_order_num{background: #fff;border-radius: 3px;border:1px solid #d9d9d9;outline:none;}

</style>
<div class="spec-top " style="">
    <span class="spec-title">请选择规格/尺码数量</span>
    <select name="" id="order_num" class=" col-md-1 select">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
    </select>
</div>
<div class="spec-body" id="spec-body">
    <table id="spec-table" class="table">

    </table>
</div>
<div>
    <table>
    <tr>
    <td width="25%">&nbsp;</td><td width="50%">&nbsp;</td>
    <td width="23.5%" style="text-align: right">
        <span>数量合计:</span>
        <input name="all_num"  id="all_num"  class="input-h3 "
         value="0" readonly />
    </td>
</tr>
</table>
</div>
<hr>
<script >
$(function() {
  var count = 1;
  var purchase_standard = {$purchase_standard}
   var optionshtml ='';
   $.each(purchase_standard,function(index,data) {
      optionshtml+='<option value="'+data.id+'">'+data.purchase_standard_name+'</option>'
    })
      //初始化
    $('#spec-table').append(oneline(1,'',optionshtml))
    calculatePrice
     unitchange()
      calcuAllNum()
  //选择数量
  $(document).on('change','.num',function() {
       calculatePrice()
        calcuAllNum()
  })
  $(document).on('change','#order_num',function() {
      var that = this;
      var new_spec_num = $(that).val()
      var old_spec_num = $('#spec-table tr').length

      if(old_spec_num<new_spec_num){
          //增加
          var removenum = new_spec_num-old_spec_num
          var appendhtml = '';
          for(i=0;i<removenum;i++){
              appendhtml+=oneline(old_spec_num+1+i,'',optionshtml)
          }
          $('#spec-table').append(appendhtml)
      }else if(old_spec_num>new_spec_num){
          //删除
          var fromnum = parseInt(new_spec_num)

           $('#spec-table').find("tr:nth-child("+fromnum+")").nextAll().remove();
      }
      unitchange()
      calculatePrice()
      calcuAllNum()
  })
})
function calculatePrice() {
      $("input.money").each(function(){
        var that =this
        var price =  $("input[name=price]").length>0?$("input[name=price]").val():0;
        var num = $(that).parent().prev().children('input.num').val();
        var change_coefficient = $(that).parent().prev().children('input.change').val();
        var all_price = parseFloat(price)*parseFloat(change_coefficient)*parseFloat(num);
        all_price = all_price>0?all_price:0
        $(that).val(all_price.toFixed(2))
      });
    }
function unitchange() {
     var unit =  $("input[name=unit]").length>0?$("input[name=unit]").val():'';
      var change_coefficient =  $("input[name=change_coefficient]").length>0?$("input[name=change_coefficient]").val():0;
      setTimeout(function(){
           $(".change").each(function(){
            $(this).val(change_coefficient)
            });
          $(".unit").each(function(){
             $(this).val(unit)
          });
      }, 100);

}
function calcuAllNum(){

    var all_num=0;
    $("input.num").each(function(){
        var that =this
        let temp_num = parseFloat($(that).val());
        all_num+=temp_num;
      });
    $('#all_num').val(all_num.toFixed(0))
}
  function oneline(index,data,optionshtml) {
    if(!data){
        return ' <tr>'+
      '<td width="25%"><span>规格</span>' +
      '<select name="specarr[spec][]">' +optionshtml+
        '</select></td>'+
       '<td>' +
        '   <span>申购数量:</span>' +
         '  <input name="specarr[num][]"   class="input-h2 num"  value="0"/>&nbsp;&nbsp;' +
          '<input type="text" class="input-h2 unit" readonly />&nbsp;&nbsp;x&nbsp;&nbsp;' +
           '<input type="text" class="input-h2 change"  readonly/></td>'+
       '<td style="text-align:left" width="25%"><span>金额:</span>' +
        '<input name="specarr[money][]"  class="input-h3 money" value="0" ></td>'+
        '</tr>';
    }
  }
</script>
EHTML;
                },' ')->width(11,1);
            });
            $form->column(6, function (Form $form) {
                $form->hidden('apply_user_id')->default(Admin::user()->id);
                $form->text('apply_user_name')->default(Admin::user()->name)->required()->readonly();
            });
            $form->column(6, function (Form $form) {
                $form->datetime('date_at','申请时间')->format('YYYY-MM-DD HH:mm:ss')->default(Carbon::now())->required();
            });
            $form->column(12, function (Form $form) {
                $form->hidden('total_num');
                $form->textarea('subscribe_remark')->width(10,1);
                $form->textarea('subscribe_content')->width(10,1);
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
            $form->disableListButton();
            $form->disableDeleteButton();

            $form->submitted(function (Form $form) {
                $form->deleteInput('_token');
                $query = SoleWorkshopSubscribe::query()->where('sole_workshop_subscribe_no',
                    $form->sole_workshop_subscribe_no);

                if($form->isEditing()){
                    $id = $form->getKey();
                    $query = $query->where('id','!=',$id);
                }
                $no_check = $query->count();
                //检测单号
                if($no_check>0){
                    return $form->error('申购单号已存在，请修改');
                }


            });
        });
    }


    /**
     * 保存页面
     * @param ProductRequest $request
     * @return mixed
     */
    public function storeH(Request $request)
    {
        $res = $this->saveH($request);
        $form=new Form();
        if($res['status']=='success'){
            return $form->redirect(
                admin_url('sole-workshop-subscribe-detail'),
                trans('admin.save_succeeded')
            );
        }else{
            return $form->error($res['message']);
        }
    }
    /**
     * 保存
     * @param Request $request
     * @param null $id
     * @return \App\Models\WorkshopPurchase
     */
    protected function saveH(Request $request, $id = null)
    {

        $data=$request->all();

        DB::beginTransaction(); //开启事务
        //添加
        $sole_workshop_subscribe = new SoleWorkshopSubscribe();
        $sole_workshop_subscribe_detail = new SoleWorkshopSubscribeDetail();
        try{
            $now = Carbon::now();
            $total_num  =array_sum($data['specarr']['num']);
            $workshoppurchase = $sole_workshop_subscribe->create([
                'sole_workshop_subscribe_no'=>$data['sole_workshop_subscribe_no'],
                'raw_material_product_information_no'=>$data['raw_material_product_information_no'],
                'raw_material_product_information_id'=>$data['raw_material_product_information_id'],
                'raw_material_product_information_name'=>$data['raw_material_product_information_name'],
                'raw_material_category_id'=>$data['raw_material_category_id'],
                'raw_material_category_name'=>$data['raw_material_category_name'],
                'supplier_id'=>$data['supplier_id'],
                'supplier_name'=>$data['supplier_name'],
                'price'=>$data['price'],
                'color_id'=>$data['color_id'],
                'color'=>$data['color'],
                'total_num'=>$total_num,
                'subscribe_remark'=>$data['subscribe_remark'],
                'subscribe_content'=>$data['subscribe_content'],
                'date_at'=>$data['date_at'],
                'apply_user_id'=>$data['apply_user_id'],
                'apply_user_name'=>$data['apply_user_name'],
            ]);

            $detail_data=[];
            if(!isset( $data['specarr']['num']) || count( $data['specarr']['num'])<=0){
                DB::rollback();
                return [
                    'message' => '请选择规格',
                    'status' => 'error',
                ];
            }
            $all_num=0;
            foreach ( $data['specarr']['spec'] as $kk=>$vv){
                if($data['specarr']['num'][$kk]>0){
                    $all_num += $data['specarr']['num'][$kk];
                    $detail_data[$kk]['sole_workshop_subscribe_id']= $workshoppurchase->id;
                    $detail_data[$kk]['purcahse_standard_id']= $vv;
                    $detail_data[$kk]['purcahse_standard_name']= PurchaseStandard::find($vv)->purchase_standard_name;
                    $detail_data[$kk]['apply_num']=$data['specarr']['num'][$kk];
                    $detail_data[$kk]['unit_name']= $data['unit'];
                    $detail_data[$kk]['unit_id']= $data['unit_id'];
                    $detail_data[$kk]['change_coefficient']= $data['change_coefficient'];
                    $detail_data[$kk]['total_price']=$data['specarr']['money'][$kk];
                    $detail_data[$kk]['price']= $data['price'];
                    $detail_data[$kk]['created_at']= $now;
                    $detail_data[$kk]['updated_at']= $now;
                }else{
                    continue;
                }
            }
            if(!($all_num>0)){
                DB::rollback();
                return [
                    'message'=>'请确认申购数量',
                    'status'=>'error'
                ];
            }
            $sole_workshop_subscribe_detail->insert($detail_data);
            $showdata  =$workshoppurchase;
            DB::commit();
            return [
                'message'=>'成功',
                'status'=>'success',
                'data'=>$showdata
            ];
        }catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }

}
