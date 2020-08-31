<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryDetail extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'delivery_detail';

    public function delivery_info(){
        return $this->belongsTo(Delivery::class,'delivery_id','id');
    }
    public function plan_list_info(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }
}
