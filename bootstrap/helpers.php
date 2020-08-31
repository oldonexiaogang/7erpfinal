<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * dec: 计划单单号生成
 * @param $table
 * @param $pre
 * @param int $num
 * author : happybean
 * date: 2020-08-16
 */
function getOrderNo($table,$pre,$num=10,$title='no'){
    $lastinfo  =DB::table($table)->where($title,'like',$pre.date('Ymd').'%')
        ->orderBy('id','desc')->first();
    $number =$lastinfo?(int)substr($lastinfo->$title,$num)+1:1;
    $number =str_pad($number,3,"0",STR_PAD_LEFT);
    return $pre.str_replace("-","",Carbon::now()->toDateString()).$number;
}

/**
 * dec: 供应商获取编号
 * @param $table
 * @param $pre
 * @param int $num
 * author : happybean
 * date: 2020-08-16
 */
function getNo($table,$pre,$num=2,$title){
    $lastinfo  =DB::table($table)->orderBy('id','desc')->first();

    $number =$lastinfo?(int)substr($lastinfo->$title,$num)+1:1;
    $number =str_pad($number,5,"0",STR_PAD_LEFT);
    return $pre.$number;
}
function getOrderGang($table,$pre,$num=10){
    $lastinfo  =DB::table($table)->where('no','like',$pre.date('Ymd').'%')->orderBy('id','desc')->first();
    $number =$lastinfo?(int)substr($lastinfo->no,$num)+1:1;
    $number =str_pad($number,5,"0",STR_PAD_LEFT);
    return $pre.str_replace("-","-",Carbon::now()->toDateString()).'-'.$number;
}
/**
 * dec: 鞋底车间申购列表
 * @param $checkStatus
 * author : happybean
 * date: 2020-08-16
 */
function getCheck($checkStatus){

    switch ($checkStatus){
        case 'verify':
            $text='审核通过';
            $class='text-success';
            break;
        case 'overrule':
            $text='驳回';
            $class='text-danger';
            break;
        case 'unreviewed':
            $text='未审核';
            $class='text-info';
            break;
        case 'part':
            $text='部分审核';
            $class='text-warning';
            break;
    }
    return ['text'=>$text,'class'=>$class];
}
/**
 * dec: 图片完整路由
 * @param $img
 * author : happybean
 * date: 2020-05-04
 */
function imgurl($img){
    // 如果 image 字段本身就已经是完整的 url 就直接返回
    if (Str::startsWith($img, ['http://', 'https://'])) {
        return $img;
    }
    return \Storage::disk('admin')->url($img);
}
/**多位数组唯一**/
function assoc_unique($arr, $key)
{
    $tmp_arr = array();
    foreach ($arr as $k => $v) {

        if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

            unset($arr[$k]);

        } else {
            $tmp_arr[] = $v[$key];
        }

    }
    //sort($arr); //sort函数对数组进行排序
    return $arr;
}

/**
 * dec:批量更新
 * @param array $multipleData
 * @param string $tableName
 * author : happybean
 * date: 2020-08-16
 */
function batchUpdate($multipleData = array(), $tableName = ""){

    try {
        if (empty($multipleData)) {
            throw new \Exception("数据不能为空");
        }
        $firstRow  = current($multipleData);

        $updateColumn = array_keys($firstRow);
        // 默认以id为条件更新，如果没有ID则以第一个字段为条件
        $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
        unset($updateColumn[0]);
        // 拼接sql语句
        $updateSql = "UPDATE " . $tableName . " SET ";
        $sets      = [];
        $bindings  = [];
        foreach ($updateColumn as $uColumn) {
            $setSql = "`" . $uColumn . "` = CASE ";
            foreach ($multipleData as $data) {
                $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                $bindings[] = $data[$referenceColumn];
                $bindings[] = $data[$uColumn];
            }
            $setSql .= "ELSE `" . $uColumn . "` END ";
            $sets[] = $setSql;
        }
        $updateSql .= implode(', ', $sets);
        $whereIn   = collect($multipleData)->pluck($referenceColumn)->values()->all();
        $bindings  = array_merge($bindings, $whereIn);
        $whereIn   = rtrim(str_repeat('?,', count($whereIn)), ',');
        $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
        // 传入预处理sql语句和对应绑定数据
        return DB::update($updateSql, $bindings);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * dec: 数字变汉字大写
 * @param $ns
 * author : happybean
 * date: 2020-05-04
 */
/**
 * 人民币金额转大写
 * @param int $rmb  人民币金额
 * @param int $maxLength    显示长度
 * @return string
 */
function convertRmbToUpper($rmb = 0,$maxLength = 0){
    //大写符号
    $upperSymbol = array(
        '0' => '零',
        '1' => '壹',
        '2' => '贰',
        '3' => '叁',
        '4' => '肆',
        '5' => '伍',
        '6' => '陆',
        '7' => '柒',
        '8' => '捌',
        '9' => '玖'
    );
    //单位符号
    $unitSymbol = array(
        '-2' => '分',
        '-1' => '角',
        '0' => '整',
        '1' => '元',
        '2' => '拾',
        '3' => '佰',
        '4' => '仟',
        '5' => '万',
        '6' => '拾',
        '7' => '佰',
        '8' => '仟',
        '9' => '亿',
    );

    $upperRmb = '';

    if(empty($maxLength)){
        $maxLength = 0;
    }

    $rmbSplit = explode('.',$rmb);

    //整数部分
    $length = strlen($rmbSplit[0]);
    if($length > $maxLength){
        $maxLength = $length;
    }
    for($i=0;$i<$maxLength;$i++){
        if($i < $length){
            $char = substr($rmbSplit[0],$i,1);
            $unitChar = $unitSymbol[$length-$i];
            $upperRmb .= $upperSymbol[$char].$unitChar;
        }else{
            $upperRmb = $unitSymbol[$i+1] . $upperRmb;
        }
    }

    //小数部分
    if(!isset($rmbSplit[1])){
        //如果没有小部分，默认填充2位小数
        $rmbSplit[1] = '00';
    }
    $length = strlen($rmbSplit[1]);
    for($i=0;$i<$length;$i++){
        $char = substr($rmbSplit[1],$i,1);
        $unitChar = $unitSymbol[-($i+1)];
        $upperRmb .= $upperSymbol[$char].$unitChar;
    }

    return $upperRmb;
}

function is_float_number($number){
    if(ceil($number)==$number){
        return sprintf('%.0f',$number);
    }else{
        return sprintf('%.1f',$number);
    }
}

/**
 * 通过code获取计划单数量
 * @param $id
 * @param $code
 * @return mixed|string
 */
function getPlanListNumByCode($id,$code){
    $planlist = new \App\Models\PlanList();
    $arr = $planlist->getDetailNum($id,$code);
    if($arr['left']>0||$arr['right']>0){
        return $arr['left'].'/'.$arr['right'];
    }else{
        return  $arr['all'];
    }
}

/**
 * 获取票据单号
 * @param $table
 * @param $pre
 * @param int $num
 * @return string
 */

function getPaperOrder($table,$pre,$num=11){
    $lastinfo  =DB::table($table)->where('no','like',$pre.date('Y-m-d').'%')->orderBy('id','desc')->first();
    $number =$lastinfo?(int)substr($lastinfo->no,$num)+1:1;
    $number =str_pad($number,3,"0",STR_PAD_LEFT);
    return $pre.str_replace("-","-",Carbon::now()->toDateString()).'-'.$number;
}
function arraySort($array, $keys, $sort = SORT_DESC) {
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}
