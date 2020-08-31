<?php

namespace App\Admin\Extensions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Dcat\Admin\Admin;
use App\Admin\Extensions\Forms\SoleWorkshopSubscribeDetailCheckStatusChange
    as SoleWorkshopSubscribeDetailCheckStatusChangeForm;

class SoleWorkshopSubscribeDetailCheckStatusChange extends RowAction
{
    /**
     * @return string
     */
    protected $title = '修改审核状态';
    public function render()
    {
        ;
        $id = "sole-workshop-purchase-check-status-{$this->getKey()}";
        // 模态窗
        $this->modal($id);
        $style = getCheck($this->row->check_status);
        if(in_array($this->row->check_status,['verify','overrule','unreviewed','part']) && $this->row->storage_in_num==0 ){
            return <<<HTML
<span class="grid-expand" data-toggle="modal" data-target="#{$id}">
   <a href="javascript:void(0)" class="{$style['class']}" style="text-decoration: underline">{$style['text']}</a>
</span>
HTML;
        }else{
            return <<<HTML
<span class="{$style['class']}">{$style['text']}</span>
HTML;
        }

    }
    protected function modal($id)
    {
        // 工具表单
        $form = new SoleWorkshopSubscribeDetailCheckStatusChangeForm($this->getKey());
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
        <h4 class="modal-title">修改审核状态</h4>
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
