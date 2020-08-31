<?php

namespace App\Admin\Extensions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Dcat\Admin\Admin;
use App\Admin\Extensions\Forms\SoleWorkshopSubscribeCheck as SoleWorkshopSubscribeFormCheck;

class SoleWorkshopSubscribeCheck extends RowAction
{
    /**
     * @return string
     */
    protected $title = '审核';
    public function render()
    {
        ;
        $id = "check-{$this->getKey()}";
        // 模态窗
        $this->modal($id);
        if($this->row->check_status!='verify'){
            return <<<HTML
<span class="grid-expand" data-toggle="modal" data-target="#{$id}">
   <a href="javascript:void(0)">审核</a>
</span>
HTML;
        }else{
            return '-';
        }

    }
    protected function modal($id)
    {
        // 工具表单
        $form = new SoleWorkshopSubscribeFormCheck($this->getKey());
        // 刷新页面时移除模态窗遮罩层
        Admin::script('Dcat.onPjaxComplete(function () {
            $(".modal-backdrop").remove();
        }, true)');

        // 通过 Admin::html 方法设置模态窗HTML
        Admin::html(
            <<<HTML
<div class="modal fade" id="{$id}">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">审核</h4>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        {$form->render()}
      </div>
    </div>
  </div>
</div>
HTML
        );
    }
}
