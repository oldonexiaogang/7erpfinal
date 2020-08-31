<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryPrice;
use App\Models\ClientSoleInformation;
use App\Models\CompanyModel;
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
use  App\Admin\Extensions\Tools\DeliveryPriceMultiCheck;

class DeliveryPriceController extends AdminController
{
    protected $status=[
        0=>'禁用',
        1=>'启用',
    ];
    protected $check_arr=[
        0=>'未验收',
        1=>'已验收',
        2=>'删除',
    ];
    protected $check_color_arr=[
        0=>'#f33',
        1=>'#333',
        2=>'#409EFF',

    ];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $check_color_arr = $this->check_color_arr;
        $check_arr = $this->check_arr;

        return Grid::make(new DeliveryPrice(), function (Grid $grid) use($check_color_arr,$check_arr){
            $grid->model()->orderBy('created_at','desc');
            $grid->column('company_model')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->company_model</span>";
            });
            $grid->column('client_model')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->client_model</span>";
            });
            $grid->column('product_category_name')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->product_category_name</span>";
            });
            $grid->column('date_at','定价时间')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->date_at</span>";
            });
            $grid->column('client_name')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->client_name</span>";
            });
            $grid->column('craft_color_name')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->craft_color_name</span>";
            });
            $grid->column('sole_material_name')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->sole_material_name</span>";
            });
            $grid->column('price')->display(function () use($check_color_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>$this->price</span>";
            });
            $statusarr = $this->status;
            $grid->column('price_status')->display(function () use($statusarr){
                return '<span class="text-info">'.$statusarr[$this->price_status].'</span>';
            });
            $grid->column('is_check')->display(function () use($check_color_arr,$check_arr){
                $color =$check_color_arr[$this->is_check];
                return "<span style='color:".$color."'>".$check_arr[$this->is_check]."</span>";
            });
            $grid->column('caozuo','操作')->display(function (){
                if($this->check!=2){
                    return  ' <a href="'.admin_url('delivery-price/'.$this->id).'">
                            <i class="feather icon-search grid-action-icon"></i>
                        </a>
                        <a href="'.admin_url('delivery-price/'.$this->id.'/edit').'">
                            <i class="feather icon-edit-1 grid-action-icon"></i>
                        </a>
                        ';
                }else{
                    return '-';
                }
            });
            $grid->column('shanchu','删除')->display(function (){
                if($this->is_check!=2){
                    return '<a href="javascript:void(0);" data-url="'.admin_url('delivery-price/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });

            $grid->header(function ($query) {
                return '  <div>

                        <label>*所有在鞋底资料中被修改过的“单价信息”，都以红色字体显示请点击验收</label>
                          <a href="' . admin_url('delivery-price/create') . '" class="btn btn-sm btn-info" title="新增">
                           <span class="hidden-xs">&nbsp;&nbsp;新增&nbsp;&nbsp;</span>
                        </a>
                        </div>';
            });
            $grid->withBorder();
            $grid->disableActions();
            $grid->disableRefreshButton();
            $grid->disableCreateButton();
            $grid->disableFilterButton();
            $grid->batchActions(function ($batch) {
                 $batch->add(new DeliveryPriceMultiCheck('验收'));
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('company_model')->width(2);
                $filter->like('client_model')->width(2);
                $filter->equal('product_category_name')->select($this->check_color_arr)->width(2);
                $filter->like('client_name')->width(2);
                $filter->like('craft_color_name')->width(2);
                $filter->like('price')->width(2);
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
    protected function detail($id)
    {
        $title = "客户信息";
        $info = DeliveryPrice::findOrFail($id);
        $length=4;
        $info=[
            [
                'label'=>'雷力型号',
                'value'=>$info->company_model,
                'length'=>$length
            ],
            [
                'label'=>'客户型号',
                'value'=>$info->client_model,
                'length'=>$length
            ],
            [
                'label'=>'产品类型',
                'value'=>$info->product_category_name,
                'length'=>$length
            ],
            [
                'label'=>'定价时间',
                'value'=>$info->date_at,
                'length'=>$length
            ],
            [
                'label'=>'客户',
                'value'=>$info->client_name,
                'length'=>$length
            ],
            [
                'label'=>'工艺颜色',
                'value'=>$info->craft_color_name,
                'length'=>$length
            ],
            [
                'label'=>'材料用料',
                'value'=>$info->sole_material_name,
                'length'=>$length
            ],
            [
                'label'=>'单价',
                'value'=>$info->price,
                'length'=>$length
            ],
            [
                'label'=>'状态',
                'value'=>$this->status[$info->price_status],
                'length'=>$length
            ],
            [
                'label'=>'是否验收',
                'value'=>$this->check_arr[$info->is_check],
                'length'=>$length
            ],
        ];
        $reback = admin_url('delivery-price');
        return view('admin.common.show', compact('title','info','reback'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new DeliveryPrice(), function (Form $form) {
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
                $form->text('price')->required();
            });
            $form->column(6, function (Form $form) {
                $form->select('company_model_id')
                    ->required();
                $form->hidden('company_model');
                $form->select('craft_color_id')->required();
                $form->hidden('craft_color_name');
                $form->selectResource('product_category_id')
                    ->path('dialog/product-category') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return ProductCategory::findOrFail($v)->pluck('product_category_name', 'id');
                    })->required();
                $form->hidden('product_category_name');
                $form->hidden('price_at');

                $form->text('personnel_name')->readonly();
                $form->html(function () {
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
})
</script>
AD;
                });
            });

            $form->footer(function ($footer) {
                $footer->disableViewCheck();
                $footer->disableEditingCheck();
                $footer->disableCreatingCheck();
            });
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->submitted(function (Form $form) {
                $client_id = $form->client_id;
                $company_model_id = $form->company_model_id;
                $kehu_model = $form->client_model_id;
                $craft_color_id = $form->craft_color_id;
                $query = DeliveryPrice::where('client_id',$client_id)
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
        $delivery_price_info = DeliveryPrice::find($id);
        $personnel= Personnel::find($form->personnel_id);

        $delivery_price_info->client_name = $client->client_name;
        $delivery_price_info->client_model = $client_model->client_model_name;
        $delivery_price_info->company_model = $company_model->company_model_name;
        $delivery_price_info->product_category_name = $product_category->product_category_name;
        $delivery_price_info->sole_material_name = $sole_material->sole_material_name;
        $delivery_price_info->craft_color_name = $craft_color->craft_color_name;
        $delivery_price_info->personnel_name = $personnel->name;
        $delivery_price_info->date_at = Carbon::now();
        $delivery_price_info->price_at = Carbon::now();
        $delivery_price_info->save();
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
        $grid = new IFrameGrid(new DeliveryPrice());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()
            ->where('client_id',$request->client_id)
            ->where('client_model',$request->client_model)
            ->where('company_model',$request->company_model)
            ->where('craft_color_id',$request->craft_color_id)
            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('price');
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
            $filter->like('client_name')->width(3);
            $filter->like('client_model')->width(3);
            $filter->like('company_model')->width(3);
            $filter->like('craft_color_name')->width(3);
        });

        return $grid;
    }
}
