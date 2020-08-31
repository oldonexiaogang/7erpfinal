<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SoleMaterial extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sole_material';
    protected $fillable = [ 'sole_material_color_name','sole_material_color_id',
        'sole_material_name','description'];

    public function sole_material_color(){
        return $this->belongsTo(SoleMaterialColor::class,'sole_material_color_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
