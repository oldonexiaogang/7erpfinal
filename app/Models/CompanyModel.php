<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'company_model';
    protected $fillable = ['company_model_name', 'status'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
