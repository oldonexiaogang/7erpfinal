<?php

namespace App\Services;

use App\Models\BoxLabelDispatchPaper;
use App\Models\BoxLabelDispatchPaperDetail;
use App\Models\DeliveryDetail;
use App\Models\Dispatch;
use App\Models\DispatchDetail;
use App\Models\InjectMoldDispatchPaper;
use App\Models\MoldInformation;
use App\Models\PlanListDetail;
use App\Models\PlanList;
use App\Models\RawMaterialProductInformation;
use App\Models\RawMaterialStorage;
use App\Models\RawMaterialStorageOut;
use App\Models\RawMaterialStorageOutPaper;
use App\Models\SoleDispatchPaper;
use App\Models\SoleDispatchPaperDetail;
use App\Models\SoleWorkshopSubscribeDetail;
use App\Models\SoleWorkshopSubscribePaper;
use Carbon\Carbon;
use Dcat\Admin\Admin;


class PrinterService
{
    public function __construct()
    {
        $this->style = <<<EOB
        <style>
            .data-table tr td,.data-table tr th{
                border:0.5px solid #333;
                text-align: center;
                vertical-align: middle;
                line-height: 15px;
            }
            .page-title{
                text-align:center;text-decoration: underline;
                font-size: 15px;
                line-height: 15px;
            }
            .page-sub-title{
                text-align: center;
                line-height: 12px;
                font-size: 12px;
            }
            .footer{
                line-height: 15px;
            }
            .data-top{
                line-height: 12px;
            }
            .table-line-2 tr td{
                line-height:28px;
                height: 28px;
                font-size: 14px;
                font-weight: bold;
            }

        </style>
EOB;

    }
    //底部
    private function printFooter($left,$lnum,$middle,$mnum,$right,$rnum){
        return <<<EOD
        <table class="footer">
            <tr>
                <td colspan="{$lnum}">$left</td>
                <td colspan="{$mnum}">$middle</td>
                <td colspan="{$rnum}">$right</td>
            </tr>
        </table>
EOD;
    }
//头部
    private function printHeader($company,$subtitle,$left,$lnum,$middle,$mnum,$right,$rnum,$top=0){
        return <<<EOC
        <div class="page-title">
                 {$company}
        </div>
          <div class="page-sub-title">
                 {$subtitle}
        </div>
        <table>
            <tr class="data-top" >
                <td colspan="{$lnum}" style="text-align: left">{$left}</td>
                <td colspan="{$mnum}">{$middle}</td>
                <td colspan="{$rnum}" style="text-align: right">{$right}</td>
            </tr>
        </table>
EOC;
    }
    //注塑派工
    public function injectMoldDispatchTable($ids,$no){
        $idarr = explode(',',$ids);
        if(count($idarr)>1){
            //多个鞋底派工
            $datas = DispatchDetail::with(['dispatch_info'])->whereIn('id',$idarr)->get();
            $total_num = DispatchDetail::whereIn('id',$idarr)->sum('num');
            return $this->injectMoldTCPDF(210,297,$datas,$total_num,$no);
        }else{
            $datas = DispatchDetail::with(['dispatch_info'])->where('id',$ids)->get();
            $total_num = $datas[0]->num;
            return $this->injectMoldTCPDF(210,297,$datas,$total_num,$no);
        }
    }
//注塑派工
    private function getInjectMoldCodeInfo($vv){
        return [
            'plan_list_no'=>$vv->dispatch_info->plan_list_no,
            'spec'=>$vv->spec,
            'dispatch_no'=>$vv->dispatch_info->dispatch_no,
            'num'=>$vv->num,
            'plan_remark'=>$vv->dispatch_info->plan_remark,
            'inject_mold_ask'=>$vv->dispatch_info->inject_mold_ask,
            'dispatch_user_name'=>$vv->dispatch_info->dispatch_user_name,
        ];
    }
    private function injectMoldTCPDF($width, $height,$datas,$total_num,$no){
        $pdf = new \TCPDF('P', 'mm','A4',true, 'UTF-8', false);
        //数据处理：按照规格型号分开，
        $codenum=[
            "33"=>0,
            "34"=>0,
            "35"=>0,
            "36"=>0,
            "37"=>0,
            "38"=>0,
            "39"=>0,
            "40"=>0,
            "41"=>0,
        ];
        $onedata = $datas[0];
        $info = [
            'client_name'=>$onedata->dispatch_info->client_name,
            'dispatch_type'=>config('plan.dispatch_process_workshop')['inject_mold'],
            'dispatch_time'=>date('Y年m月d日',strtotime($onedata->created_at)),
            'company_model'=>$onedata->dispatch_info->company_model,
            'spec'=>$onedata->spec,
            'carft_skill_name'=>$onedata->dispatch_info->carft_skill_name,
            'sole_material_name'=>$onedata->dispatch_info->sole_material_name,
            'craft_color_name'=>$onedata->dispatch_info->craft_color_name,
            'product_category_name'=>$onedata->dispatch_info->product_category_name
        ];
        foreach ($datas as $k=>$vv){
            for ($i=33;$i<=41;$i++){
                if(!(strpos($vv->spec,''.$i)===false)){
                    $types[''.$i][]=$this->getInjectMoldCodeInfo($vv);
                    $codenum[''.$i]+=$vv->num;
                }
            }
        }

        $htmltrab = '';

        // 设置文档信息
        $title = '生产注塑压机派工单';
        $name = config('admin.company_name');
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(5, 5, 5);//页面间隔
//        $pdf->SetHeaderMargin(5);//页眉top间隔
//        $pdf->SetFooterMargin(10);//页脚bottom间隔
        // 设置分页
        //  $pdf->SetAutoPageBreak(true, 3);
        $pdf->setFontSubsetting(true);
        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 10);
//        $pdf->AddPage();
        foreach ($types as $pindex =>$pagearr){
            if(!(count($pagearr)>0)){
                continue;
            }

            $inone = $pagearr[0];

            $data_detail='';
            foreach ($pagearr as $index=>$page){

                $data_detail .= <<<EOA
                <tr>
                   <td colspan="3">{$page['plan_list_no']}</td>
                    <td colspan="3">{$page['dispatch_no']}</td>
                    <td colspan="3">{$page['num']}</td>
                    <td colspan="4">{$page['plan_remark']}</td>
                </tr>
EOA;
                $huizong = is_float_number( $codenum[$pindex]);

            }

            $htmltrab = <<<EOF
                    {$this->style}
                    <div >
                <table class="header">
                    <tr>
                        <td colspan="8" class="page-sub-title" style="text-decoration: underline">
                            {$name}
                        </td>
                    </tr>
                    <tr>
                     <td colspan="2" class="page-title" style="text-align: left;text-decoration: none">
                          {$info['product_category_name']}:{$pindex}码
                        </td>
                        <td colspan="4" class="page-title" style="text-decoration: none">{$title}</td>
                         <td colspan="2" class="page-title" style="text-align: right;text-decoration: none">
                            No:{$no}
                        </td>
                    </tr>
                    <tr class="data-top">
                        <td colspan="3">客户:{$info['client_name']}</td>
                        <td colspan="2" style="text-align: center">派工类型:{$info['dispatch_type']}</td>
                        <td colspan="5" style="text-align: right">派工时间：{$info['dispatch_time']}</td>
                    </tr>
                </table>
                <table class="data-table">
                    <tr style="border-bottom:none">
                        <td colspan="1">雷力型号</td>
                        <td colspan="2">{$info['company_model']}</td>
                        <td  colspan="1">型号规格</td>
                        <td  colspan="2">{$inone['spec']}</td>
                        <td  colspan="1">工艺</td>
                        <td  colspan="2">{$info['carft_skill_name']}</td>
                        <td  colspan="1">材料</td>
                        <td  colspan="3">{$info['sole_material_name']}</td>
                    </tr>
                     <tr>
                       <td colspan="3">计划单号</td>
                        <td colspan="3">派工号</td>
                        <td colspan="3">派工数量</td>
                        <td colspan="4">派工说明</td>
                    </tr>
                    {$data_detail}
                    <tr>
                        <td colspan="14" style="text-align: right">
                            合计总数: {$huizong}
                        </td>
                    </tr>
                     <tr >
                        <td rowspan="2" style="line-height: 30px">完成情况</td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                    </tr>
                    <tr >
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                    </tr>
                     <tr>
                        <td colspan="13"  style="text-align: left;line-height: 28px;">
                          注塑要求:{$inone['inject_mold_ask']}
                        </td>
                    </tr>
                     <tr>
                        <td colspan="6"  style="text-align: left;line-height: 20px;">
                          收货人:
                        </td>
                         <td colspan="7"  style="text-align: left;line-height: 20px;">
                          收货时间:
                        </td>
                    </tr>
                </table>
                 {$this->printFooter('',5,'',5,'派工员:'.$inone['dispatch_user_name'],3)}
                 </div>
EOF;
            $pdf->AddPage();
            $pdf->writeHTML($htmltrab);
        }

        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }

    //单号查询注塑
    public function injectMoldDispatchNoTable($no){
        $ids =  InjectMoldDispatchPaper::where('no',$no)
            ->where('is_void','id')
            ->pluck('dispatch_id');
        $data = DispatchDetail::whereHas('dispatch_info',function ($q){
            $q->where('is_void','0');
        })->whereIn('dispatch_id',$ids)->get();
        $total_num =  DispatchDetail::whereHas('dispatch_info',function ($q){
            $q->where('is_void','0');
        })->whereIn('dispatch_id',$ids)->sum('num');
        return $this->injectMoldTCPDF(210,297,$data,$total_num,$no);
    }

    //模具信息id 查询
    public function moldInformationTable($id){
        $idarr = explode(',',$id);
        if(count($idarr)>1){
            $data = MoldInformation::with('mold_category_child')->whereIn('id',$idarr)->get();
        }else{
            $data = MoldInformation::with('mold_category_child')->where('id',$idarr)->get();
        }
        return $this->moldInformationTCPDF(180,297,$data);
    }

    /**
     * 模具单据打印
     */
    private function moldInformationTCPDF($width,$height,$data){
        $pdf = new \TCPDF('p', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '新雷力模具通知开发单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(5, 5, 5);//页面间隔
//        $pdf->SetHeaderMargin(5);//页眉top间隔
//        $pdf->SetFooterMargin(10);//页脚bottom间隔
        // 设置分页
        //$pdf->SetAutoPageBreak(true, 25);
        $pdf->setFontSubsetting(true);
        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        $company_name = config('admin.company_name');
        $mold_information_no= $data[0]->mold_information_no;
        $make_img= Admin::user()->name;
        $today_date = date('Y年m月d日',time());
        $property = config('plan.mold_information_property');

        $htmltrab='';
        foreach ($data as $kk=>$dv) {
            $position = 'uploads/'.$dv->image;
            $htmltrab .= <<<EOF
            {$this->style}
         <table>
                <tr>
                    <td class="page-title"  colspan="3">{$title}</td>
                </tr>
                <tr >
                 <td >&nbsp;</td>
                    <td class="page-sub-title">{$today_date}</td>
                    <td style="text-align: right;text-decoration: none" class="page-title">No:{$mold_information_no}</td>
                </tr>
            </table>
            <table class="data-table" >
                <tr>
                    <td >雷力型号</td>
                    <td >{$dv->company_model}</td>
                    <td >客户型号</td>
                    <td colspan="2">{$dv->client_model}</td>
                    <td colspan="5" rowspan="6">
                    <img src="{$position}" alt="图片" style="width:4cm;height:3.5cm">
</td>
                </tr>
                <tr>
                    <td >产品类型</td>
                    <td colspan="4">{$dv->mold_category_child->mold_category_name}</td>
                </tr>
                <tr>
                    <td >所属类型</td>
                     <td colspan="2">{$property[$dv->properties]}</td>
                    <td >双数</td>
                    <td >{$dv->sole_count}</td>
                </tr>
                  <tr>
                    <td >模具明细</td>
                    <td colspan="4">{$dv->mold_make_detail_standard}</td>
                </tr>
                  <tr>
                    <td >业务员</td>
                    <td colspan="4">{$dv->personnel_name}</td>
                </tr>
                  <tr>
                    <td >备注</td>
                    <td colspan="4" style="text-align:left">
                    <span style="text-align:left;color:#f00;font-size: 1.1em">{$dv->remark}</span>
</td>
                </tr>
            </table>
            <br>
            {$this->printFooter('审核人:',4,'签收人:',4,'制单人:'.$make_img,2)}
            <div style="height:2cm;width:100%;"></div>
EOF;
        }

        $pdf->writeHTML($htmltrab);
        //输出PDF
        $pdf->Output($title.'.pdf', 'I');
    }
    //单号查询原材料出库
    public function rawMaterialStorageOutNoTable($no){
        $idarr =  RawMaterialStorageOutPaper::where('no',$no)->where('is_void','0')
            ->pluck('raw_material_storage_out_id');
        if(count($idarr)>1){
            $data = RawMaterialStorageOut::whereIn('id',$idarr)->get();
            $total_num =  RawMaterialStorageOut::whereIn('id',$idarr)->sum('num');
        }else{
            $data = RawMaterialStorageOut::where('id',$idarr)->get();
            $total_num = $data[0]->num;
        }
        return $this->rawMaterialStorageOutTCPDF(225,500,$data,$total_num,$no);
    }
    //原材料出库打印
    public function rawMaterialStorageOutTable($ids,$no){
        $idarr = explode(',',$ids);
        if(count($idarr)>1){
            $rawmaterialdatas = RawMaterialStorageOut::whereIn('id',$idarr)->get();
            $total_num = RawMaterialStorageOut::whereIn('id',$idarr)->sum('num');
        }else{
            $rawmaterialdatas = RawMaterialStorageOut::where('id',$ids)->get();
            $total_num = $rawmaterialdatas[0]->num;
        }
        return $this->rawMaterialStorageOutTCPDF(225,500,$rawmaterialdatas,$total_num,$no);
    }
//原材料出库打印
    public function rawMaterialStorageOutTCPDF($width,$height,$data,$total_num,$no){
        $pdf = new \TCPDF('p', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '原材料出库凭证';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(5, 5, 5);//页面间隔
//        $pdf->SetHeaderMargin(5);//页眉top间隔
//        $pdf->SetFooterMargin(10);//页脚bottom间隔
        // 设置分页
        //$pdf->SetAutoPageBreak(true, 25);
        $pdf->setFontSubsetting(true);
        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        //入库数据
        $data_detail='';
        $unit_name='';

        $data_info = RawMaterialProductInformation::where('id',$data[0]->raw_material_product_information_id)->first();
        if($data_info->supplier_name){
            $sipplier_name=$data_info->supplier_name;
        }else{
            $sipplier_name='';
        }

        foreach ($data as $kk=>$v){
            $unit_name = $v->purchase_unit;
            $gongyingshang_name = $v->lingyong_user_name;
            $data_detail.='
             <tr>
                <td >'.date('Y年m月d日',strtotime($v->created_at)).'</td>
                <td >'.$v->raw_material_category_name.'</td>
                <td >'.$v->raw_material_product_information_name.'</td>
                <td >'.$v->apply_user_name.'</td>
                <td >'.$v->purchase_standard_name.'</td>
                <td >'.$v->num.'('.$v->unit.')'.'</td>
                <td >'.$v->change_coefficient.'</td>
                <td >'.$v->remark.'</td>
            </tr>
            ';
        }
        $huizong = $total_num;
        $htmltrab = <<<EOF
            {$this->style}
            {$this->printHeader(config('admin.company_name'),$title,'To:'.$sipplier_name,3,'申购单号:xxxx',3,'No:'.$no,3)}
            <table class="data-table">
                <tr>
                    <th >出库时间</th>
                    <th >原料类型</th>
                    <th >原料名称</th>
                    <th >领用厂家</th>
                    <th >规格</th>
                    <th >出库数量</th>
                    <th >系数</th>
                    <th >备注</th>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="7" style="text-align: right;line-height: 20px;">
                       合计总数:{$huizong}
                    </td>
                    <td >&nbsp;</td>
                </tr>
            </table>
            {$this->printFooter('制表员:',2,'主管审批:',4,'原材料仓签单:',2)}
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        $pdf->Output($title.'.pdf', 'I');
    }

    //鞋底材料采购单
    public function soleWorkshopSubscribeDetailTable($ids=0,$no){
        $idarr = explode(',',$ids);
        if(count($idarr)>1){
            $data = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->whereIn('id',$idarr)->get();
            $total_num = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->whereIn('id',$idarr)->sum('apply_num');
        }else{
            $data = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->where('id',$ids)->get();
            $total_num = $data[0]->apply_num;
        }
        return $this->soleWorkshopSubscribeTCPDF(210,297,$data,$total_num,$no);
    }
    //单号查询鞋底材料采购单
    public function soleWorkshopSubscribeDetailNoTable($no){
        $idarr =  SoleWorkshopSubscribePaper::where('no',$no)
            ->where('is_void','0')
            ->pluck('sole_workshop_subscribe_detail_id');
        if(count($idarr)>1){
            $data = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->whereIn('id',$idarr)->get();
            $total_num = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->whereIn('id',$idarr)->sum('apply_num');
        }else{
            $data = SoleWorkshopSubscribeDetail::with('sole_workshop_subscribe')->where('id',$idarr)->get();
            $total_num = $data[0]->apply_num;
        }
        return $this->soleWorkshopSubscribeTCPDF(210,297,$data,$total_num,$no);
    }



    //鞋底材料采购单
    private function soleWorkshopSubscribeTCPDF($width, $height,$data,$total_num,$no){
        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '原材料入库单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(5, 5, 5);//页面间隔
//        $pdf->SetHeaderMargin(5);//页眉top间隔
//        $pdf->SetFooterMargin(10);//页脚bottom间隔
        // 设置分页
        //$pdf->SetAutoPageBreak(true, 25);
        $pdf->setFontSubsetting(true);
        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        //入库数据
        $data_detail='';
        $unit_name='';
        $supplier_name='';
        foreach ($data as $kk=>$v){
            $unit_name = $v->unit_name;
            $supplier_name= $v->sole_workshop_subscribe->supplier_name;
            $data_detail.='
             <tr>
                <td width="18%">'.date('Y年m月d日',strtotime($v->updated_at)).'</td>
                <td width="25%">'.$v->sole_workshop_subscribe->sole_workshop_subscribe_no.'</td>
                <td width="15%">'.$v->sole_workshop_subscribe->raw_material_product_information_no.'</td>
                <td width="20%">'.$v->sole_workshop_subscribe->raw_material_product_information_name.'</td>
                <td width="10%">'.$v->unit_name.'</td>
                <td width="12%">'.$v->apply_num.'('.$v->unit_name.')'.'</td>
            </tr>
            ';
        }
        $huizong = $total_num;
        $htmltrab = <<<EOF
            {$this->style}
            {$this->printHeader(config('admin.company_name'),$title,'To:'.$supplier_name,5,'&nbsp;',1,'No:'.$no,2)}
            <table class="data-table">
                <tr>
                    <th  width="18%">申购时间</th>
                    <th  width="25%">申购单号</th>
                    <th  width="15%">原料编号</th>
                    <th  width="20%">原料名称</th>
                    <th  width="10%">规格</th>
                    <th  width="12%">申购数量</th>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="6" style="text-align: right;line-height: 20px;">
                       合计总数:{$huizong}
                    </td>
                </tr>
                 <tr>
                    <td colspan="6" rowspan="3" style="text-align: left;height:50px;line-height: 20px;">
                      备注:
                    </td>
                </tr>
            </table>
            {$this->printFooter('制表员',3,'',1,'审批员',2)}
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }



    public  function rawPlanListDispatchTable($ids,$no){
        $idarr = explode(',',$ids);
        $datas = Dispatch::with(['plan_list','detail'])->whereIn('plan_list_id',$idarr)
            ->get();
        $total_num = DispatchDetail::whereIn('plan_list_id',$idarr)
            ->sum('num');
        return $this->soleDispatchMultiTCPDF(210,93,$datas,$total_num,$no);
    }

    //单个鞋底派工+记录的多个码--
    public function soleDispatchTable($ids, $no)
    {
        $idarr = explode(',', $ids);
        if (count($idarr) > 1) {
            $datas     = DispatchDetail::with(['plan_list', 'dispatch_info'])->whereIn('id', $idarr)->get();
            $total_num = DispatchDetail::whereIn('id', $idarr)->sum('num');
            return $this->soleDispatchSimpleTCPDF(210, 93, $datas, $total_num, $no);
        } else {
            $datas     = DispatchDetail::with(['plan_list', 'dispatch_info'])->where('id', $ids)->first();
            $total_num = $datas->num;
            return $this->soleDispatchSimpleCodeTCPDF(210, 93, $datas, $total_num, $no);
        }
    }
    //单号查询的鞋底派工
    public function soleDispatchNoTable($no){
        $paper =  SoleDispatchPaper::where('no',$no)
            ->where('is_void',0)
            ->first();
        $ids = SoleDispatchPaperDetail::where('sole_dispatch_paper_id',$paper->id)
            ->pluck('dispatch_detail_id');
        if(count($ids)>1){
            $plan_list_ids = DispatchDetail::whereIn('id',$ids)->pluck('plan_list_id');

            $data = Dispatch::with(['plan_list','detail'])->whereIn('plan_list_id',$plan_list_ids)
                ->get();
            $total_num = DispatchDetail::whereIn('plan_list_id',$plan_list_ids)
                ->sum('num');
            return $this->soleDispatchMultiTCPDF(210,93,$data,$total_num,$no);
        }else{
            $id = $ids->toArry()[0];
            $data = DispatchDetail::with(['plan_list','dispatch_info'])->where('id',$id)->first();
            $total_num =  DispatchDetail::where('id',$id)->sum('num');
            return $this->soleDispatchSimpleTCPDF(210,93,$data,$total_num,$no);
        }

    }

    //多个
    private function soleDispatchMultiTCPDF($width, $height,$datas,$total_num,$no){
        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '鞋底派工单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetAutoPageBreak(false,0);
        // 设置间距
        $pdf->SetMargins(5, 5, 5,0);//页面间隔
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();

        //数据
        $code='';
        $codenum='';
        $all_code_num = count($datas);
        $extra_empty_html = '';
        for ($i=0;$i<(9-$all_code_num);$i++){
            $extra_empty_html.='<td></td>';
        }
        $data_detail = '';
        $codesum=[
            "33"=>0,
            "34"=>0,
            "35"=>0,
            "36"=>0,
            "37"=>0,
            "38"=>0,
            "39"=>0,
            "40"=>0,
            "41"=>0,
        ];
        foreach ($datas as $kk=>$vv){
            $oneshow =[
                "33"=>0,
                "34"=>0,
                "35"=>0,
                "36"=>0,
                "37"=>0,
                "38"=>0,
                "39"=>0,
                "40"=>0,
                "41"=>0,
            ];
            $allsum=0;
            foreach ($vv->detail as $kkk=>$vvv){
                $allsum+=$vvv->num;
                for($i=33;$i<=41;$i++){
                    if($vvv->spec==''.$i){
                        $codesum[''.$i]+=$vvv->num;
                        $oneshow[''.$i]=$vvv->num;
                    }
                }
            }
            $data_detail_other_html='';
            for($j=33;$j<=41;$j++){
               $data_detail_other_html.=' <td >'.($oneshow[''.$j]>0?
                       is_float_number($oneshow[''.$j]):'').'</td>';
            }
            $data_detail.=' <tr>
               <td >'.($kk+1).'</td>
                <td colspan="2">'.$vv->client_order_no.'</td>
                <td colspan="2">'.$vv->plan_list_no.'</td>'.$data_detail_other_html.'
                <td >'.is_float_number($allsum).'</td>
            </tr>';
        }
        $total_num = is_float_number($total_num);

        $onedata = $datas[0];
        $jiaohuo_at = date('Y-m-d',strtotime($onedata->plan_list->delivery_date));
        $paigong_at = date('Y-m-d',strtotime($onedata->created_at));
        $paigong_type = config('plan.dispatch_type')[$onedata->type];
        $codesum=[
            "33"=>$codesum['33']>0?$codesum['33']:'',
            "34"=>$codesum['34']>0?$codesum['34']:'',
            "35"=>$codesum['35']>0?$codesum['35']:'',
            "36"=>$codesum['36']>0?$codesum['36']:'',
            "37"=>$codesum['37']>0?$codesum['37']:'',
            "38"=>$codesum['38']>0?$codesum['38']:'',
            "39"=>$codesum['39']>0?$codesum['39']:'',
            "40"=>$codesum['40']>0?$codesum['40']:'',
            "41"=>$codesum['41']>0?$codesum['41']:'',
        ];

        $company_name = config('admin.company_name');
        $htmltrab = <<<EOF
            {$this->style}
            <table>
                <tr>

                    <td class="page-title" colspan="3">{$company_name}</td>

                </tr>
                <tr >
                    <td >生产周期:{$onedata->plan_list->product_time}</td>
                    <td class="page-sub-title" >{$title}</td>
                    <td style="text-align: right;text-decoration: none" class="page-title">No:{$no}</td>
                </tr>
            </table>
             <table >
                <tr style="border-bottom:none;line-height: 15px;">
                    <td colspan="3" >派工时间:{$paigong_at}</td>
                    <td  colspan="2">交货日期:{$jiaohuo_at}</td>
                    <td  colspan="4" style="text-align: center">客户订单:{$onedata->client_order_no}</td>
                    <td  colspan="3" style="text-align: right">派工类型:{$paigong_type}</td>
                </tr>
            </table>
            <table class="data-table">
                <tr style="border-bottom:none">
                    <td  colspan="1">客户</td>
                    <td  colspan="2">{$onedata->client_name}</td>
                    <td  colspan="2">客户型号</td>
                    <td  colspan="4">{$onedata->client_model}</td>
                    <td  colspan="2">雷力型号</td>
                    <td  colspan="4">{$onedata->company_model}</td>
                </tr>
                <tr style="border-bottom:none">
                    <td colspan="1">颜色</td>
                    <td colspan="6">{$onedata->craft_color_name}</td>
                    <td  colspan="1">用料</td>
                    <td  colspan="7">{$onedata->sole_material_name}</td>
                </tr>
                <tr>
                    <td >下料段</td>
                    <td colspan="2">客户单号</td>
                    <td colspan="2">雷力单号</td>
                    <td >33码</td>
                    <td >34码</td>
                    <td >35码</td>
                    <td >36码</td>
                    <td >37码</td>
                    <td >38码</td>
                    <td >39码</td>
                    <td >40码</td>
                    <td >41码</td>
                    <td >合计</td>
                </tr>
                {$data_detail}
                 <tr>
                    <td colspan="5" >小计</td>
                    <td >{$codesum['33']}</td>
                    <td >{$codesum['34']}</td>
                    <td >{$codesum['35']}</td>
                    <td >{$codesum['36']}</td>
                    <td >{$codesum['37']}</td>
                    <td >{$codesum['38']}</td>
                    <td >{$codesum['39']}</td>
                    <td >{$codesum['40']}</td>
                    <td >{$codesum['41']}</td>
                    <td >{$total_num}</td>
                </tr>
                <tr>

                    <td colspan="2" >
                       刀模
                    </td>
                    <td colspan="6" style="text-align: left">
                      {$onedata->plan_list->knife_mold}
                    </td>
                    <td colspan="2" >
                       革片
                    </td>
                    <td colspan="5" style="text-align: left">
                      {$onedata->plan_list->leather_piece}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" >
                       鞋跟
                    </td>
                    <td colspan="6" style="text-align: left">
                        {$onedata->plan_list->sole}
                    </td>
                    <td colspan="2" >
                       沿条
                    </td>
                    <td colspan="5" style="text-align: left">
                      {$onedata->plan_list->welt}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" >
                       注塑要求
                    </td>
                    <td colspan="6" style="text-align: left">
                      {$onedata->inject_mold_ask}
                    </td>
                    <td colspan="2" >
                       备注
                    </td>
                    <td colspan="5" style="text-align: left">
                      {$onedata->plan_remark}
                    </td>
                </tr>
                 <tr>
                    <td colspan="2"  >
                      工艺要求
                    </td>
                    <td colspan="13"  style="text-align: left;">
                       {$onedata->plan_list->carft_ask}
                    </td>
                </tr>
            </table>
               {$this->printFooter('派工员:'.$onedata->dispatch_user_name,2,'',
            1,'计划说明:'.$onedata->plan_list->plan_describe,3)}
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }
    //单个
    private function soleDispatchSimpleTCPDF($width, $height,$datas,$total_num,$no){
        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '鞋底派工单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(5, 5, 5);//页面间隔
        $pdf->setFontSubsetting(true);
        $pdf->SetAutoPageBreak(false,0);
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        $onedata = $datas[0];
        $dispatch_type = config('plan.dispatch_type')[$onedata->dispatch_info->type];
        $codesum=[
            33=>0,
            34=>0,
            35=>0,
            36=>0,
            37=>0,
            38=>0,
            39=>0,
            40=>0,
            41=>0,
        ];
        foreach ($datas as $k=>$vv){
            for($i=33;$i<=41;$i++){
                if($vv->spec==$i){
                    $codesum[$i]+=$vv->num;
                }
            }
        }
        //入库数据
        $total_num = is_float_number($total_num);
        $data_html = '';
            for($i=33;$i<=41;$i++){
                $data_html.="<td >".($codesum[$i]>0?is_float_number($codesum[$i]):'')."</td>";
            }
        $data_detail = "<tr>
                    <td >数量</td>".$data_html."<td  colspan=\"2\">{$total_num}</td></tr>";
        $delivery_date = date('Y-m-d',strtotime($onedata->plan_list->delivery_date));
        $dispatch_at = date('Y-m-d',strtotime($onedata->created_at));
        $company_name = config('admin.company_name');
        $htmltrab = <<<EOF
            {$this->style}

            <table>
                <tr>

                    <td class="page-title"  colspan="3">{$company_name}</td>

                </tr>
                <tr >
                 <td >生产周期:{$onedata->plan_list->product_time}</td>
                    <td class="page-sub-title">{$title}</td>
                      <td style="text-align: right;text-decoration: none" class="page-title">No:{$no}</td>
                </tr>
            </table>
             <table >
                <tr style="border-bottom:none;line-height: 15px;">
                    <td colspan="3" >派工时间:{$dispatch_at}</td>
                    <td  colspan="2">交货日期:{$delivery_date}</td>
                    <td  colspan="4" style="text-align: center">客户订单:{$onedata->dispatch_info->client_order_no}</td>
                    <td  colspan="3" style="text-align: right">派工类型:{$dispatch_type}</td>
                </tr>
            </table>
            <table class="data-table">
                <tr style="border-bottom:none">
                    <td colspan="1">计划单号</td>
                    <td colspan="2">{$onedata->dispatch_info->plan_list_no}</td>
                    <td  colspan="1">客户</td>
                    <td  colspan="2">{$onedata->dispatch_info->client_name}</td>
                    <td  colspan="1">客户型号</td>
                    <td  colspan="2">{$onedata->dispatch_info->client_model}</td>
                    <td  colspan="1">雷力型号</td>
                    <td  colspan="2">{$onedata->dispatch_info->company_model}</td>
                </tr>
                <tr style="border-bottom:none">
                    <td colspan="1">刀模</td>
                    <td colspan="2">{$onedata->plan_list->knife_mold}</td>
                    <td  colspan="1">用料</td>
                    <td  colspan="3">{$onedata->dispatch_info->sole_material_name}</td>
                    <td  colspan="1">工艺颜色</td>
                    <td  colspan="4">{$onedata->dispatch_info->craft_color_name}</td>

                </tr>
                <tr>
                    <td >码段</td>
                    <td >33码</td>
                    <td >34码</td>
                    <td >35码</td>
                    <td >36码</td>
                    <td >37码</td>
                    <td >38码</td>
                    <td >39码</td>
                    <td >40码</td>
                    <td >41码</td>
                    <td colspan="2">合计</td>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="1" >
                       革片
                    </td>
                    <td colspan="11" style="text-align: left">
                      {$onedata->plan_list->leather_piece}
                    </td>
                </tr>
                <tr>
                    <td colspan="1" >
                       鞋跟
                    </td>
                    <td colspan="5" style="text-align: left">
                       {$onedata->plan_list->sole}
                    </td>
                    <td colspan="2" >
                       注塑要求
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$onedata->inject_mold_ask}
                    </td>
                </tr>
                 <tr>
                    <td colspan="1" >
                       沿条
                    </td>
                    <td colspan="5" style="text-align: left">
                       {$onedata->plan_list->welt}
                    </td>
                    <td colspan="2" >
                       备注
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$onedata->plan_remark}
                    </td>
                </tr>
                 <tr>
                    <td  >
                      工艺要求
                    </td>
                     <td colspan="11"  style="text-align: left;">
                       {$onedata->plan_list->craft_ask}
                    </td>
                </tr>
            </table>
            {$this->printFooter('派工员:'.$onedata->dispatch_info->dispatch_user_name,2,'',1,
            '计划说明:'.$onedata->plan_list->plan_describe,3)}
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }

    private function soleDispatchSimpleCodeTCPDF($width, $height,$datas,$total_num,$no){
        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '鞋底派工单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetAutoPageBreak(false,0);
        $pdf->SetMargins(5, 5, 5);//页面间隔
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();

        $dispatch_type = config('plan.dispatch_type')[$datas->dispatch_info->type];
        $codesum=[
            33=>0,
            34=>0,
            35=>0,
            36=>0,
            37=>0,
            38=>0,
            39=>0,
            40=>0,
            41=>0,
        ];
        $data_html = '';
        for($i=33;$i<=41;$i++){
            if($datas->spec==$i){
                $codesum[$i]+=$datas->num;
            }
            $data_html.="<td >".($codesum[$i]>0?is_float_number($codesum[$i]):'')."</td>";
        }
        //入库数据
        $total_num = is_float_number($total_num);
        $data_detail = "<tr>
                    <td >数量</td>".$data_html." <td  colspan=\"2\">{$total_num}</td></tr>";
        $delivery_date = date('Y-m-d',strtotime($datas->plan_list->delivery_date));
        $dispatch_at = date('Y-m-d',strtotime($datas->created_at));
        $company_name = config('admin.company_name');
        $htmltrab = <<<EOF
            {$this->style}

             <table>
                <tr>
                    <td class="page-title" colspan="3">{$company_name}</td>
                </tr>
                <tr >
                    <td >生产周期:{$datas->plan_list->product_time}</td>
                    <td class="page-sub-title" >{$title}</td>
                    <td  style="text-align: right;text-decoration: none" class="page-title">No:{$no}</td>
                </tr>
            </table>
             <table >
                <tr style="border-bottom:none;line-height: 15px;">
                    <td colspan="3" >派工时间:{$dispatch_at}</td>
                    <td  colspan="2">交货日期:{$delivery_date}</td>
                    <td  colspan="4" style="text-align: center">客户订单:{$datas->dispatch_info->client_order_no}</td>
                    <td  colspan="3" style="text-align: right">派工类型:{$dispatch_type}</td>
                </tr>
            </table>

            <table class="data-table">
                <tr style="border-bottom:none">
                    <td colspan="1">计划单号</td>
                    <td colspan="2">{$datas->dispatch_info->plan_list_no}</td>
                    <td  colspan="1">客户</td>
                    <td  colspan="2">{$datas->dispatch_info->client_name}</td>
                    <td  colspan="1">客户型号</td>
                    <td  colspan="1">{$datas->dispatch_info->client_model}</td>
                    <td  colspan="1">雷力型号</td>
                    <td  colspan="2">{$datas->dispatch_info->company_model}</td>
                </tr>
                <tr style="border-bottom:none">
                    <td colspan="1">刀模</td>
                    <td colspan="2">{$datas->plan_list->knife_mold}</td>
                    <td  colspan="1">用料</td>
                    <td  colspan="2">{$datas->dispatch_info->sole_material_name}</td>
                    <td  colspan="1">工艺颜色</td>
                    <td  colspan="4">{$datas->dispatch_info->craft_color_name}</td>

                </tr>
                <tr>
                    <td >码段</td>
                    <td >33码</td>
                    <td >34码</td>
                    <td >35码</td>
                    <td >36码</td>
                    <td >37码</td>
                    <td >38码</td>
                    <td >39码</td>
                    <td >40码</td>
                    <td >41码</td>
                    <td colspan="2">合计</td>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="1" >
                       革片
                    </td>
                    <td colspan="10" style="text-align: left">
                      {$datas->plan_list->leather_piece}
                    </td>
                </tr>
                <tr>
                    <td colspan="1" >
                       鞋跟
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$datas->plan_list->sole}
                    </td>
                    <td colspan="2" >
                       注塑要求
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$datas->dispatch_info->inject_mold_ask}
                    </td>
                </tr>
                 <tr>
                    <td colspan="1" >
                       沿条
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$datas->plan_list->welt}
                    </td>
                    <td colspan="2" >
                       备注
                    </td>
                    <td colspan="4" style="text-align: left">
                       {$datas->dispatch->plan_remark}
                    </td>
                </tr>
                 <tr>
                    <td  >
                      工艺要求
                    </td>
                     <td colspan="10"  style="text-align: left;">
                       {$datas->plan_list->craft_ask}
                    </td>
                </tr>
            </table>
            {$this->printFooter('派工员:'.$datas->dispatch_info->dispatch_user_name,2,'',1,
            '计划说明:'.$datas->dispatch_info->plan_describe,3)}
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }
    //箱标打印
    public function boxLabelDispatchTable($ids,$no){
        $idarr = explode(',',$ids);
        if(count($idarr)>1){
            //多个鞋底派工
            $datas = DispatchDetail::with('dispatch_info')->whereIn('id',$idarr)->get();
            $total_num = DispatchDetail::whereIn('id',$idarr)->sum('num');
            return $this->boxLabelTCPDF(150,105,$datas,$total_num,$no);
        }else{
            //单个鞋底派工
            $datas = DispatchDetail::with('dispatch_info')->where('id',$ids)->get();
            $total_num = $datas[0]->num;
            return $this->boxLabelTCPDF(150,105,$datas,$total_num,$no);
        }
    }

    //箱标打印
    private function boxLabelTCPDF($width, $height,$data,$total_num,$no){
        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '箱标派工单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->SetAutoPageBreak(false,0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        //  $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0, 0,0)));
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(5, 15, 5);//页面间隔
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        //数据
        $code='';
        $codenum='';
        $codeallnum = count($data);
        $extra_empty_html = '';
        for ($i=0;$i<(9-$codeallnum);$i++){
            $extra_empty_html.='<td></td>';
        }
        foreach ($data as $kk=>$vv){
            $code.='<td>'.$vv['spec'].'#</td>';
            $codenum.='<td>'.is_float_number($vv['num']).'</td>';
        }
        $huizong = is_float_number($total_num);
        $onedata = $data[0];
        $htmltrab = <<<EOF
            {$this->style}
             <table class="table-line-2">
                <tr style="border-bottom:none">
                    <td >客户:{$onedata->dispatch_info->client_name}</td>
                    <td  style="text-align: right">生产商:新雷力</td>
                </tr>
                <tr style="border-bottom:none">
                    <td  >No:{$no}</td>
                </tr>
            </table>
            <table class="data-table table-line-2">
                <tr style="border-bottom:none">
                    <td  colspan="2">客户订单</td>
                    <td  colspan="8">{$onedata->dispatch_info['client_order_no']}</td>
                </tr>
                <tr>
                    <td colspan="2">工艺颜色</td>
                    <td colspan="8">{$onedata->dispatch_info['craft_color_name']}</td>
                </tr>
                 <tr>
                    <td colspan="2">计划单号</td>
                    <td colspan="3">{$onedata->dispatch_info['plan_list_no']}</td>
                    <td colspan="2">产品型号</td>
                    <td colspan="3">{$onedata->dispatch_info['company_model']}</td>
                </tr>
                <tr>
                    <td colspan="2">码段</td>
                    {$code}
                    {$extra_empty_html}
                </tr>
                <tr>
                    <td colspan="2">数量</td>
                    {$codenum}
                    {$extra_empty_html}
                </tr>
                <tr>
                    <td colspan="2">
                       合计
                    </td>
                    <td colspan="9" >
                       {$huizong}
                    </td>
                </tr>
            </table>
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }

    //单号查询的鞋底派工
    public function boxLabelDispatchNoTable($no){
        $paper =  BoxLabelDispatchPaper::where('no',$no)
            ->where('is_void',0)
            ->first();
        $ids = BoxLabelDispatchPaperDetail::where('box_label_dispatch_paper_id',$paper->id)
            ->pluck('dispatch_detail_id');
        $data = DispatchDetail::whereIn('id',$ids)->get();
        $total_num =  DispatchDetail::whereIn('id',$ids)->sum('num');
        return $this->boxLabelTCPDF(150,105,$data,$total_num,$no);
    }

    //发货单批量打印
    public function deliveryTable($ids,$no){
        $idarr = explode(',',$ids);
        if(count($idarr)>1){
            $data = DeliveryDetail::with(['plan_list_info','delivery_info'])->whereIn('id',$idarr)->get();
            return $this->deliveryMultiTCPDF(210.4,93,$data,$no);
        }else{
            $data = DeliveryDetail::with(['plan_list_info','delivery_info'])->where('id',$ids)->first();
            return $this->deliverySimpleTCPDF(210.4,93,$data,$no);
        }
    }
    //单条成品发货单
    private function deliverySimpleTCPDF($width, $height,$data,$no){
        $pdf = new \TCPDF('p', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '发货单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(5, 5, 5);//页面间隔
        $pdf->setFontSubsetting(true);
        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        //查询计划单的detail
        $allsize = PlanListDetail::where('plan_list_id',$data['plan_list_id'])
            ->get(['id','spec'])->toArray();
        $allsizeHtml='';
        $thissizeHtml='';
        $show_num = is_float_number($data['num']);
        $show_price = is_float_number($data['delivery_info']['delivery_price']);
        $allnum =count($allsize);
        for ($i=0;$i<10;$i++){
            if($i<$allnum){
                if($allsize[$i]['id']==$data['plan_list_detail_id']){
                    $allsizeHtml.='<td>'.$allsize[$i]['spec'].'</td>';
                    $thissizeHtml.='<td>'.is_float_number($show_num).'</td>';
                }else{
                    $allsizeHtml.='<td>'.$allsize[$i]['spec'].'</td>';
                    $thissizeHtml.='<td>0</td>';
                }
            }else{
                $allsizeHtml.='<td></td>';
                $thissizeHtml.='<td></td>';
            }
        }

        $total_money = $data['delivery_info']['delivery_price']*$data['num'];
        //入库数据
        $data_detail = <<<EOA

            <tr>
              {$thissizeHtml}
                <td >{$show_num}</td>
                <td >{$show_price}</td>
                <td >¥ {$total_money}</td>
            </tr>
EOA;
        ;
        $moneyBig = convertRmbToUpper($total_money);
        $htmltrab = <<<EOF
            {$this->style}
            {$this->printHeader(config('admin.company_name'),$title,'客户:'.$data['chengpinfahuo']['kehu_name'],4,'&nbsp;',2,'No:'.$no,4)}
            <table class="data-table">
                <tr style="border-bottom:none">
                    <td colspan="3">客户工艺:{$data['delivery_info']['carft_name']}</td>
                    <td  colspan="5">客户单号:{$data['delivery_info']['client_order_no']}</td>
                    <td  colspan="5">新雷力计划单号:{$data['delivery_info']['plan_list_no']}</td>
                </tr>
                <tr>
                  {$allsizeHtml}
                    <td >合计</td>
                    <td >单价</td>
                    <td >金额</td>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="7" style="text-align: left">
                       鞋底型号:{$data['delivery_info']['company_model']}
                    </td>
                    <td colspan="6" style="text-align: right">
                       金额大写:{$moneyBig}
                    </td>
                </tr>
                 <tr>
                    <td colspan="13"  style="text-align: left;line-height: 20px;">
                      备注:请核对后签字，如有异常，请在24小时内联系，否则视为确认。
                    </td>
                </tr>
                 <tr>
                  <td colspan="13">
                    <table>
                        <tr>
                            <td style="text-align: left">送货员:{$data['delivery_info']['personnel_name']}</td>
                            <td>业务员:{$data['plan_list_info']['personnel_name']}</td>
                            <td>收货员:{$data['delivery_info']['client_name']}</td>
                            <td>收货人签收:</td>
                        </tr>
                    </table>
                    </td>
                </tr>
                 <tr>
                    <td colspan="13"  style="text-align: left;line-height: 20px;">
                      描述:{$data['delivery_info']['content']}
                    </td>
                </tr>
                 <tr>
                  <td colspan="13">
                    <table>
                        <tr>
                            <td style="text-align: left">公司地址:</td>
                            <td>传真:</td>
                            <td>公司电话:</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }
    //多条成品发货单
    private function deliveryMultiTCPDF($width, $height,$data,$no){

        $pdf = new \TCPDF('L', 'mm', array($width, $height),true, 'UTF-8', false);
        // 设置文档信息
        $title = '发货单';
        $pdf->SetCreator(config('admin.name'));
        $pdf->SetAuthor(config('admin.name'));
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords( 'PDF, 雷力,'.$title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(5, 5, 5);//页面间隔
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('stsongstdlight', '', 10);
        $pdf->AddPage();
        $showdataarr=[];
        $allmoney=0;
        $allnum=0;
        $i=0;
        $data=$data->toArray();
        //  dd($data);
        $total_size=[
            33=>0,34=>0,35=>0,36=>0,37=>0,
            38=>0,39=>0,40=>0,41=>0
        ];

        foreach($data as $kk=>$vv){
            $num=is_float_number($vv['num']);
            $price=is_float_number($vv['delivery_info']['delivery_price']);
            $calcu_allmoney =$vv['delivery_info']['delivery_price']*$vv['num'];

            if(!isset($showdataarr[$vv['plan_list_id']])){
                $showdataarr[$vv['plan_list_id']] = [
                    'client_order_no'=>$vv['delivery_info']['client_order_no'],
                    'company_model'=>$vv['delivery_info']['company_model'],
                    'price'=>$price,
                    'num'=>$vv['num'],
                    'money'=>$calcu_allmoney,
                ];
                $allmoney+=$calcu_allmoney;
                $allnum+=$vv['num'];
                $showdataarr[$vv['plan_list_id']]['num']   = $vv['num'];
                $showdataarr[$vv['plan_list_id']]['money'] = $calcu_allmoney;
                for($i=33;$i<=41;$i++){
                    $showdataarr[$vv['plan_list_id']][$i] = $vv['spec']==$i?$num:0;
                }
            }else {
                $allmoney += $calcu_allmoney;
                $allnum   += $vv['num'];

                $showdataarr[$vv['plan_list_id']]['num']   += $vv['num'];
                $showdataarr[$vv['plan_list_id']]['money'] += $calcu_allmoney;
                for($i=33;$i<=41;$i++){
                    $showdataarr[$vv['plan_list_id']][$i] = $vv['spec']==$i?$num:0;
                }
            }
        }
        for($j=33;$j<=41;$j++){
            $total_size[$j] = array_sum(array_column($showdataarr,$j));
        }
        //入库数据
        $data_detail='';
        foreach ($showdataarr as $vk=>$va){
            $data_detail.='<tr>
               <td >'.($i+1).'</td>
                <td width="15%">'.$va['client_order_no'].'</td>
             <td width="15%">'.$va['company_model'].'</td>
                <td >'.$va[33].'</td>
                <td >'.$va[34].'</td>
                <td >'.$va[35].'</td>
                <td >'.$va[36].'</td>
                <td >'.$va[37].'</td>
                <td >'.$va[38].'</td>
                <td >'.$va[39].'</td>
                <td >'.$va[40].'</td>
                <td >'.$va[41].'</td>
                <td >'.$va['num'].'</td>
                <td >'.$va['price'].'</td>
                <td >¥ '.$va['money'].'</td>
            </tr>';
            $i++;
        }
        $allmoney_text = convertRmbToUpper($allmoney);
        $htmltrab = <<<EOF
            {$this->style}
            {$this->printHeader(config('admin.company_name'),$title,'To:'.$data[0]['chengpinfahuo']['kehu_name'],4,'&nbsp;',2,'No:'.$no,4)}
            <table class="data-table">
                <tr style="border-bottom:none">
                    <td colspan="3">客户型号:{$data[0]['delivery_info']['client_model']}</td>
                    <td  colspan="8">客户工艺:{$data[0]['delivery_info']['craft_name']}</td>
                    <td  colspan="3">鞋底型号:{$data[0]['delivery_info']['company_model']}</td>
                </tr>
                <tr>
                    <td  width="5%">序号</td>
                    <td  width="15%">客户单号</td>
                    <td  width="15%">雷力单号</td>
                    <td width="5%">33</td>
                    <td width="5%">34</td>
                    <td width="5%">35</td>
                    <td width="5%">36</td>
                    <td width="5%">37</td>
                    <td width="5%">38</td>
                    <td width="5%">39</td>
                    <td width="5%">40</td>
                    <td width="5%">41</td>
                    <td width="8%">合计</td>
                    <td width="8%">单价</td>
                    <td width="9%">金额</td>
                </tr>
                {$data_detail}
                <tr>
                    <td colspan="3" style="text-align: right;line-height: 20px;">
                       总合计
                    </td>
                    <td >{$total_size['33']}</td>
                    <td >{$total_size['34']}</td>
                    <td >{$total_size['35']}</td>
                    <td >{$total_size['36']}</td>
                    <td >{$total_size['37']}</td>
                    <td >{$total_size['38']}</td>
                    <td >{$total_size['39']}</td>
                    <td >{$total_size['40']}</td>
                    <td >{$total_size['41']}</td>
                    <td >{$allnum}</td>
                    <td >-</td>
                    <td >¥ {$allmoney}</td>
                </tr>
                <tr>
                    <td colspan="15">
                        <table>
                            <tr>
                                <td style="text-align: left">描述:</td>
                                <td style="text-align: right">金额大写:{$allmoney_text}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                 <tr>
                    <td colspan="15"  style="text-align: left;line-height: 20px;">
                      备注:请核对后签字，如有异常，请在24小时内联系，否则视为确认。
                    </td>
                </tr>
                 <tr>
                  <td colspan="15">
                    <table>
                        <tr>
                            <td style="text-align: left">送货员:{$data[0]['delivery_info']['psersonnel_name']}</td>
                            <td>业务员:{$data[0]['plan_list_info']['personnel_name']}</td>
                            <td>发货员:{$data[0]['delivery_info']['client_name']}</td>
                            <td>收货人签收:</td>
                        </tr>
                    </table>
                    </td>
                </tr>
                 <tr>
                  <td colspan="14">
                    <table>
                        <tr>
                            <td style="text-align: left">公司地址:</td>
                            <td>传真:</td>
                            <td>公司电话:</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>
EOF;
        $pdf->writeHTML($htmltrab);
        //输出PDF
        return $pdf->Output($title.'.pdf', 'I');
    }
}
