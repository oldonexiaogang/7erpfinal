<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CarftSkill extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'carft_skill';
    protected $fillable = [ 'carft_skill_name','description'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
