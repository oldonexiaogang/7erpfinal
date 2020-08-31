<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryPaper extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'delivery_paper';
    public function getDetialNum($id){
        $num = DeliveryPaperDetail::where('delivery_paper_id',$id)->sum('num');
        return $num;
    }
    public function plan_list(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
