<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RawMaterialStorage extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'raw_material_storage';

    protected $fillable = [
        'supplier_id','supplier_name','raw_material_product_information_id',
        'raw_material_product_information_no','raw_material_product_information_name',
        'raw_material_category_id','raw_material_category_name',
        'purchase_standard_id','purchase_standard_name',
        'color_id','color','unit_id','unit','price',
        'num','change_coefficient'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
