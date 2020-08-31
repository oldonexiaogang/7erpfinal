<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ClientSoleInformationVoidLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'client_sole_information_void_log';
    
}
