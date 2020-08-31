<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SoleWorkshopSubscribeDetail extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sole_workshop_subscribe_detail';
    protected $fillable = [
        'sole_workshop_subscribe_id','purcahse_standard_id',
        'purcahse_standard_name','total_price',
        'unit_id','change_coefficient',
        'unit_name','approval_num',
        'apply_num','storage_in_num',
        'is_void','check_user_id',
        'check_user_name','check_status',
        'price'
    ];
    public function sole_workshop_subscribe(){
        return $this->belongsTo(SoleWorkshopSubscribe::class,'sole_workshop_subscribe_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
