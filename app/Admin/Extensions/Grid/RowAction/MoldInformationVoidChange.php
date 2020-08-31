<?php

namespace App\Admin\Extensions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Dcat\Admin\Admin;
use App\Admin\Extensions\Forms\MoldInformationVoidChange as MoldInformationVoidChangeForm;

class MoldInformationVoidChange extends RowAction
{
    /**
     * @return string
     */
    protected $title = '修改作废状态';
    public function render()
    {
        ;
        $id = "mold-information-void-status-{$this->getKey()}";
        // 模态窗
        $this->modal($id);
        if($this->row->is_void=='1'){
            return <<<HTML
<span class="grid-expand" data-toggle="modal" data-target="#{$id}">
   <a href="javascript:void(0)" class="text-danger" style="text-decoration: underline">已作废</a>
</span>
HTML;
        }elseif($this->row->is_void=='0'){
            return <<<HTML1
<span class="grid-expand" data-toggle="modal" data-target="#{$id}">
   <a href="javascript:void(0)" class="text-success"  style="text-decoration: underline">正常</a>
</span>
HTML1;
        }else{
            return '-';
        }

    }
    protected function modal($id)
    {
        // 工具表单
        $form = new MoldInformationVoidChangeForm($this->getKey());
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
        <h4 class="modal-title">修改废除状态</h4>
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
