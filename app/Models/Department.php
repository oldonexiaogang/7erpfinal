<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Department extends ParentTreeModel
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'department';
    protected $titleColumn = 'department_name';
    protected $fillable = ['parent_id', 'order', 'department_name'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
