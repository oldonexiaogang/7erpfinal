<?php

namespace App\Admin\Controllers;

use App\Models\CompanyModelAndClient;
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
use Dcat\Admin\Controllers\AdminController;

class CompanyModelAndClientController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CompanyModelAndClient(), function (Grid $grid) {
            $grid->model()->orderBy('created_at','desc');
            $grid->column('client_id');
            $grid->column('company_model_id');
            $grid->column('craft_information_id');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $filter->equal('client_id')->width(2);
                $filter->equal('company_model_id')->width(2);
                $filter->equal('craft_information_id')->width(2);
            });
        });
    }

    /**
     * dec:api获取数据
     *  @param Request $request
     * author : happybean
     * date: 2020-04-19
     */
    public function apiIndex(Request $request)
    {
        $q = $request->get('q');
        $query = CompanyModelAndClient::query()->with('company_model');
        if($q){
            $result = $query->where('client_id', 'like', "%$q%");
        }
        $result = $query->select('company_model_id')->get();
        $show_result = $result->map(function (CompanyModelAndClient $data) {
            return ['id' => $data->company_model_id, 'text' => $data->company_model->company_model_name];
        });
        $last_result = assoc_unique($show_result,'id');

        return $last_result;
    }
}
