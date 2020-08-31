<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InjectMoldDispatchPaper extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'inject_mold_dispatch_paper';
    public function getDetialNum($id){
        $num = InjectMoldDispatchPaperDetail::where('inject_mold_dispatch_paper_id',$id)->sum('num');
        return $num;
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
