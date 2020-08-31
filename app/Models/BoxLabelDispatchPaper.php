<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BoxLabelDispatchPaper extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'box_label_dispatch_paper';
    public function getDetialNum($id){
        $num = BoxLabelDispatchPaperDetail::where('box_label_dispatch_paper_id',$id)->sum('num');
        return $num;
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
