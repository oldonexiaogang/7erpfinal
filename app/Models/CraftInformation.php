<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CraftInformation extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'craft_information';

    public function setSoleImageAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['sole_image'] = json_encode($pictures);
        }
    }

    public function getSoleImageAttribute($pictures)
    {
        return json_decode($pictures, true);
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
