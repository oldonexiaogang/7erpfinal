<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PlanListDetail extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'plan_list_detail';
    public function plan_list(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
