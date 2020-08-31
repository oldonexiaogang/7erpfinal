<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'personnel';
    protected $fillable = [
        'id','department_id','personnel_no','position_id',
        'name','sex','nation','birthday_at',
        'work_at','come_at','address',
        'idcard','out_at','work_status','status',
        'remark'
    ];

    public function department(){
        return $this->belongsTo(Department::class,'department_id','id');
    }
    public function position(){
        return $this->belongsTo(Position::class,'position_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
