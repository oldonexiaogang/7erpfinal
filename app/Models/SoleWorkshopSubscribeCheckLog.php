<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SoleWorkshopSubscribeCheckLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sole_workshop_subscribe_check_log';
    
}
