<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RawMaterialCategory extends ParentTreeModel
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'raw_material_category';
    protected $titleColumn = 'raw_material_category_name';
    protected $fillable = ['parent_id', 'order', 'raw_material_category_name','description'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
