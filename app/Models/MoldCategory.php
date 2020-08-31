<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MoldCategory extends ParentTreeModel
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $titleColumn = 'mold_category_name';
    protected $fillable = ['parent_id', 'order', 'mold_category_name'];
    protected $table = 'mold_category';

    public function parent(){
        return $this->belongsTo(MoldCategory::class,'parent_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
