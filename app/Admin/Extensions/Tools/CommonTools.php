<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;

class CommonTools extends BatchAction
{
    public function actionScript(){
        $warning = __('请先选择数据');

        return <<<JS
function (data, target, action) { 
    var key = {$this->getSelectedKeysScript()}

    if (key.length === 0) {
        Dcat.warning('{$warning}');
        return false;
    }
    // 设置主键为复选框选中的行ID数组
    action.options.key = key;
}
JS;
    }
}