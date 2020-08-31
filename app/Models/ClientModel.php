<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'client_model';
    protected $fillable = ['client_model_name', 'status','client_id'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
