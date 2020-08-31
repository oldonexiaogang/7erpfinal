<?php

namespace App\Admin\Controllers;

use App\Models\SoleDispatchPaperCheck;
use App\Models\DeliveryPaper;
use App\Models\DeliveryDetail;
use App\Models\DispatchDetail;
use App\Models\SoleDispatchPaper;
use App\Services\PaperService;
use App\Services\PrinterService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Admin\Extensions\Grid\RowAction\DeliveryPaperCheck;
class DeliveryPaperController extends AdminController
{
    /**
     * dec:单个鞋底派工打印或着多个码打印
     * author : happybean
     * date: 2020-04-22
     */
    public function deliveryPrinter(Request $request){
        $id = $request->id;
        $no = $request->no;
        // 点击确认打印-插入数据
        $makepaper = new PaperService(DeliveryDetail::class,$id,$no);
        $makepaper->makeDeliveryPaper();
        $printer = new PrinterService();
        return $printer->soleDispatchTable($id,$no);

    }

    /**
     * 仅仅打印
     * @param Request $request
     * @return mixed\
     */
    public function deliveryJustPrinter(Request $request){
        $no = $request->no;
        $printer = new PrinterService();

        return $printer->soleDispatchNoTable($no);
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new DeliveryPaper(), function (Grid $grid) {
            $model = new DeliveryPaper();
            $grid->model()->orderBy('created_at');
            $grid->column('created_at');
            $grid->column('no')->display(function (){
                $id = $this->id;
                return '<a href="javascript::void(0)" id="'.$id.'_paper"
                style="text-decoration: underline"
                data-url="'.admin_url('sole-dispatch/just/print?no='.$this->no).'" >'.
                    $this->no.'</a>
<script >
     $("#'.$id.'_paper").on("click",function (){
                        let url = $(this).attr("data-url")
                        layer.closeAll();
                         parent.layer.open({
                          type: 2,
                          title: "发货票据",
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


            $grid->plan_list_no;
            $grid->client_name;
            $grid->client_model;
            $grid->company_model;
            $grid->craft_color_name;
            $grid->sole_material_name;
            $grid->column('num')->display(function () use($model){
                //详情数量
                $num =  $model->getDetialNum($this->id);
                return is_float_number($num);
            });
            $grid->is_check->display(function (){
                return config('plan.paper_check')[$this->is_check];
            });
            $grid->is_void->action(DeliveryPaperCheck::class);
            $grid->void_reason;
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableBatchDelete();
            $grid->disableActions();
            $grid->withBorder();
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }
}
