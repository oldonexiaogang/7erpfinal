<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1,IE=edge">
    {{-- 默认使用谷歌浏览器内核--}}
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>@if(! empty($header)){{ $header }} | @endif {{ Dcat\Admin\Admin::title() }}</title>

    @if(! config('admin.disable_no_referrer_meta'))
        <meta name="referrer" content="no-referrer"/>
    @endif

    @if(! empty($favicon = Dcat\Admin\Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    {!! admin_section(\AdminSection::HEAD) !!}

    {!! Dcat\Admin\Admin::asset()->cssToHtml() !!}

    {!! Dcat\Admin\Admin::asset()->headerJsToHtml() !!}

    @yield('head')
</head>

<body class="dcat-admin-body full-page ">

<script>
    var Dcat = CreateDcat({!! Dcat\Admin\Admin::jsVariables() !!});
</script>

{{-- 页面埋点 --}}
{!! admin_section(\AdminSection::BODY_INNER_BEFORE) !!}
<div class="app-content content">
    <div class="wrapper" >
        <style>
            .table tr td{
                height:36px!important;
            }
        </style>
        <div class="box box-info">
            @if(!(isset($is_dialog)&&$is_dialog)&&$reback)
                <div class="box-header with-border">
                    <h3 class="box-title">{{$title}}</h3>
                    <div class="box-tools">
                        <div class="btn-group pull-right" style="margin-right: 5px">
                            <a href="{{$reback}}"
                               class="btn btn-sm btn-default" title="列表">
                                <i class="fa fa-list"></i><span class="hidden-xs">&nbsp;列表</span></a>
                        </div>
                    </div>
                </div>
            @endif
            <div class="box-body " style="display: block;">
                <div class="row">
                    @foreach ($info as $value )
                        @if((int)$value['length']>1)
                            <div class="col-md-{{$value['length']}} col-sm-{{$value['length']}}">
                                <label style="width:120px;padding:5px 0 ;font-style: 15px;">{{$value['label']}}:</label>
                                <label>{{$value['value']}}
                                    @if(isset($value['type']))
                                        <span class="text-{{$value['type']}}">{{$value['type_value']}}</span>
                                    @endif
                                </label>
                            </div>
                        @elseif($value['length']=='planListoneLine')
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped table-hover table-condensed">
                                    <tr>
                                        <th>尺码</th>
                                        <th>规格</th>
                                        <th>订单数</th>
                                    </tr>
                                    @if(count($value['value'])>0)
                                        @foreach ($value['value'] as $onedetail )
                                            <tr>
                                                <td>{{$onedetail['spec']}}</td>
                                                <td>{{$types[$onedetail['type']]}}</td>
                                                <td>{{$onedetail['num']}}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10">
                                                <p align="center">暂无数据</p>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif($value['length']=='xiedimoju')
                            <style>
                                .input-h{width:95px;height:36px;text-align:center;margin-right:10px;margin-bottom:5px;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;outline: none;border:1px solid #d9d9d9}
                            </style>

                            <table style="margin-left:20px">
                                <tr>
                                    <td>鞋底&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;模具生产详情规格</td>
                                    <td id="line1">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][0])?$value['value'][0]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][1])?$value['value'][1]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][2])?$value['value'][2]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][3])?$value['value'][3]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][4])?$value['value'][4]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][5])?$value['value'][5]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][6])?$value['value'][6]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][7])?$value['value'][7]:''}}">
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td id="line2">

                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][8])?$value['value'][8]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][9])?$value['value'][9]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][10])?$value['value'][10]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][11])?$value['value'][11]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][12])?$value['value'][12]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][13])?$value['value'][13]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][14])?$value['value'][14]:''}}">
                                        <input name="xiedi_model_spec[]" class="input-h" type="text" value="{{isset($value['value'][15])?$value['value'][15]:''}}">
                                    </td>
                                </tr>
                            </table>
                        @elseif($value['length']=='xiedioneline')
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped table-hover  table-condensed">
                                    <tr>
                                        <th>规格</th>
                                        <th>前高（mm）</th>
                                        <th>后高（mm）</th>
                                        <th>鞋跟净重克/双</th>
                                        <th>出面重g/双</th>
                                        <th>防水台重g/双</th>
                                        <th>鞋跟重量g/双</th>
                                        <th>油漆用量克/双</th>
                                        <th>模具付数</th>
                                        <th>备注</th>
                                    </tr>
                                    @if(count($value['value'])>0)
                                        @foreach ($value['value'] as $onedetail )
                                            <tr>
                                                <td>{{$onedetail['xiedi_spec']}}</td>
                                                <td>{{$onedetail['qiangao']}}</td>
                                                <td>{{$onedetail['hougao']}}</td>
                                                <td>{{$onedetail['xiegenjing_weight']}}</td>
                                                <td>{{$onedetail['chumian_weight']}}</td>
                                                <td>{{$onedetail['fangshuitai_weight']}}</td>
                                                <td>{{$onedetail['xiegeng_weight']}}</td>
                                                <td>{{$onedetail['youqi_weight']}}</td>
                                                <td>{{$onedetail['moju_num']}}</td>
                                                <td>{{$onedetail['remark']}}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10">
                                                <p align="center">暂无数据</p>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif($value['length']=='img')
                            <div class="col-md-12">
                                <label style="width:100px;">{{$value['label']}}:</label>
                                @if(is_array($value['value']))
                                    @foreach($value['value'] as $v)
                                        <img src="{{imgurl($v)}}" alt="" style="width:200px;height:auto">
                                    @endforeach
                                @endif
                                @if(is_string($value['value']))
                                    <img src="{{imgurl($value['value'])}}" alt="" style="width:200px;height:auto">
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{!! admin_section(\AdminSection::BODY_INNER_AFTER) !!}

{!! Dcat\Admin\Admin::asset()->jsToHtml() !!}

<script>Dcat.boot();</script>

</body>
</html>
