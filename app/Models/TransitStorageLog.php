<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransitStorageLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'transit_storage_log';

    protected $fillable=[
        'company_model_id','company_model','spec_id','spec','type','transit_storage_id','log_user_id','log_user_name',
        'from','in_num','out_num','storage'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
