<?php

namespace App\Services;
use App\Models\SoleWorkshopSubscribeLog;

class SoleWorkshopSubscribeLogService
{
    public function __construct()
    {
        $this->logmodel = new SoleWorkshopSubscribeLog();
    }

    /**
     * 单条记录
     * @param $data
     * @return bool
     */
    public function insertOne($data){
        $this->logmodel->check_user_id = $data['check_user_id'];
        $this->logmodel->check_user_name = $data['check_user_name'];
        $this->logmodel->sole_workshop_subscribe_detail_id = $data['sole_workshop_subscribe_detail_id'];
        $this->logmodel->sole_workshop_subscribe_id = $data['sole_workshop_subscribe_id'];
        $this->logmodel->approval_num = $data['approval_num'];
        $this->logmodel->reason = $data['reason'];
        $res =  $this->logmodel->save();
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 多条记录
     * @param $data
     * @return bool
     */
    public function insertAll($data){
        $res = $this->logmodel->insert($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}
