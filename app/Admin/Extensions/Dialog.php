<?php

namespace App\Admin\Extensions;

use Dcat\Admin\Admin;
use Dcat\Admin\Support\Helper;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class Dialog extends AbstractDisplayer
{

    protected $area = ['700px', '500px'];
    protected $title;

    /**
     * @param string $width
     * @param string $height
     *
     * @return $this
     */
    public function nodes($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        return $data;

    }
    public function display($callbackOrNodes = null)
    {
        $getParamsArr=[];
        if (is_array($callbackOrNodes) || $callbackOrNodes instanceof Arrayable) {
            $getParamsArr =  $this->nodes($callbackOrNodes);
        }elseif ($callbackOrNodes instanceof \Closure) {
            $getParamsArr = $callbackOrNodes->call($this->row, $this);
        }
        $this->setupScript();
        $val = $this->format($this->value);
        $linkurl='';
        $type='';
        if($getParamsArr['type']=='html'){
            $type='html';
            $linkurl= $getParamsArr['content'];
        }
        elseif($getParamsArr['type']=='url'){
            $type='url';
            $linkurl = $this->format($getParamsArr['url']);
        }elseif($getParamsArr['type']=='img'){
            $type='img';
            $linkurl= imgurl($getParamsArr['img']);
        }
        elseif($getParamsArr['type']=='text'){
            $type='text';
            $linkurl= $getParamsArr['content'];
        }
        if(isset($getParamsArr['width'])&&isset($getParamsArr['height'])){
            $this->area = [$getParamsArr['width'], $getParamsArr['height']];
        }
        if($getParamsArr['type']=='onlytext'){
            return <<<EOF
<a href="javascript:void(0)">
    {$getParamsArr['value']}
</a>
EOF;
        }
        if(isset($getParamsArr['value'])){
            return <<<EOF
<a href="javascript:void(0)" class="open-dialog-{$this->row->id}-{$this->getSelectorPrefix()}" data-url="{$linkurl}" 
 data-type="{$type}" data-val="{$val}" 
 data-width="{$this->area[0]}" data-height="{$this->area[1]}">
    {$getParamsArr['value']}
</a>
EOF;
        }else{
            return <<<EOF
<a href="javascript:void(0)" class="open-dialog-{$this->row->id}-{$this->getSelectorPrefix()}" 
data-url="{$linkurl}"  data-type="{$type}" data-val="{$val}" 
data-width="{$this->area[0]}" data-height="{$this->area[1]}">
    {$this->value}
</a>
EOF;
        }

    }

    protected function format($val)
    {
        return implode(',', Helper::array($val, true));
    }

    protected function getSelectorPrefix()
    {
        return $this->grid->getName().'_'.$this->column->getName();
    }

    protected function setupScript()
    {
        $title = $this->title ?: $this->column->getLabel();
        Admin::script(
            <<<JS
$('.open-dialog-{$this->row->id}-{$this->getSelectorPrefix()}').off('click').on('click', function () {
    var  val = $(this).data('val');
    var  linkurl = $(this).data('url');
    var  type = $(this).data('type');
    var  width = $(this).data('width');
    var  height = $(this).data('height');
    val = val ? String(val).split(',') : [];
    build(val);
    function build(val) {
        if(type=='url'){
            idx = layer.open({
                type:2,
                area: [width,height],
                content: linkurl,
                title: '{$title}',
            });
        }else if(type=='img'){
             idx = layer.open({
                type:1,
                area: [width,height],
                content: '<img src="'+linkurl+'"  style="width:580px;height:auto;display:block;margin:0px auto"/>',
                title: '{$title}',
            });
        }else if(type=='text'||type=='html'){
            idx = layer.open({
                type:1,
                area: [width,height],
                content:"<div style='margin-top:20px'>"+linkurl+"</div>",
                title: '{$title}',
            });
        }
        
        
        $(document).one('pjax:complete', function () { 
            layer.close(idx);
        });
    }
});
JS
        );
    }
}
