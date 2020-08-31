<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\RawMaterialStorageOutPaperCheckMulti;
use App\Models\RawMaterialStorageOut;
use App\Models\RawMaterialStorageOutPaper;
use App\Services\PaperService;
use App\Services\PrinterService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Admin\Extensions\Grid\RowAction\RawMsterailStorageOutPaperCheck;

class RawMaterialStorageOutPaperController extends AdminController
{
    /**
     * 原材料入库打印
     */
    public function printer(Request $request){
        $id = $request->id;
        $no = $request->no;
        // 点击确认打印-插入数据
        $makepaper = new PaperService(RawMaterialStorageOut::class,$id,$no);
        $makepaper->makeRawMaterialStorageOutPaper();
        $printer = new PrinterService();
        return $printer->rawMaterialStorageOutTable($id,$no);

    }

    /**
     * 仅仅打印
     * @param Request $request
     * @return mixed\
     */
    public function justPrinter(Request $request){
        $no = $request->no;
        $printer = new PrinterService();
        return $printer->rawMaterialStorageOutNoTable($no);
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RawMaterialStorageOutPaper(), function (Grid $grid) {
            $model = new RawMaterialStorageOutPaper();
            $grid->model()->orderBy('created_at','desc');
            $grid->column('created_at')->display(function (){
                return date('Y年m月d',strtotime($this->created_at));
            });
            $grid->column('no')->display(function (){
                $id = $this->id;
                return '<a href="javascript::void(0)" id="'.$id.'_paper"
                style="text-decoration: underline"
                data-url="'.admin_url('raw-material-storage-out/just/print?no='.$this->no).'" >'.
                    $this->no.'</a>
<script >
     $("#'.$id.'_paper").on("click",function (){
                        let url = $(this).attr("data-url")
                        layer.closeAll();
                         parent.layer.open({
                          type: 2,
                          title: "原材料出库票据",
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


            $grid->raw_material_product_information_name;
            $grid->column('num');
            $grid->is_check->display(function (){
                return config('plan.paper_check')[$this->is_check];
            });
            $grid->is_void->action(RawMsterailStorageOutPaperCheck::class);
            $grid->void_reason;
            $grid->batchActions(function ($batch) {
                $batch->add(new RawMaterialStorageOutPaperCheckMulti('批量验收'));
            });
            $grid->disableBatchDelete();
            $grid->withBorder();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableBatchDelete();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
            $grid->toolsWithOutline(false);
            $grid->paginate(15);
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->between('created_at')->date()->width(4);
                $filter->equal('is_check')->select(config('plan.paper_check'))->width(2);
                $filter->equal('is_void')->select(config('plan.paper_void'))->width(2);

            });
        });
    }
}
