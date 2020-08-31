<?php

namespace App\Admin\Extensions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

class CraftInformationCopy extends RowAction
{

    public function html()
    {
        $icon = 'feather icon-copy text-blue';

        return <<<HTML
<i class="{$this->getElementClass()} fa {$icon}"></i>
HTML;
    }

    public function handle(Request $request)
    {
        try {
            $id = $this->getKey();
            $class = $request->class;
            // 复制数据-鞋底data-detail
            $craft_information= $class::find($id);
            $craft_information=$craft_information->replicate();
            $craft_information->save();

            return $this->response()->success("success")->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要复制这行数据吗？",
            // 确认弹窗 content
           '',
        ];
    }
    public function parameters()
    {
        return [
            'class' => $this->modelClass(),
        ];
    }


    public function modelClass()
    {
        return get_class($this->parent->model()->eloquent()->repository()->eloquent());
    }
}