<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransitStorage extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'transit_storage';

    protected $fillable=[
        'company_model_id','company_model','spec_id','spec','type','check_at','price','in_num',
        'out_num'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
