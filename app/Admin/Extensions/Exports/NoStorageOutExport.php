<?php

namespace App\Admin\Extensions\Exports;

use Illuminate\Contracts\View\View;
use App\Models\PlanList;
use App\Models\Delivery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NoStorageOutExport implements FromCollection,WithHeadings
{
    public function __construct($start,$end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $plan_list_model = new PlanList();
        $rows =  PlanList::where('created_at','>=',$this->start)
            ->where('created_at','<=',$this->end)
            ->get();
        $showarr = [];
        foreach ($rows as $index => $row) {
            $showarr[$index]['created_at']= $row['created_at'];
            $showarr[$index]['plan_list_no']= $row['plan_list_no'];
            $showarr[$index]['client_name']= $row['client_name'];
            $showarr[$index]['client_order_no']= $row['client_order_no'];
            $showarr[$index]['product_time']= $row['product_time'];
            $showarr[$index]['personnel_name']= $row['personnel_name'];
            $showarr[$index]['product_category_name']= $row['product_category_name'];
            $showarr[$index]['company_model']= $row['company_model'];
            $showarr[$index]['craft_color_name']= $row['craft_color_name'];
            $showarr[$index]['spec_num'] =$row['spec_num'];
            $showarr[$index]['delivery_num'] =$row['delivery_num'];
            $showarr[$index]['wait_delivery_num'] = $row['spec_num']-$row['delivery_num'];
            for($i=33;$i<=41;$i++){
                $code_num = $plan_list_model->getDetailNum($row['id'],$i);
                if($code_num['left']>0||$code_num['right']>0){
                    $showarr[$index][''.$i]= $code_num['left'].'/'.$code_num['right'];
                }else{
                    $showarr[$index][''.$i]=  $code_num['all'];
                }
            }
        }
        return collect($showarr);
    }
    public function headings(): array
    {
        return [
            'created_at'=>'订制时间',
            'plan_list_no'=>'计划编号',
            'client_name'=>'客户',
            'client_order_no' => '客户计划单号',
            'product_time' => '生产周期',
            'personnel_name' => '业务员',
            'product_category_name' => '产品类型',
            'company_model' => '型号',
            'craft_color_name' => '工艺颜色',
            'spec_num' => '订单数',
            'delivery_num' => '已发数量',
            'wait_delivery_num' => '未发数量',
            '33' => '33码',
            '34' => '34码',
            '35' => '35码',
            '36' => '36码',
            '37' => '37码',
            '38' => '38码',
            '39' => '39码',
            '40' => '40码',
            '41' => '41码',
        ];
    }
}
