<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransitStorageOutVoidLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'transit_storage_out_void_log';
    
}
