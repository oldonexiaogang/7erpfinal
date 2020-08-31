<?php

namespace App\Admin\Extensions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use App\Admin\Extensions\Forms\SoleWorkshopSubscribePaperCheck as SoleWorkshopSubscribePaperCheckForm;

class SoleWorkshopSubscribePaperCheck extends RowAction
{
    public function render()
    {
        $id = "purchase-check-{$this->getKey()}";
        // 模态窗
        $this->modal($id);
        $is_void = $this->row->is_void ;
        $void_status = config('plan.paper_void')[$is_void];
        if($is_void=='0'){

            return <<<HTML
<span class="grid-expand" data-toggle="modal" data-target="#{$id}">
   <a href="javascript:void(0)" style="text-decoration: underline">{$void_status}</a>
</span>
HTML;
        }else{
            return $void_status;
        }

    }
    protected function modal($id)
    {
        // 工具表单
        $form = new SoleWorkshopSubscribePaperCheckForm($this->getKey());
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
        <h4 class="modal-title">作废</h4>
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
