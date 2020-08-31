<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grid\RowAction\ClientSoleInformationVoidChange;
use App\Models\ClientSoleInformation;
use App\Models\CompanyModel;
use App\Models\CraftInformation;
use App\Models\DeliveryPrice;
use App\Models\PlanList;
use App\Models\ProductCategory;
use App\Models\Client;
use App\Models\CraftColor;
use App\Models\SoleMaterial;
use App\Models\ClientModel;
use App\Models\Personnel;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;

class ClientSoleInformationController extends AdminController
{
    protected $uses;
    protected $weltstatus;
    public function __construct(){
        $this->uses = ['1'=>'正常','0'=>'禁用'];
        $this->weltstatus = ['1'=>'是','0'=>'否'];
    }
    /**
     * 复制首页跳转
     */
    public function copyIndex(){
        return  redirect(admin_url("client-sole-information"));
    }

    /**
     * dec: 复制计划单
     * @param $id
     * @param Content $content
     * author : happybean
     * date: 2020-05-21
     */
    public function copyData($id,Content $content){
        return $content
            ->title($this->title())
            ->description($this->description()['create'] ?? trans('admin.create'))
            ->body($this->formCopy($id));
    }

    protected function formCopy($id)
    {
        $client_sole_info = ClientSoleInformation::find($id);

        return Form::make(new ClientSoleInformation(), function (Form $form) use($client_sole_info){

            $form->column(6, function (Form $form) use($client_sole_info) {
                $form->select('client_id')->options('api/client')
                    ->load('company_model_id','api/company-model-and-client')
                    ->required()->value($client_sole_info->client_id);
                $form->hidden('client_name')->value($client_sole_info->client_name);
                $form->select('client_model_id')->required()
                    ->value($client_sole_info->client_model_id);
                $form->hidden('client_model')->value($client_sole_info->client_model);

                $form->selectResource('sole_material_id')
                    ->path('dialog/sole-material') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return SoleMaterial::findOrFail($v)->pluck('sole_material_name', 'id');
                    })->required()->value($client_sole_info->sole_material_id);
                $form->hidden('sole_material_name')->value($client_sole_info->sole_material_name);
                $form->hidden('personnel_id')->value($client_sole_info->personnel_id);
                $form->text('personnel_name')->readonly()->value($client_sole_info->personnel_name);
                $form->select('is_welt')->options($this->weltstatus)->value($client_sole_info->is_welt);
                $form->select('is_use')->options($this->uses)->default(1)->value($client_sole_info->is_use);
            });
            $form->column(6, function (Form $form) use($client_sole_info){
                $form->select('company_model_id')
                    ->required()->value($client_sole_info->company_model_id);
                $form->hidden('company_model')->value($client_sole_info->company_model);
                $form->selectResource('product_category_id')
                    ->path('dialog/product-category') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return ProductCategory::findOrFail($v)->pluck('product_category_name', 'id');
                    })->required()->value($client_sole_info->product_category_id);
                $form->hidden('product_category_name')->value($client_sole_info->product_category_name);
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('craft_color_id')->required()
                    ->append(
                        <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor').on('click',function (){
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

                    )->value($client_sole_info->craft_color_id);
                $form->hidden('craft_color_name')->value($client_sole_info->craft_color_name);
                $form->datetime('date_at')->default(Carbon::now())->value(Carbon::now());
                $form->select('is_color')->options($this->weltstatus)->value($client_sole_info->is_color);
            });

            $form->column(12, function (Form $form) use($client_sole_info) {
                $form->text('knife_mold')->width(10,1)->value($client_sole_info->knife_mold);
                $form->text('leather_piece')->width(10,1)->value($client_sole_info->leather_piece);
                $form->text('welt')->width(10,1)->value($client_sole_info->welt);
                $form->text('sole','鞋跟配件')->width(10,1)->value($client_sole_info->sole);
                $id = $form->getKey();
                $form->html(function () use ($id,$client_sole_info){
                    $spec =  config('plan.normal_size');

                    $client_sole_information = $client_sole_info;
                    $start_code = $client_sole_information->start_code;
                    $end_code = $client_sole_information->end_code;
                    $startHtml='';
                    $endHtml='';
                    foreach ($spec as $k=>$v){
                        $startHtml .= '<option value="'.$v.'" '.($start_code==$v?'selected':'').'>'.$v.'码</option>';
                        $endHtml .= '<option value="'.$v.'"  '.($end_code==$v?'selected':'').'>'.$v.'码</option>';
                    }

                    $csrf=csrf_token();
                    $envheader=getenv('ADMIN_ROUTE_PREFIX');
                    return <<<AD
<input type="hidden" id="token" value="{$csrf}">
<script >
$(function() {
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
  //颜色改变触发 沿条底和改色
  $('.field_craft_color_id').on('change',function (e){
       var status_arr = ['否','是']
       var status_arr_json = [{id:0,text:'否'},{id:1,text:'是'}];
     var color_name =$('.field_craft_color_id').find("option:selected").text();

     var is_change_color = color_name.search('改色')>-1?'1':'0';
     var is_welt = color_name.search('沿条')>-1?'1':'0';

     $(".field_is_color").find("option").remove();
     $(".field_is_welt").find("option").remove();
    $(".field_is_color").select2({
            data: status_arr_json,
           //默认空点选
        }).val(is_change_color).trigger('change');
     $(".field_is_welt").select2({
            data: status_arr_json,
           //默认空点选
        }).val(is_welt).trigger('change');
     $('select[name=is_color]').val(is_change_color)
     $('select[name=is_welt]').val(is_welt)

  })
})
</script>
<style>
.simselect{width:150px;height:36px;}
</style>
<select class="simselect" name="start_code">{$startHtml}</select>&nbsp;&nbsp;至&nbsp;&nbsp;
                        <select class="simselect" name="end_code">{$endHtml}</select>
AD;
                },'码数')->width(10,1);
                $form->text('out')->width(10,1)->value($client_sole_info->out);
                $form->text('inject_mold_ask')->width(10,1)->value($client_sole_info->inject_mold_ask);
                $form->textarea('craft_ask')->width(10,1)->value($client_sole_info->craft_ask);
                $form->textarea('remark')->width(10,1)->value($client_sole_info->remark);
                $form->hidden('_token')->value(csrf_token());
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
                $client_id = $form->client_id;
                $company_model_id = $form->company_model_id;
                $kehu_model = $form->client_model_id;
                $craft_color_id = $form->craft_color_id;
                $query = ClientSoleInformation::where('client_id',$client_id)
                    ->where('company_model_id',$company_model_id)
                    ->where('client_model_id',$kehu_model)
                    ->where('craft_color_id',$craft_color_id);
                if($form->getKey()){
                    $query = $query->where('id','!=',$form->getKey());
                }
                $is_exist = $query->count();
                if($is_exist){
                    return $form->error('已存在此工艺单');
                }
            });
            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });

        });
    }

    /**
     *列表
     */
    protected function grid()
    {
        return Grid::make(new ClientSoleInformation(), function (Grid $grid) {
            $grid->model()->orderBy('date_at','desc');
            $grid->fixColumns(2, 0);
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->column('date_at','日期')->width("80px")->display(function (){
                return $this->is_copy?"<span class='text-danger'>".$this->date_at."</span>":$this->date_at;
            });
            $grid->client_name;
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
            $grid->sole_material_name;
            $grid->personnel_name;
            $grid->remark;
            $grid->column('is_use','使用状态')->action(ClientSoleInformationVoidChange::class);
            $grid->column('copy','复制')->display(function (){
                $url = admin_url('client-sole-information/copy/'.$this->id);
                Form::dialog('复制',$url)
                    ->click('#copy_form_client_sole_info'.$this->id)
                    ->url($url)
                    ->width(config('plan.dialog.width'))
                    ->height(config('plan.dialog.height'))
                    ->success(
                        <<<JS
                    // 保存成功之后刷新页面
                    Dcat.reload();
JS
                    );
                return "<a class='text-info' id='copy_form_client_sole_info".$this->id."' >
 <i class=\"feather icon-copy grid-action-icon\"></i></a>";
            });
            $grid->column('oprateion','操作')->display(function () {
                $url= admin_url('client-sole-information/'.$this->id.'/edit');
                Form::dialog('修改',$url)
                    ->click('#edit_form_client_sole_information'.$this->id)
                    ->url($url)
                    ->width('900px')
                    ->height('650px');
                return "<a class='text-info' id='edit_form_client_sole_information".$this->id."' >
<i class=\"feather icon-edit grid-action-icon\"></i></a>";
            });
            $grid->column('delete','删除')->display(function (){
                //计划单后需要修改
                $usenum = PlanList::where('client_sole_information_id',$this->id)->count();
                if($usenum){
                    return '-';
                }else{
                    return '<a href="javascript:void(0);" data-url="'.
                        admin_url('client-sole-information/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }
            });

            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableActions();
            $grid->toolsWithOutline(false);
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id')
                    ->selectResource('dialog/client')
                    ->options(function ($v) { // options方法用于显示已选中的值
                        if (!$v) return $v;
                        return Client::find($v)->pluck('client_name', 'id');
                    })->width(2);
                $filter->like('company_model')->width(2);
                $filter->like('client_model')->width(2);
                $filter->like('craft_color_name')->width(2);
                $filter->like('sole_material_name')->width(2);
                $filter->equal('product_category_id')->select('api/product-category')->width(2);
                $filter->equal('is_use')->select($this->uses)->width(2);
                $filter->equal('personnel_id')->select('api/personnel')->width(2);
                $filter->between('date_at')->date()->width(3);

            });
            //导出
            $titles = [
                'date_at'=>'日期',
                'client_name'=>'客户名称',
                'company_model' => '雷力型号',
                'client_model' => '客户型号',
                'product_category_name' => '产品类型',
                'craft_color_name' => '工艺颜色',
                'sole_material_name' => '鞋底用料',
                'personnel_name' => '业务员',
                'remark' => '备注',
                'is_use' => '使用状态',
            ];
            $filename = '客户鞋底资料'.date('Y-m-d H:i:s');
            $grid->export($titles)->filename($filename)->rows(function (array $rows) {
                $plan_list_model = new PlanList();
                $is_use_arr = ['1'=>'正常','0'=>'禁用'];
                foreach ($rows as $index => &$row) {
                    $row['is_use'] = $is_use_arr[$row['is_use']];
                }
                return $rows;
            })->xlsx();
        });
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new ClientSoleInformation(), function (Form $form) {

            $form->column(6, function (Form $form) {
                $form->select('client_id')->options('api/client')
                    ->load('company_model_id','api/company-model-and-client')
                    ->required();
                $form->hidden('client_name');
                $form->select('client_model_id')->required();
                $form->hidden('client_model');

                $form->selectResource('sole_material_id')
                    ->path('dialog/sole-material') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return SoleMaterial::findOrFail($v)->pluck('sole_material_name', 'id');
                    })->required();
                $form->hidden('sole_material_name');
                $form->hidden('personnel_id');
                $form->text('personnel_name')->readonly();
                $form->select('is_welt')->options($this->weltstatus);
                $form->select('is_use')->options($this->uses)->default(1);
            });
            $form->column(6, function (Form $form) {
                $form->select('company_model_id')
                    ->required();
                $form->hidden('company_model');
                $form->selectResource('product_category_id')
                    ->path('dialog/product-category') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return ProductCategory::findOrFail($v)->pluck('product_category_name', 'id');
                    })->required();
                $form->hidden('product_category_name');
                $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
                $_token= csrf_token();
                $form->select('craft_color_id')->required()
                    ->append(
                    <<<EOD
<span style="padding:10px 10px 0 10px;font-size: 18px;" class="text-info feather icon-edit-2" id="toChangeCraftColor"> </span>
<script>
var envheader = "{$env_prefix}"
var _token = "{$_token}"
$(function (){
    $('#toChangeCraftColor').on('click',function (){
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
                $form->datetime('date_at')->default(Carbon::now());
                $form->select('is_color')->options($this->weltstatus);
            });

            $form->column(12, function (Form $form) {
                $form->text('knife_mold')->width(10,1);
                $form->text('leather_piece')->width(10,1);
                $form->text('welt')->width(10,1);
                $form->text('sole')->width(10,1);
                $id = $form->getKey();
                $form->html(function () use ($id){
                    $optionHtml='';
                    $spec =  config('plan.client_sole_information_size');

                    if($id&&$id>0){
                        $client_sole_information = ClientSoleInformation::find($id);
                        $start_code = $client_sole_information->start_code;
                        $end_code = $client_sole_information->end_code;
                        $startHtml='';
                        $endHtml='';
                        foreach ($spec as $k=>$v){
                            $startHtml .= '<option value="'.$v.'" '.($start_code==$v?'selected':'').'>'.$v.'码</option>';
                            $endHtml .= '<option value="'.$v.'"  '.($end_code==$v?'selected':'').'>'.$v.'码</option>';
                        }
                    }else{
                        foreach ($spec as $k=>$v){

                            if($k==0){
                                $optionHtml .= '<option value="'.$v.'" selected>'.$v.'码</option>';
                            }else{
                                $optionHtml .= '<option value="'.$v.'">'.$v.' 码</option>';
                            }

                        }
                        $startHtml = $optionHtml;
                        $endHtml = $optionHtml;
                    }
                    $csrf=csrf_token();
                    $envheader=getenv('ADMIN_ROUTE_PREFIX');
                    return <<<AD
<input type="hidden" id="token" value="{$csrf}">
<script >
$(function() {
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

  //颜色改变触发 沿条底和改色
  $('.field_craft_color_id').on('change',function (e){
       var status_arr = ['否','是']
       var status_arr_json = [{id:0,text:'否'},{id:1,text:'是'}];
     var color_name =$('.field_craft_color_id').find("option:selected").text();

     var is_change_color = color_name.search('改色')>-1?'1':'0';
     var is_welt = color_name.search('沿条')>-1?'1':'0';

     $(".field_is_color").find("option").remove();
     $(".field_is_welt").find("option").remove();
    $(".field_is_color").select2({
            data: status_arr_json,
           //默认空点选
        }).val(is_change_color).trigger('change');
     $(".field_is_welt").select2({
            data: status_arr_json,
           //默认空点选
        }).val(is_welt).trigger('change');
     $('select[name=is_color]').val(is_change_color)
     $('select[name=is_welt]').val(is_welt)

  })

  $('select[name = start_code]').on('change',function (e){
      $('select[name = start_code]').attr('value',$(this).val())
  })
  $('select[name = end_code]').on('change',function (e){
      $('select[name = end_code]').attr('value',$(this).val())
  })
})
</script>
<style>
.simselect{width:150px;height:36px;}
</style>
<select class="simselect" name="start_code" value="">{$startHtml}</select>至
<select class="simselect" name="end_code" value="">{$endHtml}</select>
AD;
                },'码数')->width(10,1);
                $form->text('out')->width(10,1);
                $form->text('inject_mold_ask')->width(10,1);
                $form->textarea('craft_ask')->width(10,1);
                $form->textarea('remark')->width(10,1);
                $form->hidden('_token')->value(csrf_token());

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

                $client_id = $form->client_id;
                $company_model_id = $form->company_model_id;
                $kehu_model = $form->client_model_id;
                $craft_color_id = $form->craft_color_id;
                $query = ClientSoleInformation::where('client_id',$client_id)
                    ->where('company_model_id',$company_model_id)
                    ->where('client_model_id',$kehu_model)
                    ->where('craft_color_id',$craft_color_id);
                if($form->getKey()){
                    $query = $query->where('id','!=',$form->getKey());
                }
                $is_exist = $query->count();
                if($is_exist){
                    return $form->error('已存在此工艺单');
                }
            });
            $form->saved(function (Form $form, $result) {
                $id = $form->getKey();
                $this->afterSave($id,$form);
            });

        });
    }

    private function afterSave($id,$form){
        $client = Client::find($form->client_id);
        $company_model = CompanyModel::find($form->company_model_id);
        $client_model = ClientModel::find($form->client_model_id);
        $product_category = ProductCategory::find($form->product_category_id);
        $sole_material = SoleMaterial::find($form->sole_material_id);
        $craft_color = CraftColor::find($form->craft_color_id);
        $client_sole_information = ClientSoleInformation::find($id);
        $personnel= Personnel::find($form->personnel_id);
        $client_sole_information->client_name = $client->client_name;
        $client_sole_information->client_model = $client_model->client_model_name;
        $client_sole_information->company_model = $company_model->company_model_name;
        $client_sole_information->product_category_name = $product_category->product_category_name;
        $client_sole_information->sole_material_name = $sole_material->sole_material_name;
        $client_sole_information->craft_color_name = $craft_color->craft_color_name;
        $client_sole_information->personnel_name = $personnel->name;
        $client_sole_information->is_copy = 0;
        $client_sole_information->start_code = $form->start_code;
        $client_sole_information->end_code = $form->end_code;
        $client_sole_information->save();

        if($form->isCreating()){
            //添加成品发货单价记录
            $time_now = Carbon::now();
            DeliveryPrice::insert([
                'client_id'=>$client_sole_information->client_id,
                'client_name'=>$client_sole_information->client_name,
                'company_model_id'=>$client_sole_information->company_model_id,
                'company_model'=>$client_sole_information->company_model,
                'client_model_id'=>$client_sole_information->client_model_id,
                'client_model'=>$client_sole_information->client_model,
                'product_category_id'=>$client_sole_information->product_category_id,
                'product_category_name'=>$client_sole_information->product_category_name,
                'sole_material_id'=>$client_sole_information->sole_material_id,
                'sole_material_name'=>$client_sole_information->sole_material_name,
                'craft_color_id'=>$client_sole_information->craft_color_id,
                'craft_color_name'=>$client_sole_information->craft_color_name,
                'personnel_id'=>$client_sole_information->personnel_id,
                'personnel_name'=>$client_sole_information->personnel_name,
                'date_at'=>$client_sole_information->date_at,
                'is_use'=>$client_sole_information->is_use,
                'created_at'=>$time_now,
                'updated_at'=>$time_now,
                'price_at'=>$time_now,
            ]);

        }

    }
    /**计划单中调用**/
    public function planListLoadClientSole(Request $request)
    {
        $client_id = $request->post('client_id');
        $client_model_id = $request->post('client_model_id');
        $craft_color_id = $request->post('craft_color_id');
        $company_model_id = $request->post('company_model_id');
        $product_category_id = $request->post('product_category_id');

        $info = ClientSoleInformation::where('client_id', $client_id)
            ->where('client_model_id', $client_model_id)
            ->where('craft_color_id', $craft_color_id)
            ->where('company_model_id', $company_model_id)
            ->where('product_category_id', $product_category_id)
            ->where('is_use', '1')
            ->first();

        if($info){
            return json_encode([
                'code'=>200,
                'data'=>$info,
                'msg'=>'获取success'
            ]);
        }else{
            return json_encode([
                'code'=>100,
                'msg'=>'未获取到信息'
            ]);
        }
    }
    /**
     * dec:弹框选择
     * author : happybean
     * date: 2020-04-19
     */
    public function dialogPriceIndex(Content $content){
        $request = request();
        return $content->body($this->iFrameGrid($request));

    }
    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid($request)
    {
        $grid = new IFrameGrid(new ClientSoleInformation());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()->where('status','1')
            ->where('client_id',$request->client_id)
            ->where('client_model',$request->client_model)
            ->where('company_model',$request->company_model)
            ->where('craft_color_id',$request->craft_color_id)
            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('client_model_name');
        $grid->client_name;
        $grid->client_model;
        $grid->company_model;
        $grid->craft_color_name;
        $grid->price;
        $grid->disableRefreshButton();
        $grid->withBorder();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('client_name')->width(6);
            $filter->like('client_model')->width(6);
            $filter->like('company_model')->width(6);
            $filter->like('craft_color_name')->width(6);
        });

        return $grid;
    }
}
