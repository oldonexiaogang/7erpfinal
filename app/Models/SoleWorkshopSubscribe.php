<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SoleWorkshopSubscribe extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sole_workshop_subscribe';

    protected $fillable = [
        'sole_workshop_subscribe_no','raw_material_product_information_no',
        'raw_material_product_information_id','raw_material_product_information_name',
        'raw_material_category_id','raw_material_category_name',
        'supplier_id','supplier_name',
        'price','color_id','color','total_num','subscribe_remark','subscribe_content',
        'apply_user_id','apply_user_name','date_at'
    ];
    public function sole_workshop_subscribe_details(){
        return $this->hasMany(SoleWorkshopSubscribeDetail::class,'sole_workshop_subscribe_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
