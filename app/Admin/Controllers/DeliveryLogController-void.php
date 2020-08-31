<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryLog;
use App\Models\DeliveryLogDetail;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\IFrameGrid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Illuminate\Http\Request;
use Dcat\Admin\Controllers\AdminController;

class DeliveryLogController extends AdminController
{
    /**
     * dec: 已发货记录
     * @param $id
     * @param Request $request
     * @param Content $content
     * author : happybean
     * date: 2020-05-04
     */
    public function planIndex($id, Content $content)
    {
        return $content
            ->title('已发货记录')
            ->row(function (Row $row) use ($id) {
                $row->column(12, $this->planIndexGrid($id));
            });
    }

    protected function planIndexGrid($id)
    {
        return IFrameGrid::make(DeliveryLogDetail::with(['delivery_log']), function (Grid $grid) use ($id) {
            $grid->model()->whereHas('delivery_log',function ($q) use($id){
                $q->where('plan_list_id',$id);
            })
                ->orderBy('created_at', 'desc');
            $grid->created_at;
            $grid->column('plan_list_no')->display(function () {
                return $this->delivery_log['plan_list_no'];
            });
            $grid->column('client_name')->display(function () {
                return $this->delivery_log['client_name'];
            });
            $grid->column('craft_color_name')->display(function () {
                return $this->delivery_log['craft_color_name'];
            });
            $grid->column('company_model')->display(function () {
                return $this->delivery_log['company_model'];
            });
            $grid->column('spec')->display(function () {
                return $this->spec . ' 码';
            });
            $grid->num;
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableRefreshButton();
            $grid->withBorder();
        });
    }
}
