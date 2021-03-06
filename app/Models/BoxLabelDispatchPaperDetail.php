<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BoxLabelDispatchPaperDetail extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'box_label_dispatch_paper_detail';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
