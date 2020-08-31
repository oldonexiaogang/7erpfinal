<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MoldPrice extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'mold_price';
    public function setImageAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['image'] = json_encode($pictures);
        }
    }

    public function getImageAttribute($pictures)
    {
        return json_decode($pictures, true);
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
