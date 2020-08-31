<?php

namespace App\Admin\Controllers;

use App\Models\RawMaterialStorageLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Controllers\AdminController;

class RawMaterialStorageLogController extends AdminController
{

    public function index(Content $content){
        $raw_material_storage_id= request()->get('raw_material_storage_id');
        return $content
            ->title('库存信息')
            ->row(function (Row $row) use($raw_material_storage_id){
                $row->column(12, $this->sGrid($raw_material_storage_id));
            });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function sGrid($raw_material_storage_id)
    {

        return Grid::make(new RawMaterialStorageLog(), function (Grid $grid) use($raw_material_storage_id){
            if($raw_material_storage_id){
                $grid->model()->where('raw_material_storage_id',$raw_material_storage_id)
                    ->orderBy('created_at','desc');
            }else{
                $grid->model()->orderBy('created_at','desc');
            }

            $grid->column('created_at','日期')->display(function (){
                return $this->created_at?date('Y-m-d',strtotime($this->created_at)):'';
            });
            $grid->raw_material_product_information_name;
            $grid->column('in','入库数')->display(function (){
                return $this->type=='in'?$this->num:0;
            });
            $grid->column('out','出库数')->display(function (){
                return $this->type=='out'?$this->num:0;
            });
            $grid->after_storage_num;
            $grid->disableRowSelector();
            $grid->disableFilterButton();
            $grid->disableRefreshButton();
            $grid->disableCreateButton();
            $grid->disableBatchDelete();
            $grid->disableActions();
            $grid->withBorder();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->between('created_at', '日期')->date()->width(4);
            });
        });
    }
}
