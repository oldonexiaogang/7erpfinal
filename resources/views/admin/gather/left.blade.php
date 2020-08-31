<style>
    .text-bold-5{
        font-weight: bolder!important;
    }

</style>

<link rel="stylesheet" href="/vendors/dcat-admin/dcat/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.css">


<script src="/vendors/dcat-admin/dcat/plugins/moment/moment-with-locales.min.js"></script>
<script src="/vendors/dcat-admin/dcat/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>

<table class="table custom-data-table dataTable table-bordered complex-headers ">
    <tr>
        <td class="text-bold-5">日期</td>
        <td colspan="2">
            <div class="input-group" >
                 <span class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="feather icon-calendar"></i></span>
                </span>
                <input autocomplete="off" type="text" name="left_date_start"
                       class="form-control " id="left_date_start_box"/>
            </div>
        </td>
        <td class="text-bold-5">至</td>
        <td colspan="2">
            <div class="input-group" >
                 <span class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="feather icon-calendar"></i></span>
                </span>
                <input autocomplete="off" type="text" name="left_date_end"
                       class="form-control "  id="left_date_end_box"/>
            </div>
        </td>
        <td>
        <button class="btn btn-primary btn-mini btn-sm" id="left_date_search">
            确定
        </button>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-bold-5">今日订单数</td>
        <td colspan="2">{{$all['num']}}</td>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td class="text-bold-5">TPU</td>
        <td colspan="2">{{$all['tpu']}}</td>
        <td class="text-bold-5">橡胶</td>
        <td>{{$all['rubber']}}</td>
        <td class="text-bold-5">需改色</td>
        <td>{{$all['color']}}</td>
    </tr>
    <tr>
        <td class="text-bold-5">沿条底</td>
        <td colspan="2">{{$all['welt']}}</td>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td class="text-bold-5" colspan="2">今日成品发货</td>
        <td>{{$chengpin['day']}}</td>
        <td  class="text-bold-5" colspan="2">本月累计发货量</td>
        <td colspan="2">{{$chengpin['month']}}</td>
    </tr>
    <tr>
        <td class="text-bold-5 text-left" colspan="7" >当日报单汇总:</td>
    </tr>
    <tr>
        <td class="text-bold-5">序号</td>
        <td class="text-bold-5">客户</td>
        <td class="text-bold-5">订单数量</td>
        <td class="text-bold-5">TPU</td>
        <td class="text-bold-5">橡胶</td>
        <td class="text-bold-5">沿条底</td>
        <td class="text-bold-5">订单排名</td>
    </tr>
    @foreach($data as $k=>$d)
        <tr>
            <td>{{($k+1)}}</td>
            <td>{{$d['client_name']}}</td>
            <td>{{$d['num']}}</td>
            <td>{{$d['TPU']}}</td>
            <td>{{$d['rubber']}}</td>
            <td>{{$d['welt']}}</td>
            <td>{{($k+1)}}</td>
        </tr>
    @endforeach
</table>

<script>
    $(function () {
        $('#left_date_search').on('click',function (){
            var startdate = $('#left_date_start_box').val()
            var enddate = $('#left_date_end_box').val()
            var pre = window.location.href.split('?')[0];
            var rstart = getQueryVariable('rstart')?getQueryVariable('rstart'):''
            var  rend = getQueryVariable('rend')?getQueryVariable('rend'):''
            window.location.href =pre+'?lstart='+startdate+'&lend='+enddate+'&rstart='+rstart+'&rend='+rend
        })
        $('#left_date_start_box').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            locale:'zh-CN',
            defaultDate:"{{$time['lstart']}}"
        })
        $('#left_date_end_box').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            locale:'zh-CN',
            defaultDate:"{{$time['lend']}}"
        })
    })
    function getQueryVariable(variable)
    {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){return pair[1];}
        }
        return('');
    }
</script>
