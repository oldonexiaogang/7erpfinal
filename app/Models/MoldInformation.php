<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MoldInformation extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'mold_information';

    public  function mold_category_parent(){
        return $this->belongsTo(MoldCategory::class,'mold_category_parent_id','id');
    }
    public  function mold_category_child(){
        return $this->belongsTo(MoldCategory::class,'mold_category_child_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
//    public function setImageAttribute($pictures)
//    {
//        if (is_array($pictures)) {
//            $this->attributes['image'] = json_encode($pictures);
//        }
//    }
//
//    public function getImageAttribute($pictures)
//    {
//        return json_decode($pictures, true);
//    }
}
