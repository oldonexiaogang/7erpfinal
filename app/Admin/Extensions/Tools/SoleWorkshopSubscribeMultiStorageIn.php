<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\RawMaterialStorage;
use App\Models\SoleWorkshopSubscribe;
use App\Models\RawMaterialStorageLog;
use Illuminate\Support\Facades\DB;
use Dcat\Admin\Admin;
use Carbon\Carbon;
class SoleWorkshopSubscribeMultiStorageIn extends CommonTools
{
    protected $action;

    // 注意action的构造方法参数一定要给默认值
    public function __construct($title = null, $action = 1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  ">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要入库所选数据？",
            // 确认弹窗 content
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        //判断是否包含未验收或者未入库的数据

        $noprintcount = SoleWorkshopSubscribeDetail::whereIn('id',$keys)
            ->where(function ($query) {
                $query->orWhere('is_void','1')
                      ->orWhere('check_status','!=','verify')
                      ->orWhere('is_print','1');
            })->count();
        if($noprintcount>0){
            return $this->response()->error('请确认所选数据已审核且未作废,未打印')->refresh();
        }

        //批量入库操作
        $data = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->whereIn('id',$keys)->get();

        DB::beginTransaction(); //开启事务
        try{
            $storage = new RawMaterialStorage();

            $now = Carbon::now();
            $is_create=0;
            $insert_data=[];
            $storage_log_data=[];
            foreach($data as $k=>$v) {
                $v->storage_in_num    = $v->apply_num;
                $v->check_user_id   = Admin::user()->id;
                $v->check_user_name = Admin::user()->name;
                $v->save();
                //仓库查找，相同进行修改，不同进行添加
                $storage_info = $storage->where('supplier_id', $v->sole_workshop_subscribe->supplier_id)
                    ->where('raw_material_product_information_id', $v->sole_workshop_subscribe->raw_material_product_information_id)
                    ->where('raw_material_category_id', $v->sole_workshop_subscribe->raw_material_category_id)
                    ->where('color_id', $v->sole_workshop_subscribe->color_id)
                    ->where('purchase_standard_id', $v->purcahse_standard_id)
                    ->where('unit_id', $v->unit_id)
                    ->where('change_coefficient', $v->change_coefficient)
                    ->where('price', $v->price)
                    ->first();

                $changedata = [
                    'supplier_id'                           => $v->sole_workshop_subscribe->supplier_id,
                    'supplier_name'                         => $v->sole_workshop_subscribe->supplier_name,
                    'raw_material_product_information_id'   => $v->sole_workshop_subscribe->raw_material_product_information_id,
                    'raw_material_product_information_name' => $v->sole_workshop_subscribe->raw_material_product_information_name,
                    'raw_material_product_information_no'   => $v->sole_workshop_subscribe->raw_material_product_information_no,
                    'raw_material_category_id'              => $v->sole_workshop_subscribe->raw_material_category_id,
                    'raw_material_category_name'            => $v->sole_workshop_subscribe->raw_material_category_name,
                    'purchase_standard_id'                  => $v->purcahse_standard_id,
                    'purchase_standard_name'                => $v->purcahse_standard_name,
                    'color_id'                              => $v->sole_workshop_subscribe->color_id,
                    'color'                                 => $v->sole_workshop_subscribe->color,
                    'unit'                                  => $v->unit_name,
                    'unit_id'                               => $v->unit_id,
                    'price'                                 => $v->price,
                    'change_coefficient'                    => $v->change_coefficient,
                    'num'                                   => $v->apply_num,
                    'created_at'                            => $now,
                    'updated_at'                            => $now,
                ];
                if ($storage_info) {
                    $storage_info->num+=$v->apply_num;
                    $storage_info->save();
                    //出入库记录
                    $temp_data['raw_material_storage_id']=$storage_info->id;
                    $temp_data['raw_material_product_information_id']=$storage_info->raw_material_product_information_id;
                    $temp_data['raw_material_product_information_name']=$storage_info->raw_material_product_information_name;
                    $temp_data['raw_material_product_information_no']=$storage_info->raw_material_product_information_no;
                    $temp_data['check_user_id']=Admin::user()->id;
                    $temp_data['check_user_name']=Admin::user()->name;
                    $temp_data['from']='批量出入库';
                    $temp_data['num']=abs($v->apply_num);
                    $temp_data['after_storage_num']=$storage_info->num;
                    $temp_data['created_at']= $now;
                    $temp_data['updated_at']= $now;
                    if(abs($v->apply_num)&&$v->apply_num>0){
                        $temp_data['type']='in';
                        DB::table('raw_material_storage_log')->insert($temp_data);
                    }else{
                        $temp_data['type']='out';
                        DB::table('raw_material_storage_log')->insert($temp_data);
                    }

                } else {
                    $is_create+=1;

                    $insert_data[] = $changedata;
                    $insertId = DB::table('raw_material_storage')->insertGetId($changedata);
                    //出入库记录
                    $temp_data['raw_material_storage_id']=$insertId;
                    $temp_data['raw_material_product_information_id']=$changedata['raw_material_product_information_id'];
                    $temp_data['raw_material_product_information_name']=$changedata['raw_material_product_information_name'];
                    $temp_data['raw_material_product_information_no']=$changedata['raw_material_product_information_no'];
                    $temp_data['check_user_id']=Admin::user()->id;
                    $temp_data['check_user_name']=Admin::user()->name;
                    $temp_data['from']='批量出入库';
                    $temp_data['num']=abs($changedata['num']);
                    $temp_data['after_storage_num']=$changedata['num'];
                    $temp_data['created_at']= $now;
                    $temp_data['updated_at']= $now;
                    if(abs($changedata['num'])>0&&$changedata['num']>0){
                        $temp_data['type']='in';
                         DB::table('raw_material_storage_log')->insert($temp_data);
                    }else{
                        $temp_data['type']='out';
                        DB::table('raw_material_storage_log')->insert($temp_data);
                    }
                }
            }

            DB::commit();
            return $this->response()->redirect(admin_url('sole-workshop-subscribe-detail'));
        }catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error($e.'入库数据异常,请检查')->refresh();
        }

    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
