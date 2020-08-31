<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DeliveryLogDetail extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'delivery_log_detail';

    public function delivery_log(){
        return $this->belongsTo(DeliveryLog::class,'delivery_log_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
