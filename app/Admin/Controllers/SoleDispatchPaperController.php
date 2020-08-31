<?php

namespace App\Admin\Controllers;

use App\Models\SoleDispatchPaper;
use App\Models\DispatchDetail;
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
use App\Services\PrinterService;
use App\Services\PaperService;
use App\Admin\Extensions\Grid\RowAction\SoleDispatchPaperCheck;
class SoleDispatchPaperController extends AdminController
{
    /**
     * dec:单个鞋底派工打印或着多个码打印
     * author : happybean
     * date: 2020-04-22
     */
    public function soleDispatchPrinter(Request $request){
        $id = $request->id;
        $no = $request->no;
        // 点击确认打印-插入数据
        $makepaper = new PaperService(DispatchDetail::class,$id,$no);
        $makepaper->makeSoleDispatchPaper();
        $printer = new PrinterService();
        return $printer->soleDispatchTable($id,$no);

    }

    /**
     * 仅仅打印
     * @param Request $request
     * @return mixed\
     */
    public function soleDispatchJustPrinter(Request $request){
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
        return Grid::make(new SoleDispatchPaper(), function (Grid $grid) {
            $model = new SoleDispatchPaper();
            $grid->model()->orderBy('created_at','desc');
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
                          title: "鞋底派工票据",
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
            $grid->is_void->action(SoleDispatchPaperCheck::class);
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
