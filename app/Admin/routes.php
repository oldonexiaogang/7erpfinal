<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('/home', 'HomeController@index');
    /**资料管理**/
    $router->resource('personnel', PersonnelController::class);//人事管理
    $router->resource('department', DepartmentController::class);//部门管理
    $router->resource('position', PositionController::class);//职位管理
    $router->resource('client-category', ClientCategoryController::class);//客户分类管理
    //api请求资料管理
    $router->get('api/personnel', 'PersonnelController@apiIndex');//人事选择
    $router->get('api/department', 'DepartmentController@apiIndex');
    $router->get('api/department/to/personnel', 'DepartmentController@apiIndexToPersonnel');
    //弹框 资料管理
    $router->get('dialog/personnel', 'PersonnelController@dialogIndex');//人事选择
    $router->get('dialog/department', 'DepartmentController@dialogIndex');//部门管理
    $router->get('dialog/position', 'PositionController@dialogIndex');//职位管理
    /**资料管理**/



    /**模具管理**/
    $router->get('mold-information/print', 'MoldInformationController@moldInformationPrinter');//鞋底派工单据打印
    $router->resource('mold-information', MoldInformationController::class);//模具资料管理
    $router->resource('mold-price', MoldPriceController::class);//模具价格管理
    $router->resource('mold-maker', MoldMakerController::class);//模具制造商管理
    $router->resource('mold-category', MoldCategoryController::class);//模具分类管理
    //api 模具管理
    $router->get('api/mold-category-parent', 'MoldCategoryController@apiParentIndex');
    $router->get('api/mold-category-child', 'MoldCategoryController@apiChildIndex');
    $router->get('api/mold-maker', 'MoldMakerController@apiIndex');//模具制造商
    $router->post('api/mold-price-search', 'MoldPriceController@apiSearchIndex');//模具制造价格
    //dialog 模具管理
    $router->get('dialog/mold-maker', 'MoldMakerController@dialogIndex');//模具制造商管理
    /**模具管理**/


    /**采购管理**/
    $router->resource('raw-material-category', RawMaterialCategoryController::class);//原材料类型
    $router->resource('unit', UnitController::class);//计量单位
    $router->resource('supplier', SupplierController::class);//供应商管理
    $router->resource('purchase-standard', PurchaseStandardController::class);//采购规格
    $router->resource('color', ColorController::class);//颜色管理
    $router->resource('raw-material-product-information', RawMaterialProductInformationController::class);//原材料产品资料管理
    $router->resource('inject-mold-price', InjectMoldPriceController::class);//注塑工价
    $router->get('dialog/inject-mold-price', 'InjectMoldPriceController@dialogIndex');//部门管理
    $router->get('dialog/color', 'ColorController@dialogIndex');//颜色管理



    //$router->resource('raw-material-storage', RawMaterialStorageController::class);//原材料库存
    $router->get('raw-material-storage', 'RawMaterialStorageController@index');
    $router->post('raw-material-storage', 'RawMaterialStorageController@storeH');
    $router->get('raw-material-storage/create', 'RawMaterialStorageController@create');
    $router->put('raw-material-storage/{id}', 'RawMaterialStorageController@updateH');
    $router->get('raw-material-storage/{id}/edit', 'RawMaterialStorageController@edit');
    $router->delete('raw-material-storage/{id}', 'RawMaterialStorageController@destroy');

    // $router->resource('sole-workshop-subscribe', SoleWorkshopSubscribeController::class);//鞋底车间申购
    $router->get('sole-workshop-subscribe', 'SoleWorkshopSubscribeController@index');
    $router->post('sole-workshop-subscribe', 'SoleWorkshopSubscribeController@storeH');
    $router->get('sole-workshop-subscribe/create', 'SoleWorkshopSubscribeController@create');

    //$router->resource('sole-workshop-subscribe-detail', SoleWorkshopSubscribeDetailController::class);//鞋底车间申购详情
    $router->get('sole-workshop-subscribe-detail', 'SoleWorkshopSubscribeDetailController@index');
   //鞋底车间申购详情打印预览
    $router->get('sole-workshop-subscribe-detail/preview',
        'SoleWorkshopSubscribeDetailController@printPreviewMultiIndex');//打印预览
    $router->get('sole-workshop-subscribe-detail/print',
        'SoleWorkshopSubscribePaperController@printer');//打印预览




//    $router->get('sole-workshop-subscribe-detail/create', 'SoleWorkshopSubscribeDetailController@create');
//    $router->post('sole-workshop-subscribe-detail', 'SoleWorkshopSubscribeDetailController@store');
    $router->put('sole-workshop-subscribe-detail/{id}', 'SoleWorkshopSubscribeDetailController@updateH');
    $router->get('sole-workshop-subscribe-detail/{id}/edit', 'SoleWorkshopSubscribeDetailController@edit');
    $router->delete('sole-workshop-subscribe-detail/{id}', 'SoleWorkshopSubscribeDetailController@destroy');





    //api 采购管理
    $router->get('api/unit', 'UnitController@apiIndex');
    $router->get('api/color', 'ColorController@apiIndex');
    $router->post('api/color', 'ColorController@apiIndex');
    $router->get('api/purchase-standard', 'PurchaseStandardController@apiIndex');

    //dialog 采购管理
    $router->get('dialog/raw-material-category', 'RawMaterialCategoryController@dialogIndex');//原材料类型
    $router->get('dialog/raw-material-product-information', 'RawMaterialProductInformationController@dialogIndex');//原材料类型
    $router->get('dialog/supplier', 'SupplierController@dialogIndex');//供应商

    /**采购管理**/






    //鞋底资料
    $router->resource('product-category', ProductCategoryController::class);//产品类型管理
    $router->resource('sole-material-color', SoleMaterialColorController::class);//鞋底用料颜色分类
    $router->resource('sole-material', SoleMaterialController::class);//鞋底用料管理
    $router->resource('craft-skill', CarftSkillController::class);//制作工艺管理

    //计划单复制
    $router->get('craft-information/copy', 'CraftInformationController@copyIndex');
    $router->get('craft-information/copy/{id}', 'CraftInformationController@copyData');
    $router->post('craft-information/copy', 'CraftInformationController@store');

    $router->resource('craft-information', CraftInformationController::class);//工艺单资料管理
    //工艺颜色管理
     $router->get('craft-color', 'CraftColorController@index');
    $router->post('craft-color', 'CraftColorController@storeH');
    $router->get('craft-color/create', 'CraftColorController@create');
    $router->put('craft-color/{id}', 'CraftColorController@update');
    $router->get('craft-color/{id}/edit', 'CraftColorController@edit');
    $router->delete('craft-color/{id}', 'CraftColorController@destroy');
    //规格明细管理
    $router->get('standard-detail', 'StandardDetailController@index');
    $router->post('standard-detail', 'StandardDetailController@storeH');
    $router->get('standard-detail/create', 'StandardDetailController@create');
    $router->put('standard-detail/{id}', 'StandardDetailController@update');
    $router->get('standard-detail/{id}/edit', 'StandardDetailController@edit');
    $router->delete('standard-detail/{id}', 'StandardDetailController@destroy');

    //api请求鞋底资料
    $router->get('api/sole-material-color', 'SoleMaterialColorController@apiIndex');
    $router->get('api/sole-material', 'SoleMaterialController@apiIndex');
    $router->get('api/product-category', 'ProductCategoryController@apiIndex');
    $router->post('api/craft-information-client-model', 'CraftInformationController@apiSearchIndex');

    $router->post('api/craft-color-by-client-model', 'CraftColorController@apiByClientModelIndex');

    //dialog 鞋底资料
    $router->get('dialog/sole-material', 'SoleMaterialController@dialogIndex');//鞋底用料
    $router->get('dialog/product-category', 'ProductCategoryController@dialogIndex');//产品类型
    $router->get('dialog/craft-skill', 'CarftSkillController@dialogIndex');//职位管理





    //计划管理
    $router->resource('client', ClientController::class);//客户管理
    $router->resource('plan-category', PlanCategoryController::class);//计划单类型管理
    //计划单复制
    $router->get('plan-list/copy', 'PlanListController@copyIndex');

    $router->get('plan-list/gather', 'PlanListController@gatherPage');
    $router->get('plan-list/copy/{id}', 'PlanListController@copyData');
    $router->post('plan-list/copy', 'PlanListController@store');
    $router->get('gather/left/export', 'PlanListController@gatherLeftExport');//汇总数据左边导出
    $router->get('gather/right/export', 'PlanListController@gatherRightExport');//
    $router->get('plan-list-delivery-paper', 'PlanListController@exportDeliveryPaper');


    $router->get('temp-plan-list/copy', 'TempPlanListController@copyIndex');
    $router->get('temp-plan-list/copy/{id}', 'TempPlanListController@copyData');
    $router->post('temp-plan-list/copy', 'TempPlanListController@store');

    $router->get('client-sole-information/copy', 'ClientSoleInformationController@copyIndex');
    $router->get('client-sole-information/copy/{id}', 'ClientSoleInformationController@copyData');
    $router->post('client-sole-information/copy', 'ClientSoleInformationController@store');




    $router->resource('plan-list', PlanListController::class);//计划单管理
    $router->resource('temp-plan-list', TempPlanListController::class);//临时计划单管理
    $router->resource('client-sole-information', ClientSoleInformationController::class);//客户鞋底管理
    $router->get('dialog/client', 'ClientController@dialogIndex');//职位管理
    $router->get('dialog/delivery-price', 'DeliveryPriceController@dialogPriceIndex');//职位管理
    $router->get('api/client', 'ClientController@apiIndex');
    $router->post('api/client', 'ClientController@apiIndex');

    $router->get('api/plan-category', 'PlanCategoryController@apiIndex');
    $router->post('api/plan-list-load-client-sole', 'ClientSoleInformationController@planListLoadClientSole');//计划单中调出客户鞋底资料
    $router->post('api/client-personnel', 'ClientController@apiGetPersonnelIndex');
    //计划单批量打印预览
    $router->get('plan-list-to-dispatch/preview', 'PlanListController@multiPlanListDispatchPreview');




    //dialog 计划管理






    //隐藏菜单
    $router->get('raw-material-storage-log', 'RawMaterialStorageLogController@index');//原材料仓库记录
    $router->get('company-model', 'CompanyModelController@index');//雷力型号管理
    $router->get('client-model', 'ClientModelController@index');//雷力型号管理
    $router->get('company-model-and-client', 'CompanyModelAndClientController@index');//雷力型号管理
    //弹框 隐藏菜单
    $router->get('dialog/company-model', 'CompanyModelController@dialogIndex');//部门管理
    $router->get('dialog/client-model', 'ClientModelController@dialogIndex');//部门管理
    //隐藏菜单 api
    $router->get('api/company-model-and-client', 'CompanyModelAndClientController@apiIndex');
    $router->get('api/company-model', 'CompanyModelController@pureApiIndex');
    $router->get('api/client-model', 'ClientModelController@pureApiIndex');
    $router->post('api/company-model',
        'CompanyModelController@pureApiIndex');




    //派工管理

    //点击派工管理弹出页面
    $router->get('dispatch/{id}', 'DispatchController@planListDetail');
    //鞋底派工
    $router->post('sole-dispatch/create', 'DispatchController@soleStoreH');
    $router->get('sole-dispatch/create', 'DispatchController@soleCreateH');
    $router->get('sole-dispatch/create/{id}', 'DispatchController@soleCreateH');
    //鞋底派工记录
    $router->get('sole-dispatch', 'DispatchController@soleIndex');//鞋底派工
    $router->get('sole-dispatch-log', 'DispatchController@soleLogIndex'); //鞋底派工单记录
    $router->get('plan-list-diapatch/{id}', 'PlanListController@dispatchDetail');//派工详情

    //箱标派工
    $router->post('box-label-dispatch/create', 'DispatchController@boxLabelStoreH');
    $router->get('box-label-dispatch/create', 'DispatchController@boxLabelCreateH');
    $router->get('box-label-dispatch/create/{id}', 'DispatchController@boxLabelCreateH');
    $router->get('box-label-dispatch', 'DispatchController@boxLabelIndex');
    $router->get('box-label-dispatch-log', 'DispatchController@boxLabelLogIndex'); //鞋底派工单记录

    //注塑派工
    $router->post('inject-mold-dispatch/create', 'DispatchController@injectMoldStoreH');
    $router->get('inject-mold-dispatch/create', 'DispatchController@injectMoldCreateH');
    $router->get('inject-mold-dispatch/create/{id}', 'DispatchController@injectMoldCreateH');
    $router->get('inject-mold-dispatch', 'DispatchController@injectMoldIndex');
    $router->get('inject-mold-dispatch-log', 'DispatchController@injectMoldLogIndex'); //鞋底派工单记录
    //票据
     $router->get('sole-dispatch-paper', 'SoleDispatchPaperController@index');//鞋底派工单据
    $router->get('sole-dispatch/print', 'SoleDispatchPaperController@soleDispatchPrinter');//鞋底派工单据打印
    $router->get('sole-dispatch/just/print', 'SoleDispatchPaperController@soleDispatchJustPrinter');//鞋底派工单据打印

    $router->get('box-label-dispatch-paper', 'BoxLabelDispatchPaperController@index');//箱标派工单据
    $router->get('box-label-dispatch/print', 'BoxLabelDispatchPaperController@boxLabelDispatchPrinter');//箱标派工单据打印
    $router->get('box-label-dispatch/just/print', 'BoxLabelDispatchPaperController@boxLabelDispatchJustPrinter');//箱标打印

    $router->get('inject-mold-dispatch-paper', 'InjectMoldDispatchPaperController@index');//箱标派工单据
    $router->get('inject-mold-dispatch/print', 'InjectMoldDispatchPaperController@injectMoldDispatchPrinter');//箱标派工单据打印
    $router->get('inject-mold-dispatch/just/print', 'InjectMoldDispatchPaperController@injectMoldDispatchJustPrinter');//箱标打印


    //仓库
    //中转仓入库管理
    $router->get('transit-storage-in-manage', 'TransitStorageController@inManageIndex');//中转入库列表
    //中转仓入库信息
    $router->get('transit-storage-in-print', 'TransitStorageInController@printindex');//入库打印
    $router->post('transit-storage-in', 'TransitStorageInController@storeH');
    $router->post('transit-storage-in/create', 'TransitStorageInController@storeH');
    $router->get('transit-storage-in/create', 'TransitStorageInController@createH');
    $router->get('transit-storage-in/{id}', 'TransitStorageInController@detail');
    $router->get('transit-storage-in/create/{id}', 'TransitStorageInController@createH');
    $router->post('transit-storage-in/{id}', 'TransitStorageInController@updateH');
    $router->get('transit-storage-in', 'TransitStorageInController@index');
    $router->get('transit-storage-in/{id}/edit', 'TransitStorageInController@editH');
   // $router->delete('transit-storage-in/{id}', 'TransitStorageInController@delete');

    //中转仓出库管理
    $router->get('transit-storage-out-manage', 'TransitStorageController@outManageIndex');//箱标打印
    $router->get('transit-storage-count', 'TransitStorageController@countIndex');//箱标打印
    $router->get('transit-storage-log', 'TransitStorageController@outInIndex');//箱标打印
    $router->get('transit-storage-count/{id}', 'TransitStorageController@countDetail');
    //中转仓出库信息
    $router->post('transit-storage-out/{id}', 'TransitStorageOutController@updateH');
    $router->post('transit-storage-out', 'TransitStorageOutController@storeH');
    $router->post('transit-storage-out/create', 'TransitStorageOutController@storeH');
    $router->get('transit-storage-out/create', 'TransitStorageOutController@createH');
    $router->get('transit-storage-out/{id}', 'TransitStorageOutController@detail');
    $router->get('transit-storage-out/create/{id}', 'TransitStorageOutController@createH');
    $router->get('transit-storage-out', 'TransitStorageOutController@index');
    $router->get('transit-storage-out/{id}/edit', 'TransitStorageOutController@editH');
    $router->delete('transit-storage-out/{id}', 'TransitStorageOutController@destroy');

    //delivery-log-by-plan
    //发货管理
    $router->resource('delivery-price', DeliveryPriceController::class);//发货单价
    $router->get('delivery-log-by-plan/{id}', 'DeliveryController@planIndex');//计划单的发货记录
    $router->get('delivery-log', 'DeliveryController@deliveryIndex');//计划单的发货记录
    $router->get('delivery', 'DeliveryController@index');//成品发货管理
    $router->get('delivery/create', 'DeliveryController@createH');//成品发货记录
    $router->get('delivery/{id}', 'DeliveryController@detail');//成品发货管理
    $router->get('delivery-count', 'DeliveryController@deliveryCount');//成品发货管理
    $router->post('delivery', 'DeliveryController@deliverySave');//成品发货保存

    $router->get('delivery-paper', 'DeliveryPaperController@index');//鞋底派工单据
    $router->get('delivery/print', 'DeliveryPaperController@deliveryPrinter');//鞋底派工单据打印
    $router->get('delivery/just/print', 'DeliveryPaperController@deliveryJustPrinter');//鞋底派工单据打印


    $router->get('sole-workshop-subscribe-paper', 'SoleWorkshopSubscribePaperController@index');//原材料入库单
    $router->get('sole-workshop-subscribe/just/print', 'SoleWorkshopSubscribePaperController@justPrinter');//原材料入库单


    $router->get('raw-material-storage-out/preview',
        'RawMaterialStorageOutController@printPreviewMultiIndex');//打印预览
    $router->get('raw-material-storage-out/print',
        'RawMaterialStorageOutPaperController@printer');//打印预览
    $router->resource('raw-material-storage-out', RawMaterialStorageOutController::class);//鞋底车间申购
    $router->get('raw-material-storage-out-paper', 'RawMaterialStorageOutPaperController@index');//原材料入库单
    $router->get('raw-material-storage-out/just/print', 'RawMaterialStorageOutPaperController@justPrinter');//原材料入库单


});
