<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CompanyModelAndClient extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'company_model_and_client';
    protected $fillable = ['client_id', 'company_model_id','craft_information_id'];
    public function client(){
        return $this->belongsTo(Client::class,'client_id','id');
    }
    public function company_model(){
        return $this->belongsTo(CompanyModel::class,'company_model_id','id');
    }
    public function craft_information(){
        return $this->belongsTo(CraftInformation::class,'craft_information_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
