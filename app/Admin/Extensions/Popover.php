<?php

namespace App\Admin\Extensions;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class Popover extends AbstractDisplayer
{
    protected  $showTitle;
    protected  $showData;


    public function display($placement='bottom')
    {
        Admin::script("$('[data-toggle=\"popover\"]').popover()");
        return <<<EOT

<span  data-container="body"
    data-toggle="popover"
    data-content="{$placement}"
    data-placement="bottom"
    >
  {$this->value}
</span>
EOT;

    }
}