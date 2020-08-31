<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SoleMaterialColor extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sole_material_color';
    protected $fillable = [ 'sole_material_color_name'];

    public function sole_material(){
        return $this->hasMany(SoleMaterial::class,'sole_material_color_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
