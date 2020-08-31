<?php

return [
    /**鞋底车间申购**/
    'sole_workshop_subscribe_detail_check_status'=>[
        ''   => '全部',
        'verify'    => '已审核',
        'overrule'    => '驳回',
        'unreviewed'    => '未审核',
        'part'    => '部分审核'
    ],
    'sole_workshop_subscribe_detail_check_status_pure'=>[
        'verify'    => '已审核',
        'overrule'    => '驳回',
        'unreviewed'    => '未审核',
        'part'    => '部分审核'
    ],
    /**注塑工价**/
    'inject_mold_out_nums' => [
        '1'=>'1出1双','2'=>'1出2双'
    ],
    'inject_mold_types' => [
        'sole'=>'鞋底',
        'film_ottom'=>'片底',
        'heel'=>'鞋跟',
        'net_heel'=>'净跟',
        'chumian'=>'出面',
        'waterproof_platform'=>'防水台',
        'welt'=>'沿条',
        'rubber'=>'橡胶'
    ],

    /**模具资料信息**/
    'mold_information_property'=>[
        'all'=>'所有',
        'first'=>'第一次开模',
        'addsize'=>'第二次开模',
        'jiama'=>'加码',
        'fix'=>'维修',
        'change'=>'换型腔'
    ],
    /**模具价格**/
    'mold_price_check'=>[0=>'未验收',1=>'已验收'],
    'mold_price_status'=>[0=>'禁用',1=>'启用'],
    'mold_price_check_text'=>[
        0=>'<span class="text-danger">未验收</span>',
        1=>'<span class="text-success">已验收</span>'
    ],
    'mold_price_status_text'=>[
        0=>'<span class="text-danger">禁用</span>',
        1=>'<span class="text-success">启用</span>'
    ],
    /**弹框宽高**/
    'dialog'=>[
      'width'=>'900px',
      'height'=>'600px'
    ],
    /**人事资料**/
    'personnel_status'=>[
         'on'=>'在职',
        'off'=>'离职'
    ],
    'personnel_work_status'=>[
         'formal'=>'正式员工',
        'disformal'=>'试用员工'
    ],
    'personnel_sex'=>[
       'boy'=>'男',
        'girl'=>'女'
    ],
    'delivery_type'=>[
        'delivery'=>'成品发货'
    ],
    'print_status'=>[
      '0'=>'未打印',
      '1'=>'已打印'
    ],
    'transit_storage_type'=>[
        'blank'=>'毛坯',
        'silver_plating'=>'电镀银',
        'electroplating_gun'=>'电镀枪',
        'sole'=>'鞋底',
        'electroplating_gold'=>'电镀金',
        'crystal_heel'=>'水晶跟',
        'waterproof_platform'=>'防水台',
        'come_forward'=>'出面'
    ],
    'transit_storage_in_style'=>[
      'in'=>'内部入库',
      'out'=>'外派入库',
    ],
    'transit_storage_out_style'=>[
        'in'=>'内部出库',
        'out'=>'外派出库',
    ],
    'transit_storage_in_type'=>[
        'inject_mold_inner'        => '内部注塑入库',
        'inject_mold_outer'        => '外派注塑入库',
        'electroplating_outer'       => '外派电镀入库',
        'storage_count'       => '仓库盘点入库',
    ],

    'transit_storage_out_type'=>[
        'pqcjmpjgck'=>'喷漆车间毛坯加工出库',
        'pqcjddbcpjgck'=>'喷漆车间电镀成品加工出库',
        'wpddjgck'=>'外派电镀加工出库',
        'zcpck'=>'转成品出库（转入成品仓库）',
        'bfck'=>'报废出库（转入报废仓库）',
        'wppqmpck'=>'外派喷漆毛坯出库',
        'wppqddbcpck'=>'外派喷漆电镀半成品出库',
        'other'=>'其他'

    ],
    'transit_storage_count_type'=>[
        'inject_mold'=>'注塑车间',
        'outer'=>'外派注塑',
        'blank_electroplating_outer'=>'外派毛坯电镀加工'
    ],
    'paper_check'=>[
        0=>'未验收',
        1=>'已验收',
    ],
    'paper_void' => [
        0=>'正常',
        1=>'已作废',
    ],
    'plan_process'=>[
        'none'=>'未处理',
        'sole'=>'鞋底派工',
        'inject_mold'=>'注塑派工',
        'box_label'=>'箱标派工',
        'put_in_storage'=>'中转仓入库',
        'out_of_storage'=>'中转仓出库',
        'delivery'=>'发货'
    ],
     'plan_status_simple_html'=> [
        '0' => '<span class="text-danger">未处理</span>',
        '1' => '<span class="text-default">鞋底派工</span>',
        '2' => '<span class="text-warning">注塑派工</span>',
        '3' => '<span class="text-warning">箱标派工</span>',
        '4' => '<span class="text-info">成品出库</span>',
        '5' => '<span class="text-success">已完成</span>'
    ],
    'plan_status_html'=>[
        '0' => '<span class="text-info">未处理</span>',
        '1' => '<span class="text-warning">处理中</span>',
        '2' => '<span class="text-success">已完成</span>'
    ],
    'plan_status_simple' => [
        '0' => '未处理',
        '1' => '鞋底派工',
        '2' => '注塑派工',
        '3' => '箱标派工',
        '4' => '成品出库',
        '5' => '已完成'
    ],
    'dispatch_type'=>[
        'sole'=>'鞋底加工',
        'inject_mold'=>'注塑派工',
        'box_label'=>'箱标打印',
    ],
    'dispatch_process_workshop'=>[
        'sole'=>'鞋底加工车间',
        'inject_mold'=>'注塑加工车间',
        'box_label'=>'箱标处理',
    ],
    'dispatch_process_department'=>[
        'sole'=>'片底车间',
        'inject_mold'=>'注塑车间',
        'box_label'=>'总经办',
    ],
    'dsipatch_type'=>[
      'sole'=>'鞋底派工',
      'inject_mold'=>'注塑派工',
      'box_label'=>'箱标派工',
    ],
    'status'=>[
        '0'=>'未处理',
        '1'=>'进行中',
        '2'=>'已完成',
    ],
    'dsipatch_status'=>[
        '0' => '未派工',
        '1'  => '已派工',
        '2' => '已完成',
    ],
    'normal_size'=>[33,34,35,36,37,38,39,40,41],
    'client_sole_information_size'=>[34,35,36,37,38,39],
    /**规格**/
    'type' => [
        [
            'type'=>'couple',
            'text'=>'一双'
        ],
        [
            'type'=>'left',
            'text'=>'左'
        ],
        [
            'type'=>'right',
            'text'=>'右'
        ],
    ],
    'type_text' => [
        'couple'=>  '一双',
        'left'=>'左',
        'right'=>'右',
    ],
    /**尺码
     *   1-7是从34开始填充34、35、36、37、38、39、40
     *   8-9从33开始填充即规格尺码显示为33、34、35、36、37、38、39、40、41
     */
    'spec'=>[
        '1'=>[34],
        '2'=>[34,35],
        '3'=>[34,35,36],
        '4'=>[34,35,36,37],
        '5'=>[34,35,36,37,38],
        '6'=>[34,35,36,37,38,39],
        '7'=>[34,35,36,37,38,39,40],
        '8'=>[33,34,35,36,37,38,39,40],
        '9'=>[33,34,35,36,37,38,39,40,41],
    ]
];
?>
