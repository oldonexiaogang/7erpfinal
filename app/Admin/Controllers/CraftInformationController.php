<?php

namespace App\Admin\Controllers;

use App\Models\Color;
use App\Models\CraftInformation;
use App\Models\CompanyModelAndClient;
use App\Models\CompanyModel;
use App\Models\ClientModel;
//use App\Admin\RowActions\CraftInformationCopy;
use App\Admin\Extensions\Grid\RowAction\CraftInformationCopy;
use App\Models\Client;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Row;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Controllers\AdminController;


class CraftInformationController extends AdminController
{

    protected $controller_name = 'craft-information';
    /**
     * 复制首页跳转
     */
    public function copyIndex(){
        return  redirect(admin_url("craft-information"));
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
        $craft_info = CraftInformation::find($id);

        return Form::make(new CraftInformation(), function (Form $form) use($craft_info) {
            $form->column(6, function (Form $form) use($craft_info){
                $form->date('date_at')->default(Carbon::now()->toDateString());
                $form->text('company_model')->value($craft_info->company_model);
                $form->text('sole_material_demand')->value($craft_info->sole_material_demand);
            });
            $form->column(6, function (Form $form) use($craft_info){
                $form->selectResource('client_id')
                    ->path('dialog/client') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
                    })->value($craft_info->client_id);
                $form->hidden('client_name')->value($craft_info->client_name);
                $form->text('client_model')->value($craft_info->client_model);
                $form->text('carft_type_name')->value($craft_info->carft_type_name);
            });
            $form->column(12, function (Form $form) use($craft_info){
                $form->multipleImage('sole_image')
                    ->autoUpload()->uniqueName()->width(10,1)->value($craft_info->sole_image);
                $form->textarea('remark')
                    ->width(10,1)->value($craft_info->remark);
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

            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                $form->old_company_model='';
                $form->old_client_model='';
                //检测重复
                $num = CraftInformation::where('client_id',$form->client_id)
                    ->where('company_model',$form->company_model)
                    ->where('client_model',$form->client_model)
                    ->where('sole_material_demand',$form->sole_material_demand)
                    ->where('carft_type_name',$form->carft_type_name)
                    ->count();
                if($num>0){
                    return $form->error('该工艺单已经存在');
                }
            });
            $form->saved(function (Form $form, $result) {
                $companyModel = new CompanyModel();
                $clientModel = new ClientModel();
                $craftinformationModel = new CraftInformation();
                $id = $form->getKey();
                $company_model = $form->company_model;
                $client_model = $form->client_model;
                $old_company_model = $form->old_company_model;
                $old_client_model = $form->old_client_model;
                if(!$form->client_name){
                    $client_id = $form->client_id;
                    $client = Client::find($client_id);
                    $craftinformationModel::where('id',$id)->update([
                        'client_name'=>$client->client_name
                    ]);
                }
                if ($form->isCreating()) {
                    //检测雷力型号
                    $num  =$companyModel::where('company_model_name',$company_model)->count();
                    if($num==0){
                        $companyModel::create([
                            'company_model_name'=>$company_model,
                            'client_id'=>$form->client_id,
                            'status'=>1
                        ]);
                    }
                    //检测雷力型号
                    $client_num  =$clientModel->where('client_model_name',$client_model)->count();
                    if($client_num==0){
                        $clientModel::create([
                            'client_model_name'=>$client_model,
                            'client_id'=>$form->client_id,
                            'status'=>1
                        ]);
                    }
                }
                //客户雷力型号中间表
                $company_model  =$companyModel::where('company_model_name',$company_model)->first();
                $middleinfo = CompanyModelAndClient::where('client_id',$form->client_id)
                    ->where('craft_information_id',$id)->first();
                if(!$middleinfo){
                    CompanyModelAndClient::create([
                        'company_model_id'=>$company_model->id,
                        'client_id'=>$form->client_id,
                        'craft_information_id'=>$id
                    ]);
                }else{
                    if($middleinfo->company_model_id !=$company_model->id){
                        $middleinfo->company_model_id = $company_model->id;
                        $middleinfo->save();
                    }
                }
            });
        });
    }

    /**
     * 列表
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $is_dialog = request()->dialog;

        if($is_dialog){
            $field= request()->field;
            return $content->body(function (Row $row) use($field){
                $row->column(12, $this->iFrameGrid($field));
            });
        }else{
            return $content
                ->title($this->title())
                ->description($this->description()['index'] ?? trans('admin.list'))
                ->body($this->grid());
        }

    }
    /**
     * 列表数据
     */
    protected function grid()
    {
        return Grid::make(new CraftInformation(), function (Grid $grid) {
            $controller_name = $this->controller_name ;
            $grid->model()->orderBy('created_at','desc');
            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions(config('plan.dialog.width'), config('plan.dialog.height'));
            $grid->column('date_at','日期')->display(function() {
                return date('Y-m-d',strtotime($this->date_at));
            });
            $grid->company_model;
            $grid->client_model;
            $grid->client_name;
            $grid->column('copy','复制')->display(function () use($controller_name){
                $url= admin_url($controller_name.'/copy/'.$this->id);
                Form::dialog('修改',$url)
                    ->click('#copy_form_craft_information_'.$this->id)
                    ->url($url)
                    ->width('900px')
                    ->height('650px')
                    ->error(
                        <<<JS
if(response.status){
     setTimeout(function (){
       window.parent.location.reload()
   },500)
}
JS
                    );

                return "<a class='text-info' id='copy_form_craft_information_".$this->id."' >
<i class=\"feather icon-copy grid-action-icon\"></i></a>";

            });
            $grid->column('view','查看')->dialog(function () use($controller_name){
                return ['type'=>'url',
                        'url'=>admin_url($controller_name.'/'.$this->id.'?dialog=1'),
                        'width'=>config('plan.dialog.width'),
                        'height'=>config('plan.dialog.height'),
                        'value'=>'<i class="feather icon-search grid-action-icon"></i>'
                ];
            });
            $grid->column('oprateion','操作')->display(function () use($controller_name){
                $url= admin_url($controller_name.'/'.$this->id.'/edit');
                Form::dialog('修改',$url)
                    ->click('#edit_form_craft_infomation'.$this->id)
                    ->url($url)
                    ->width('900px')
                    ->height('650px');
                return "<a class='text-info' id='edit_form_craft_infomation".$this->id."' >
<i class=\"feather icon-edit grid-action-icon\"></i></a>";

            });

            $grid->column('delete','删除')->display(function () use($controller_name){
                if($this->check!=2){
                    return '<a href="javascript:void(0);" data-url="'.admin_url($controller_name.'/'.$this->id).'" data-action="delete">
                            <i class="feather icon-trash grid-action-icon"></i>
                        </a>';
                }else{
                    return '-';
                }
            });
            $grid->header(function ($query) use($controller_name){
                return '<div style="position:absolute;top:-30px;left:60px;">
                    <a href="javascript:void(0)" data-name="" class="btn btn-primary btn-sm
                    btn-mini  " data-action="batch-delete"
                    data-url="'.admin_url($controller_name).'">批量删除</a></div>';
            });
            $grid->withBorder();
            $grid->disableFilterButton();
            $grid->disableDeleteButton();
            //$grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->disableViewButton();
            $grid->disableActions();
            $grid->disableBatchDelete();
            $grid->toolsWithOutline(false);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->like('company_model')->width(2);
                $filter->like('client_model')->width(2);
//                $filter->equal('client_model')
//                    ->selectResource('dialog/kehus')
//                    ->options(function ($v) { // options方法用于显示已选中的值
//                        if (!$v) return $v;
//                        return Kehu::findOrFail($v)->pluck('name', 'id');
//                    })->width(2);
                $filter->between('date_at')->date()->width(3);
            });
        });
    }

    /**
     *详情
     */
    protected function detail($id)
    {
        $is_dialog = request()->dialog?:0;
        $carftinformation = CraftInformation::findOrFail($id);
        $length=4;
        $title = "工艺单资料";
        $info=[
            [
                'label'=>'详细说明',
                'value'=>$carftinformation->remark,
                'length'=>12
            ],
            [
                'label'=>'鞋底照片',
                'value'=>$carftinformation->sole_image,
                'length'=>'img'
            ],
        ];
        $reback = admin_url($this->controller_name);

        return view('admin.common.show', compact('title','info','reback','is_dialog'));
    }

    /**
     * 添加或修改表单
     */
    protected function form()
    {
        return Form::make(new CraftInformation(), function (Form $form) {
            $form->column(6, function (Form $form) {
                $form->date('date_at')->default(Carbon::now()->toDateString());
                $form->text('company_model');
                if($form->isEditing()||$form->isDeleting()){
                    $old_company_name = CraftInformation::find($form->getKey());
                    $form->hidden('old_company_model')->value($old_company_name->company_model);
                }
                $form->text('sole_material_demand');
            });
            $form->column(6, function (Form $form) {
                $form->selectResource('client_id')
                    ->path('dialog/client') // 设置表格页面链接
                    ->options(function ($v) { // 显示已选中的数据
                        if (!$v) return $v;
                        return Client::findOrFail($v)->pluck('client_name', 'id');
                    });
                $form->hidden('client_name');
                $form->text('client_model');
                if($form->isEditing()||$form->isDeleting()){
                    $old_client_name = CraftInformation::find($form->getKey());
                    $form->hidden('old_client_model')->value($old_client_name->client_model);
                }
                $form->text('carft_type_name');
            });
            $form->column(12, function (Form $form) {
                $form->multipleImage('sole_image')->oneline(true)
                    ->autoUpload()->uniqueName()->width(10,1);
                $form->textarea('remark')->oneline(true)->width(10,1);
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

            $form->saving(function (Form $form) {
                $form->deleteInput('_token');
                if ($form->isEditing()||$form->isDeleting()&&$form->company_model!=$form->old_company_model) {
                    $form->old_company_model = $form->old_company_model;
                }else{
                    $form->old_company_model='';
                }
                if ($form->isEditing()||$form->isDeleting()&&$form->client_model!=$form->old_client_model) {
                    $form->old_client_model = $form->old_client_model;
                }else{
                    $form->old_client_model='';
                }
                //检测重复
                $query= CraftInformation::where('client_id',$form->client_id)
                    ->where('company_model',$form->company_model)
                    ->where('client_model',$form->client_model)
                    ->where('sole_material_demand',$form->sole_material_demand)
                    ->where('carft_type_name',$form->carft_type_name);
                if ($form->isEditing()){
                    $id = $form->getKey();
                    $query->where('id','!=',$id);
                }
                $num = $query->count();
                if($num>0){
                    return $form->error('该工艺单已经存在');
                }

            });
            $form->saved(function (Form $form, $result) {
                $companyModel = new CompanyModel();
                $clientModel = new ClientModel();
                $craftinformationModel = new CraftInformation();
                $id = $form->getKey();
                $company_model = $form->company_model;
                $client_model = $form->client_model;
                $old_company_model = $form->old_company_model;
                $old_client_model = $form->old_client_model;
                if(!$form->client_name){
                    $client_id = $form->client_id;
                    $client = Client::find($client_id);
                    $craftinformationModel::where('id',$id)->update([
                        'client_name'=>$client->client_name
                    ]);
                }
                if ($form->isCreating()) {
                    //检测雷力型号
                    $num  =$companyModel::where('company_model_name',$company_model)->count();
                    if($num==0){
                        $companyModel::create([
                            'company_model_name'=>$company_model,
                            'client_id'=>$form->client_id,
                            'status'=>1
                        ]);
                    }
                    //检测雷力型号
                    $client_num  =$clientModel->where('client_model_name',$client_model)->count();
                    if($client_num==0){
                        $clientModel::create([
                            'client_model_name'=>$client_model,
                            'client_id'=>$form->client_id,
                            'status'=>1
                        ]);
                    }
                }
                if (($form->isEditing()||$form->isDeleting())&&($company_model!=$old_company_model)) {

                    $num  =$craftinformationModel::where('company_model',$old_company_model)
                        ->where('id','!=',$id)
                        ->count();
                    $company_model_num  =$companyModel::where('company_model_name',$company_model)->first();
                    $old_companyinfo = $companyModel->where('company_model_name',$old_company_model)->first();
                    if(!$company_model_num){
                        $companyModel::create([
                            'company_model_name'=>$company_model,
                            'status'=>1
                        ]);
                    }else{
                        $company_model_num->status=1;
                        $company_model_num->save();
                    }



                    if($num==0&&$old_companyinfo){
                        $old_companyinfo->status='0';
                        $old_companyinfo->save();
                    }
                    $changeinfo = $craftinformationModel->find($id);
                    $changeinfo->old_company_model  = $company_model;
                    $changeinfo->save();

                }
                if (($form->isEditing()||$form->isDeleting())&&($client_model!=$old_client_model)) {

                    $client_num  =$craftinformationModel::where('client_model',$old_client_model)
                        ->where('client_id',$form->client_id)
                        ->where('id','!=',$id)
                        ->count();
                    $client_model_num  =$clientModel::where('client_model_name',$client_model)->first();
                    $old_clientinfo = $clientModel->where('client_model_name',$old_client_model)->first();
                    if(!$client_model_num){
                        $clientModel::create([
                            'client_model_name'=>$client_model,
                            'client_id'=>$form->client_id,
                            'status'=>1
                        ]);
                    }else{
                        $client_model_num->status=1;
                        $client_model_num->save();
                    }

                    if($client_num==0&&$old_clientinfo){
                        $old_clientinfo->status='0';
                        $old_clientinfo->save();
                    }
                    $changeinfo = $craftinformationModel->find($id);
                    $changeinfo->old_client_model  = $client_model;
                    $changeinfo->save();
                    //修改或添加客户雷力型号
                }
                //客户雷力型号中间表
                $company_model  =$companyModel::where('company_model_name',$company_model)->first();
                $middleinfo = CompanyModelAndClient::where('client_id',$form->client_id)
                    ->where('craft_information_id',$id)->first();
                if(!$middleinfo){
                    CompanyModelAndClient::create([
                        'company_model_id'=>$company_model->id,
                        'client_id'=>$form->client_id,
                        'craft_information_id'=>$id
                    ]);
                }else{
                    if($middleinfo->company_model_id !=$company_model->id){
                        $middleinfo->company_model_id = $company_model->id;
                        $middleinfo->save();
                    }
                }
            });
        });
    }

    /**
     * 搜索
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function apiClientCompanyModelIndex(Request $request)
    {
        $q = $request->get('q');
        $query = CraftInformation::query();
        if($q){
            $query=  $query->where('client_id',$q);
        }
        $result =  $query->get([ 'id', DB::raw('company_model as text')]);
        return $result;
    }

    /**
     * 搜索
     * @param Request $request
     * @return false|string
     */
    public function apiSearchIndex(Request $request){
        $client_id = $request->post('client_id');
        $company_model_id = $request->post('company_model_id');
        $company_model = CompanyModel::find($company_model_id);
        $craftinfo = CraftInformation::where('client_id',$client_id)
            ->where('company_model',$company_model->company_model_name)
            ->pluck('client_model');
        if($craftinfo){

            $clientmodel = ClientModel::whereIn('client_model_name',$craftinfo)
                ->where('client_id',$client_id)
                ->get([ 'id', DB::raw('client_model_name as text')]);
            if($clientmodel){
                return json_encode([
                    'code'=>200,
                    'msg'=>'获取成功',
                    'data'=>$clientmodel
                ]);
            }else{
                return json_encode([
                    'code'=>100,
                    'msg'=>'获取失败',
                ]);
            }
        }else{
            return json_encode([
                'code'=>100,
                'msg'=>'获取失败',
            ]);
        }

    }

    /**
     * dec:弹框展示
     * author : happybean
     * date: 2020-04-19
     */
    protected function iFrameGrid($field='')
    {
        $grid = new IFrameGrid(new CraftInformation());

        // 如果表格数据中带有 “name”、“title”或“username”字段，则可以不用设置
        $grid->model()
            ->orderBy('created_at','desc');
        $grid->rowSelector()->titleColumn('company_model');
        $grid->company_model;
        $grid->client_model;
        $grid->client_name;
        $grid->showActions();
        $grid->disableRefreshButton();
        $grid->disableEditButton();
        $grid->disableViewButton();
        $grid->disableRowSelector();
        $grid->withBorder();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use($field) {
            $create->action('craft-information');
            $create->hidden('client_name');
            $create->otherField($field);
            $create->date('date_at')->default(Carbon::now()->toDateString());
            $create->text('company_model');
            $env_prefix = getenv('ADMIN_ROUTE_PREFIX');
            $create->select('client_id')->options('/api/client');
            $create->text('client_model');
            $create->text('sole_material_demand');
            $create->text('carft_type_name');
            $create->text('remark');
        });
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel();
            $filter->expand();
            $filter->like('company_model')->width(3);
            $filter->like('client_model')->width(3);
            $filter->like('client_name')->width(3);
        });

        return $grid;
    }
}
