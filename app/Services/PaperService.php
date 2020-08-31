<?php

namespace App\Services;

use App\Models\Dispatch;
use App\Models\BoxLabelDispatchPaper;
use App\Models\BoxLabelDispatchPaperDetail;
use App\Models\InjectMoldDispatchPaperDetail;
use App\Models\DeliveryPaper;
use App\Models\InjectMoldDispatchPaper;
use App\Models\RawMaterialStorage;
use App\Models\RawMaterialStorageOutPaper;
use App\Models\SoleDispatchPaper;
use App\Models\SoleDispatchPaperDetail;
use App\Models\Dispatch as DispatchModel;
use App\Models\DispatchDetail;
use App\Models\PlanListDetail;
use App\Models\PlanList;
use App\Models\SoleWorkshopSubscribePaper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaperService
{
    public function __construct($model,$id,$no)
    {
        $this->model =new $model;
        $this->id = $id;
        $this->no = $no;
    }
    /**
     * 原材料出库
     * @param $data
     * @return bool
     */
    public function makeRawMaterialStorageOutPaper(){
        $idarr = explode(',',$this->id);
        $num = RawMaterialStorageOutPaper::where('no',$this->no)->count();
        if($num>0){
            return false;
        }
        $info = $this->model->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            foreach ($info as $k=>$v){
                $insert_data[$k]['no']=$this->no;
                $insert_data[$k]['raw_material_storage_out_id']=$v->id;
                $insert_data[$k]['raw_material_product_information_name']=$v->raw_material_product_information_name;
                $insert_data[$k]['num']=$v->num;
                $insert_data[$k]['created_at']=$now;
                $insert_data[$k]['updated_at']=$now;
                $insert_data[$k]['is_check']=0;
                $insert_data[$k]['is_void']=0;
                $v->is_print='1';
                $v->save();
            }
            RawMaterialStorageOutPaper::insert($insert_data);
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    /**
     * dec:鞋底车间申购
     * author : happybean
     * date: 2020-04-25
     */
    public function makeSoleWorkshopSubscribeDetailPaper(){
        $idarr = explode(',',$this->id);
        $num = SoleWorkshopSubscribePaper::where('no',$this->no)->count();
        if($num>0){
            return false;
        }
        $info = $this->model->with('sole_workshop_subscribe')->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            foreach ($info as $k=>$v){
                $insert_data[$k]['no']=$this->no;
                $insert_data[$k]['sole_workshop_subscribe_id']=$v->id;
                $insert_data[$k]['raw_material_product_information_name']=$v->sole_workshop_subscribe->raw_material_product_information_name;
                $insert_data[$k]['raw_material_product_information_no']=$v->sole_workshop_subscribe->raw_material_product_information_no;
                $insert_data[$k]['raw_material_category_name']=$v->sole_workshop_subscribe->raw_material_category_name;
                $insert_data[$k]['sole_workshop_subscribe_no']=$v->sole_workshop_subscribe->sole_workshop_subscribe_no;
                $insert_data[$k]['num']=$v->apply_num;
                $insert_data[$k]['purcahse_standard_name']=$v->purcahse_standard_name;
                $insert_data[$k]['apply_user_name']=$v->sole_workshop_subscribe->apply_user_name;
                $insert_data[$k]['is_check']='0';
                $insert_data[$k]['is_void']='0';
                $insert_data[$k]['created_at']=$now;
                $insert_data[$k]['updated_at']=$now;
                $v->is_print=1;
                $v->save();
            }
            SoleWorkshopSubscribePaper::insert($insert_data);
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    /**
     * 鞋底按照多个派工单打印的
     */
    public function makeSoleDispatchPaper(){
        $idarr = explode(',',$this->id);
        $info = $this->model->with(['dispatch_info'])->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        $num = SoleDispatchPaper::where('no',$this->no)->count();

        if($num>0){
            return false;
        }
        DB::beginTransaction(); //开启事务
        try{
            $insert_data['no']=$this->no;
            $insert_data['plan_list_id']=$info[0]->plan_list_id;
            $insert_data['plan_list_no']=$info[0]->dispatch_info->plan_list_no;
            $insert_data['dispatch_id']=$info[0]->dispatch_info->id;
            $insert_data['client_name']=$info[0]->dispatch_info->client_name;
            $insert_data['client_id']=$info[0]->dispatch_info->client_id;
            $insert_data['client_model']=$info[0]->dispatch_info->client_model;
            $insert_data['company_model']=$info[0]->dispatch_info->company_model;
            $insert_data['craft_color_name']=$info[0]->dispatch_info->craft_color_name;
            $insert_data['sole_material_name']=$info[0]->dispatch_info->sole_material_name;
            $insert_data['sole_material_id']=$info[0]->dispatch_info->sole_material_id;
            $insert_data['is_check']=0;
            $insert_data['is_void']=0;
            $insert_data['created_at']=$now;
            $insert_data['updated_at']=$now;
            $inert_id = SoleDispatchPaper::insertGetId($insert_data);
            foreach ($info as $k=>$v){
                $v->is_print = '1';
                $v->status = '2';
                $v->save();
                $detailarr[$k] = [
                  'sole_dispatch_paper_id'=>$inert_id,
                  'dispatch_detail_id'=>$v->id,
                  'spec'=>$v->spec,
                  'type'=>$v->type,
                  'num'=>$v->num,
                  'created_at'=>$now,
                  'updated_at'=>$now,
                ];
            }
            SoleDispatchPaperDetail::insert($detailarr);
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    /**
     * 箱标打印添加数据
     */
    public function makeBoxLabelDispatchPaper(){
        $idarr = explode(',',$this->id);
        $info = $this->model->with(['dispatch_info'])->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        $num = BoxLabelDispatchPaper::where('no',$this->no)->count();
        if($num>0){
            return false;
        }
        DB::beginTransaction(); //开启事务
        try{
            $insert_data['no']=$this->no;
            $insert_data['plan_list_id']=$info[0]->plan_list_id;
            $insert_data['plan_list_no']=$info[0]->dispatch_info->plan_list_no;
            $insert_data['dispatch_id']=$info[0]->dispatch_info->id;
            $insert_data['client_name']=$info[0]->dispatch_info->client_name;
            $insert_data['client_id']=$info[0]->dispatch_info->client_id;
            $insert_data['client_model']=$info[0]->dispatch_info->client_model;
            $insert_data['company_model']=$info[0]->dispatch_info->company_model;
            $insert_data['craft_color_name']=$info[0]->dispatch_info->craft_color_name;
            $insert_data['sole_material_name']=$info[0]->dispatch_info->sole_material_name;
            $insert_data['sole_material_id']=$info[0]->dispatch_info->sole_material_id;
            $insert_data['is_check']=0;
            $insert_data['is_void']=0;
            $insert_data['created_at']=$now;
            $insert_data['updated_at']=$now;
            $inert_id = BoxLabelDispatchPaper::insertGetId($insert_data);
            $arr=[];

            foreach ($info as $k=>$v){
                $arr[$k]['id']=$v->id;
                $arr[$k]['plan_list_id']=$v->plan_list_id;
                $arr[$k]['dispatch_id']=$v->dispatch_id;
                $v->is_print = '1';
                $v->status = '2';
                $v->save();
                $detailarr[$k] = [
                    'box_label_dispatch_paper_id'=>$inert_id,
                    'dispatch_detail_id'=>$v->id,
                    'spec'=>$v->spec,
                    'type'=>$v->type,
                    'num'=>$v->num,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }
            BoxLabelDispatchPaperDetail::insert($detailarr);
            //判断箱标派工是否全部打印，修改状态
            foreach ($arr as $kk=>$vv){
                $num =  DispatchDetail::where('dispatch_id',$vv['dispatch_id'])
                    ->whereIn('status',['0','1'])->count();
                if($num==0){
                    Dispatch::where('id',$vv['dispatch_id'])->update([
                        'status'=>'2'
                    ]);
                    PlanList::where('id',$vv['plan_list_id'])->update([
                        'box_label_status'=>'2'
                    ]);
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return $e;
        }

    }

    /**
     * 注塑派工
     */
    public function makeInjectMoldDispatchPaper(){
        $idarr = explode(',',$this->id);
        $info = $this->model->with(['dispatch_info'])->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        $num = InjectMoldDispatchPaper::where('no',$this->no)->count();
        if($num>0){
            return false;
        }
        DB::beginTransaction(); //开启事务
        try{
            $insert_data['no']=$this->no;
            $insert_data['plan_list_id']=$info[0]->plan_list_id;
            $insert_data['plan_list_no']=$info[0]->dispatch_info->plan_list_no;
            $insert_data['dispatch_id']=$info[0]->dispatch_info->id;
            $insert_data['client_name']=$info[0]->dispatch_info->client_name;
            $insert_data['client_id']=$info[0]->dispatch_info->client_id;
            $insert_data['client_model']=$info[0]->dispatch_info->client_model;
            $insert_data['company_model']=$info[0]->dispatch_info->company_model;
            $insert_data['craft_color_name']=$info[0]->dispatch_info->craft_color_name;
            $insert_data['sole_material_name']=$info[0]->dispatch_info->sole_material_name;
            $insert_data['sole_material_id']=$info[0]->dispatch_info->sole_material_id;
            $insert_data['is_check']=0;
            $insert_data['is_void']=0;
            $insert_data['created_at']=$now;
            $insert_data['updated_at']=$now;
            $inert_id = InjectMoldDispatchPaper::insertGetId($insert_data);

            $arr=[];
            foreach ($info as $k=>$v){
                $arr[$k]['id']=$v->id;
                $arr[$k]['plan_list_id']=$v->plan_list_id;
                $arr[$k]['dispatch_id']=$v->dispatch_id;
                $v->is_print = '1';
                $v->status = '2';
                $v->save();
                $detailarr[$k] = [
                    'inject_mold_dispatch_paper_id'=>$inert_id,
                    'dispatch_detail_id'=>$v->id,
                    'spec'=>$v->spec,
                    'spec_id'=>$v->spec_id,
                    'num'=>$v->num,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }
            InjectMoldDispatchPaperDetail::insert($detailarr);
            //判断注塑派工是否全部打印，修改状态
            foreach ($arr as $kk=>$vv){
                $num =  DispatchDetail::where('dispatch_id',$vv['dispatch_id'])
                    ->whereIn('status',['0','1'])->count();
                if($num==0){
                     Dispatch::where('id',$vv['dispatch_id'])->update([
                        'status'=>'2'
                    ]);
                    PlanList::where('id',$vv['plan_list_id'])->update([
                        'box_label_status'=>'2'
                    ]);
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return $e;
        }
    }
    /**
     * 成品发货
     * @return bool|\Exception
     */
    public function makeDeliveryPaper(){
        $idarr = explode(',',$this->id);
        $num = DeliveryPaper::where('no',$this->no)->count();
        if($num>0){
            return false;
        }
        $info = $this->model->with(['delivery_info'])->whereIn('id',$idarr)->get();
        $insert_data = [];
        $now = Carbon::now();
        DB::beginTransaction(); //开启事务
        try{
            $insert_data['no']=$this->no;
            $insert_data['delivery_id']=$info[0]->delivery_id;
            $insert_data['plan_list_id']=$info[0]->plan_list_id?:0;
            $insert_data['plan_list_no']=$info[0]->delivery_info->plan_list_no;
            $insert_data['client_order_no']=$info[0]->delivery_info->client_order_no;
            $insert_data['client_name']=$info[0]->delivery_info->client_name;
            $insert_data['client_id']=$info[0]->delivery_info->client_id;
            $insert_data['company_model']=$info[0]->delivery_info->company_model;
            $insert_data['craft_color_name']=$info[0]->delivery_info->craft_color_name;
            $insert_data['craft_color_id']=$info[0]->delivery_info->craft_color_id;
            $insert_data['created_at']=$now;
            $insert_data['updated_at']=$now;
            $insert_data['is_check']=0;
            $insert_data['is_zuofei']=0;
            $insert_data['is_zuofei']=0;
            $inert_id = DeliveryPaper::insertGetId($insert_data);
            $all_num=0;
            $arr=[];
            foreach ($info as $k=>$v){
                $arr[$k]['id']=$v->id;
                $arr[$k]['plan_list_id']=$v->plan_list_id;
                $arr[$k]['delivery_id']=$v->delivery_id;
                $v->is_print = '1';
                $v->status='1';
                $v->save();
                $detailarr[$k] = [
                    'delivery_paper_id'=>$inert_id,
                    'delivery_detail_id'=>$v->id,
                    'spec'=>$v->spec,
                    'type'=>$v->type,
                    'num'=>$v->num,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ];
            }
            Delivery::where('id',$info[0]->delivery_id)->update([
                'is_print'=>'1'
            ]);
            DeliveryPaperDetail::insert($detailarr);

            $delivery_paper_info = DeliveryPaper::find($inert_id);
            $delivery_paper_info->total_num = $all_num;
            $delivery_paper_info->price = $info[0]->delivery_info->delivery_price;
            $delivery_paper_info->total_price = $all_num*$info[0]->delivery_info->delivery_price;
            $delivery_paper_info->save();

            foreach ($arr as $kk=>$vv){
                $num =  DispatchDetail::where('delivery_id',$vv['delivery_id'])
                    ->whereIn('status',['0','1'])->count();
                if($num==0){
                    Delivery::where('id',$vv['delivery_id'])->update([
                        'status'=>'2'
                    ]);
                    PlanList::where('id',$vv['plan_list_id'])->update([
                        'delivery_status'=>'2'
                    ]);
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
}
