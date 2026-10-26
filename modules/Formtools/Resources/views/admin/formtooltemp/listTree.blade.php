@include(moduleAdminTemplate($pageData['moduleName'])."public.header")
<body>
@if(!$pageData['popup'])
    @include(moduleAdminTemplate($pageData['moduleName'])."public.nav")
@endif
<div class="page-container">
    <div class="page-content">
        @if(!$pageData['popup'])
            @include(moduleAdminTemplate($pageData['moduleName'])."public.left")
        @endif
        <div class="content-wrapper">
            <div class="content" style="margin-top: 1rem;">
                @if(!$pageData['popup'])
                    @include(moduleAdminTemplate($pageData['moduleName'])."public.page",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
                @endif
                @if($pageData['searchFields'])
                    <form class="bs-example form-horizontal" method="get">
                        <div class="form-group">
                            @foreach($pageData['searchFields'] as $f)
                                @if(!in_array($f['formtype'],['hidden']))
                                    <div class="col-lg-2 mt-5">
                                @endif
                                @include(moduleAdminTemplate("formtools")."formsearchtemplates.".$f['formtype'],compact('f'))
                                @if(!in_array($f['formtype'],['hidden']))
                                    </div>
                                @endif
                            @endforeach
                            <div class="col-lg-2 mt-5">
                                <button type="submit" class="btn btn-sm btn-info">
                                    {{ getTranslateByKey('common_search') }}
                                </button>
                                <a href="{{$pageData['searchClearEmpty']?:url()->current()}}">
                                    <button type="button" class="btn btn-sm btn-danger">
                                        {{ getTranslateByKey('ft_ml_clear') }}
                                    </button>
                                </a>
                            </div>
                        </div>
                    </form>
                @endif
                <div class="">
                    <div class="table-responsive panel panel-default">
                        <div class="panel-heading">
                            @foreach($pageData['listActions'] as $act)
                                @php($topActionUrl = rtrim($act['actionUrl'].'?'.http_build_query($act['param'] ?? []), "?"))
                                <a class="label {{$act['cssClass']}} pull-right m-t-xs" style="margin-right: 10px;padding: 3px 8px;font-size: 12px;"
                                   @if($act['popup'])
                                       onclick="popupPage('{{$topActionUrl}}')"
                                   @else
                                       href="{{$topActionUrl}}"
                                   @endif
                                   @if(!empty($act['target'])) target="{{$act['target']}}" @endif>
                                    {{$act['actionName']}}
                                </a>
                            @endforeach
                            {{$pageData['subtitle']?:getTranslateByKey('ft_tmp_data_list')}}
                        </div>
                        <table class="table table-striped col-sm-12">
                            <thead>
                            <tr>
                                @foreach($pageData['fields'] as $f)
                                    <th>{{$f['name']}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pageData['datas'] as $d)
                                @php($rowData = toArray($d))
                                @php($rowClassId = $rowData['id'] ?? '')
                                <tr class="pid_{{$rowClassId}}">
                                    @foreach($pageData['fields'] as $f)
                                        @if($f['identification']!="rightaction")
                                            @if($f['callback'])
                                                <td class="{{is_array($f['cssClass'])?$f['cssClass'][$rowData[$f['identification']]]:$f['cssClass']}}"
                                                    @if($f['jsfunction']) onclick="{{$f['jsfunction']}}('{{$rowData[$f['identification']]}}')" @endif>
                                                    @if($f['datas'])
                                                        <label class="label {{$rowData[$f['identification'].'CssClass']?:'label-info'}}">{{$f['callback']($f['datas'][$rowData[$f['identification']]])}}</label>
                                                    @elseif(strpos($f['identification'], ',') !== false)
                                                        @foreach(explode(',',$f['identification']) as $name)
                                                            {{$rowData[$name]}}<br>
                                                        @endforeach
                                                    @else
                                                        {{$f['callback']($rowData[$f['identification']])}}
                                                    @endif
                                                </td>
                                            @elseif(in_array($f['formtype'],['upload', 'uploadAjax', 'image', 'imageAjax'], true))
                                                @php($fileValue = $rowData[$f['identification']] ?? '')
                                                <td>
                                                    @if($fileValue && in_array(strtolower(end(explode('.',$fileValue))),['jpg','jpeg','png']))
                                                        <img src="{{GetUrlByPath($fileValue)}}"
                                                             class="cursor-pointer" width="30"
                                                             onclick="clickImage('{{GetUrlByPath($fileValue)}}')">
                                                    @elseif($fileValue)
                                                        <i class="cursor-pointer icon-file-download2"
                                                           onclick="fileDownload('{{GetUrlByPath($fileValue)}}')"
                                                           style="font-size: 25px;"></i>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @else
                                                <td class="{{is_array($f['cssClass'])?$f['cssClass'][$rowData[$f['identification']]]:$f['cssClass']}}"
                                                    @if($f['jsfunction']) onclick="{{$f['jsfunction']}}('{{$rowData[$f['identification']]}}')" @endif>
                                                    @if($f['datas'])
                                                        <label class="label {{$rowData[$f['identification'].'CssClass']?:'label-info'}}">{{$f['datas'][$rowData[$f['identification']]]}}</label>
                                                    @elseif(strpos($f['identification'], ',') !== false)
                                                        @foreach(explode(',',$f['identification']) as $name)
                                                            @if($rowData[$name])
                                                                {{$rowData[$name]}}<br>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        {{$rowData[$f['identification']]}}
                                                    @endif
                                                </td>
                                            @endif
                                        @elseif($f['identification']=="rightaction")
                                            @php($rowActionId = $rowData[$f['actionby']] ?? ($rowData['id'] ?? ''))
                                            <td>
                                                @if(!in_array($rowData[$f['actionby']],$f['notIdArray']?:[]))
                                                    @foreach($f['datas'] as $act)
                                                        @if(!$act["show"] || ($act["show"] && $rowData[$act["show"]['field']] == $act["show"]['value']))
                                                            @if(!in_array($rowData[$f['actionby']],$act['notIdArray']?:[]))
                                                                @if($act['actionType']=="modal")
                                                                    <button type="button" class="btn {{$act['cssClass']}} btn-sm" data-toggle="modal" data-target="#modal_default_{{$rowActionId}}">{{$act['actionName']}}</button>
                                                                    <div id="modal_default_{{$rowActionId}}" class="modal fade">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                                    <h5 class="modal-title">{{ getTranslateByKey('ft_tmp_detail_with', 'admin', ['name' => $rowData[$act['titleField']] ?: $act['titleField']]) }}</h5>
                                                                                </div>
                                                                                <div class="modal-body" id="modal_default_content_{{$rowActionId}}" style="height: 600px;overflow: auto">
                                                                                    {!! $rowData[$act['field']] !!}
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-link" data-dismiss="modal">{{ getTranslateByKey('common_close') }}</button>
                                                                                    <button type="button" class="btn btn-info" onclick="exportImg('modal_default_content_{{$rowActionId}}')">{{ getTranslateByKey('ft_tmp_export_img') }}</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    @php($rowActionUrl = rtrim($act['actionUrl'].'?'.http_build_query(array_merge($act['param']?:[],[$f['actionby']=>$rowData[$f['actionby']]])),"?"))
                                                                    <a
                                                                        @if($act['popup'])
                                                                            onclick="popupPage('{{$rowActionUrl}}')"
                                                                        @elseif($act['confirm'])
                                                                            onclick="formtoolConfirmNavigate('{{$rowActionUrl}}')"
                                                                        @else
                                                                            href="{{$rowActionUrl}}"
                                                                        @endif
                                                                        @if(!empty($act['target'])) target="{{$act['target']}}" @endif>
                                                                        <button type="button" class="h-button-edit btn {{$act['cssClass']}} btn-xs">
                                                                            {{$act['actionName']}}
                                                                        </button>
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                                @include(moduleAdminTemplate("formtools")."formtooltemp.children",['d'=>$d,'pageData'=>$pageData,'level'=>1])
                            @empty
                                <tr>
                                    <td colspan="{{count($pageData['fields'])}}">
                                        {{ getTranslateByKey('no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @include(moduleAdminTemplate($pageData['moduleName'])."public.footer")
            </div>
        </div>
    </div>
    @include(moduleAdminTemplate($pageData['moduleName'])."public.js")
    <script>
        function formtoolConfirmNavigate(url) {
            layer.confirm('{{ getTranslateByKey('ft_tmp_confirm') }}', {
                title: '{{ getTranslateByKey('ft_operation_tip') }}',
                btn: ['{{ getTranslateByKey('common_ensure') }}', '{{ getTranslateByKey('common_cancel') }}']
            }, function () {
                window.location.href = url;
            });
        }

        function clickImage(src, w = 300) {
            if (w <= 0) w = 300;
            if (!src) return
            layer.open({
                title: "",
                type: 1,
                skin: 'layui-layer-demo',
                closeBtn: 0,
                anim: 7,
                shadeClose: true,
                content: "<img width='" + w + "' src='" + src + "'>"
            });
        }

        function fileDownload(src) {
            window.location.href = src;
        }

        function showAndhide(id) {
            if ($(".subpid_path_" + id).is(":hidden")) {
                $(".subpid_path_" + id).show();
            } else {
                $(".subpid_path_" + id).hide();
            }
        }

        function popupPage(url) {
            var popupPageOpen = layer.open({
                title: '',
                type: 2,
                content: url,
                closeBtn: 1,
                area:['50%','65%'],
            });
            $('.layui-layer-iframe').css({
                'transform': 'translateZ(10000px)',
                'scrollbar-width': 'none',
            });
        }
    </script>
@foreach(($pageData['inlineScripts'] ?? []) as $inlineScript)
    <script>
{!! $inlineScript !!}
    </script>
@endforeach
</body>
</html>
