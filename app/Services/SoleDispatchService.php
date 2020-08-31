<?php
namespace App\Services;
use Carbon\Carbon;
use Dcat\Admin\Admin;
use App\Models\PlanList;
use App\Models\PlanListDetail;
use App\Models\ClientSoleInformation;
use App\Models\DispatchDetail;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;
class SoleDispatchService
{
    public  function multiDispatch($plan_list_ids)
    {

        $ids_arr = explode(',',$plan_list_ids);

        $dispatch_model          = new Dispatch();
        $plan_lists = PlanList::with(['spec'])->whereIn('id',$ids_arr)->get();

        $now            = Carbon::now();
        DB::beginTransaction(); //开启事务
        try {
            $dispatch_detail_info_ids=[];
            foreach ($plan_lists as $pk=>$one_plan_list){
                $dispatch_num= $dispatch_model::where('plan_list_id',$one_plan_list->id)->count();
                if($dispatch_num>0){
                    DB::rollback();
                    return [
                        'status'  => 'error',
                        'message'     => '鞋底派工已存在,请确认',
                    ];
                }

                $client_sole_information = ClientSoleInformation::where('client_id',$one_plan_list->client_id)
                    ->where('company_model',$one_plan_list->company_model)
                    ->where('client_model',$one_plan_list->client_model)
                    ->where('craft_color_name',$one_plan_list->craft_color_name)
                    ->where('is_use',1)
                    ->first();

                $savedata = [
                    'plan_list_id'      => $one_plan_list->id,
                    'plan_list_no'     => $one_plan_list->plan_list_no,
                    'dispatch_no'      => getOrderNo('dispatches', 'P_XD', 12,'dispatch_no'),
                    'client_order_no'     => $one_plan_list->client_order_no,
                    'client_name'          => $one_plan_list->client_name,
                    'client_id'            => $one_plan_list->client_id,
                    'carft_skill_name'=>$one_plan_list->carft_skill_name,
                    'carft_skill_id'=>$one_plan_list->carft_skill_id,
                    'sole_material_id'=>$client_sole_information->sole_material_id,
                    'sole_material_name'=>$client_sole_information->sole_material_name,
                    'company_model_id'         => $one_plan_list->company_model_id,
                    'company_model'         => $one_plan_list->company_model,
                    'client_model'         => $one_plan_list->client_model,
                    'client_model_id'         => $one_plan_list->client_model_id,

                    'craft_color_name'         => $one_plan_list->craft_color_name,
                    'craft_color_id'         => $one_plan_list->craft_color_id,
                    'product_category_id'         => $one_plan_list->product_category_id,
                    'product_category_name'         => $one_plan_list->product_category_name,
                    'type'      => 'sole',
                    'process_workshop'       => 'sole',
                    'process_department' => 'sole',
                    'inject_mold_ask'        => $one_plan_list->inject_mold_ask,
                    'plan_remark'      => $one_plan_list->plan_remark,
                    'status'       =>'0',
                    'dispatch_user_id'       =>Admin::user()->id,
                    'dispatch_user_name'       =>Admin::user()->name,
                    'storage_out_status'       =>'0',
                ];
                $dispatch_info  = $dispatch_model->create($savedata);
                $all_num = 0;

                foreach ($one_plan_list->spec as $kk => $vv) {
                    $all_num+= $vv->num;
                    $insertData = [
                        'dispatch_id'           => $dispatch_info->id,
                        'plan_list_id'        => $one_plan_list->id,
                        'plan_list_detail_id' => $vv->id,
                        'spec'   => $vv->spec,
                        'type'   => $vv->type,
                        'num'        => $vv->num,
                        'is_print'        => '0',
                        'status'        => '0',
                        'spec_id'        => '0',
                        'storage_in_status'        => '0',
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                    $vv->sole_dispatch_num = $vv->num;
                    $vv->sole_dispatch_complete = '1';
                    $vv->save();
                    $detail_id_insert = DispatchDetail::insertGetId($insertData);
                    $dispatch_detail_info_ids[] = $detail_id_insert;
                }
                $dispatch_info->all_num = $all_num;
                $dispatch_info->save();
                //修改计划单状态
                if ($one_plan_list->process !='sole') {
                    $one_plan_list->process = 'sole';
                    $one_plan_list->status = '1';
                    $one_plan_list->save();
                }
            }
            DB::commit();
            return [
                'status'  => 'success',
                'ids'     => implode(',',$dispatch_detail_info_ids),
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => $e->getMessage(),
                'status'  => 'error',
            ];
        }
    }
}
