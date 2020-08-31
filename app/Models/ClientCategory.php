<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ClientCategory extends ParentTreeModel
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $sortable = [
        'sort_when_creating' => true,
    ];
    protected $table = 'client_category';
    protected $titleColumn = 'name';
    protected $fillable = ['parent_id', 'order', 'name'];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
