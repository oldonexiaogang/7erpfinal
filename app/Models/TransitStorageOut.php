<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransitStorageOut extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'transit_storage_out';
    protected $fillable = [
        'dispatch_id','dispatch_no','plan_list_id',
        'plan_list_no', 'type','style','storage_type','num',
        'log_user_id','log_user_name','company_model_id','company_model',
        'personnel_id','personnel_name','remark','out_date','status','is_void'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function plan_list(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }
}
