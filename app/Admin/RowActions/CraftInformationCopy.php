<?php

namespace App\Admin\RowActions;

use App\Models\CarftInformation;
use Carbon\Carbon;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CraftInformationCopy extends RowAction
{
    /**
     * @return string
     */
    protected $title = '<i class="feather icon-copy "></i>';
    protected $model;
    public function __construct(string $model = null)
    {
        $this->model = $model;
    }
    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // 获取当前行ID
        $id = $this->getKey();
        $model = $request->get('model');
        // 复制数据-鞋底data-detail
        $craft_information= $model::find($id);
        $craft_information=$craft_information->replicate();
        $craft_information->save();

        return $this->response()->success("复制成功")->redirect(admin_url('craft-information/'.$id.'/edit'));

    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要复制这行数据吗？",
            // 确认弹窗 content
            $this->row->company_model,
        ];
    }
    /**
     * 设置要POST到接口的数据
     *
     * @return array
     */
    public function parameters()
    {
        return [
            // 发送当前行 username 字段数据到接口
            'company_model' => $this->row->company_model,
            // 把模型类名传递到接口
            'model' => $this->model,
        ];
    }
}
