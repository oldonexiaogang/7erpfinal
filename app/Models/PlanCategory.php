<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PlanCategory  extends ParentTreeModel
{
    use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'plan_category';
    protected $titleColumn = 'plan_category_name';
    protected $fillable = ['parent_id', 'order', 'plan_category_name'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
