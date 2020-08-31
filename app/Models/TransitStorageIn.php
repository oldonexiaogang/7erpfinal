<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransitStorageIn extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'transit_storage_in';

    protected $fillable = [
        'dispatch_id','dispatch_detail_id','dispatch_no','plan_list_id',
        'plan_list_no', 'type','style','storage_type','count_type','all_num',
        'log_user_id','log_user_name','company_model_id','company_model','spec_id','spec',
        'personnel_id','personnel_name','remark','storage_in_date','inject_mold_price_id',
        'inject_mold_price'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function dispatch_info(){
        return $this->belongsTo(Dispatch::class,'dispatch_id','id');
    }
}
