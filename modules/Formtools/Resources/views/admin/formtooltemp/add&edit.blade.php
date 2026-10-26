@include(moduleAdminTemplate($pageData['moduleName'])."public.header")
<!-- ============================================================== -->
<body>
@php($useTabbedLayout = !empty($pageData['tabbedLayout']))
@php($fieldGroups = $pageData['fieldGroups'] ?? [])
@php($tabGroups = array_values(array_filter($fieldGroups, function ($group) { return !empty($group['heading']); })))
@php($plainGroups = array_values(array_filter($fieldGroups, function ($group) { return empty($group['heading']); })))
<style>
    .formtool-workbench {
        margin-bottom: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .formtool-workbench__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .formtool-workbench__title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }
    .formtool-workbench__desc {
        margin: 6px 0 0;
        color: #64748b;
        line-height: 1.8;
    }
    .formtool-workbench__sub {
        margin-top: 8px;
        color: #94a3b8;
        line-height: 1.8;
    }
    .formtool-workbench__code {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }
    .formtool-workbench__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }
    .formtool-workbench__actions .btn {
        border-radius: 999px;
        font-weight: 600;
        min-width: 102px;
    }
    .formtool-workbench__stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        padding: 18px 22px 22px;
    }
    .formtool-workbench__stat {
        padding: 16px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
    }
    .formtool-workbench__stat-label {
        margin: 0 0 8px;
        font-size: 12px;
        color: #64748b;
        letter-spacing: .04em;
        text-transform: none;
    }
    .formtool-workbench__stat-value {
        margin: 0;
        font-size: 28px;
        line-height: 1;
        font-weight: 700;
        color: #0f172a;
    }
    .formtool-workbench__stat-desc {
        margin-top: 8px;
        font-size: 13px;
        color: #94a3b8;
    }
    .formtool-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
        vertical-align: middle;
    }
    .formtool-switch input {
        display: none;
    }
    .formtool-switch-slider {
        position: absolute;
        inset: 0;
        background: #d9d9d9;
        border-radius: 999px;
        transition: all .2s ease;
        cursor: pointer;
    }
    .formtool-switch-slider:before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: all .2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
    }
    .formtool-switch input:checked + .formtool-switch-slider {
        background: #4caf50;
    }
    .formtool-switch input:checked + .formtool-switch-slider:before {
        transform: translateX(22px);
    }
    .formtool-switch input[disabled] + .formtool-switch-slider {
        opacity: .6;
        cursor: not-allowed;
    }
    .formtool-switch-label {
        display: inline-block;
        margin-left: 8px;
        vertical-align: middle;
    }
    .formtool-group-card {
        margin: 0 0 18px;
        padding: 18px 20px 6px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }
    .formtool-group-card--legend {
        border-color: #dbeafe;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }
    .formtool-group-card .text-bold {
        margin-top: 0;
        margin-bottom: 12px;
        color: #0f172a;
    }
    .formtool-sticky-actions {
        position: fixed;
        left: 50%;
        bottom: 20px;
        transform: translateX(-50%);
        z-index: 1200;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid rgba(219, 229, 241, 0.96);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
        backdrop-filter: blur(10px);
    }
    .formtool-sticky-actions__meta {
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
        padding-right: 4px;
        white-space: nowrap;
    }
    .formtool-sticky-actions .btn {
        min-width: 110px;
        border-radius: 999px;
        font-weight: 600;
    }
    .formtool-form-shell {
        padding-bottom: 92px;
    }
    .formtool-tabs {
        margin-bottom: 18px;
        border: 1px solid #dbe5f1;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .formtool-tabs__layout {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        min-height: 520px;
    }
    .formtool-tabs__sidebar {
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e7eef6;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
    }
    .formtool-tabs__nav {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 14px;
        flex: 1 1 auto;
    }
    .formtool-tabs__item {
        min-width: 0;
    }
    .formtool-tabs__link {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        width: 100%;
        height: 100%;
        padding: 14px 16px;
        border: 1px solid transparent;
        border-radius: 16px;
        background: transparent;
        color: #475569;
        text-decoration: none;
        transition: all .22s ease;
    }
    .formtool-tabs__link:hover,
    .formtool-tabs__link:focus {
        text-decoration: none;
        border-color: #d7e3f1;
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        transform: translateY(-1px);
    }
    .formtool-tabs__item.active .formtool-tabs__link {
        border-color: #cfe0ff;
        background: linear-gradient(135deg, #ffffff 0%, #eef4ff 100%);
        color: #1d4ed8;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.12);
    }
    .formtool-tabs__index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 12px;
        background: rgba(148, 163, 184, 0.12);
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
    }
    .formtool-tabs__meta {
        min-width: 0;
        flex: 1 1 auto;
    }
    .formtool-tabs__title {
        display: block;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .formtool-tabs__desc {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.45;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .formtool-tabs__item.active .formtool-tabs__index {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: #fff;
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22);
    }
    .formtool-tabs__item.active .formtool-tabs__title {
        color: #1d4ed8;
    }
    .formtool-tabs__item.active .formtool-tabs__desc {
        color: #2563eb;
    }
    .formtool-tabs__content {
        padding: 20px;
    }
    .formtool-tabs__pane {
        display: none;
    }
    .formtool-tabs__pane.active {
        display: block;
    }
    .formtool-tabs__pane .formtool-group-card {
        margin-bottom: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
        background: transparent;
    }
    .formtool-tabs__helper {
        margin: 0 14px 14px;
        padding: 12px 14px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: rgba(239, 246, 255, 0.9);
        color: #475569;
        line-height: 1.75;
        font-size: 12px;
    }
    .formtool-tabs__main {
        min-width: 0;
        background: #fff;
    }
    @media (max-width: 991px) {
        .formtool-workbench__head {
            flex-direction: column;
        }
        .formtool-workbench__actions {
            justify-content: flex-start;
        }
        .formtool-workbench__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .formtool-workbench__stats {
            grid-template-columns: 1fr;
        }
        .formtool-tabs__layout {
            grid-template-columns: 1fr;
            min-height: auto;
        }
        .formtool-tabs__sidebar {
            border-right: 0;
            border-bottom: 1px solid #e7eef6;
        }
        .formtool-tabs__nav {
            overflow-x: auto;
            flex-direction: row;
            padding-bottom: 12px;
        }
        .formtool-tabs__item {
            flex: 0 0 auto;
            min-width: 188px;
        }
        .formtool-tabs__link {
            width: auto;
        }
        .formtool-sticky-actions {
            left: 12px;
            right: 12px;
            bottom: 12px;
            transform: none;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .formtool-sticky-actions__meta {
            width: 100%;
            white-space: normal;
        }
        .formtool-sticky-actions .btn {
            flex: 1 1 calc(50% - 6px);
            min-width: 0;
        }
        .formtool-form-shell {
            padding-bottom: 126px;
        }
    }
</style>
@if(!empty($pageData['inlineStyle']))
    <style>{!! $pageData['inlineStyle'] !!}</style>
@endif
@if(!$pageData['popup'])
    @include(moduleAdminTemplate($pageData['moduleName'])."public.nav")
@endif
<!-- Page container -->
<div class="page-container">
    <!-- Page content -->
    <div class="page-content">
    @if(!$pageData['popup'])
        @include(moduleAdminTemplate($pageData['moduleName'])."public.left")
    @endif
    <!-- Main content -->
        <div class="content-wrapper">
            <!-- Content area -->
            <div class="content">
                @if(!$pageData['popup'])
                    @include(moduleAdminTemplate($pageData['moduleName'])."public.page",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
                @endif
                @if(!empty($pageData['pageWorkbench']) || !empty($pageData['contentWorkbench']))
                    @php($workbench = $pageData['pageWorkbench'] ?? $pageData['contentWorkbench'])
                    <div class="formtool-workbench">
                        <div class="formtool-workbench__head">
                            <div>
                                @if(!empty($pageData['pageWorkbench']))
                                    <h3 class="formtool-workbench__title">{{$workbench['title'] ?? $pageData['subtitle']}}</h3>
                                    @if(!empty($workbench['desc']))
                                        <p class="formtool-workbench__desc">{{$workbench['desc']}}</p>
                                    @endif
                                    @if(!empty($workbench['sub']))
                                        <div class="formtool-workbench__sub">{{$workbench['sub']}}</div>
                                    @endif
                                @else
                                    <h3 class="formtool-workbench__title">{{$workbench['isEdit'] ? getTranslateByKey('ft_wb_edit') : getTranslateByKey('ft_wb_add')}}</h3>
                                    <p class="formtool-workbench__desc">{{ getTranslateByKey('ft_wb_desc') }}</p>
                                    <div class="formtool-workbench__sub">
                                        {{ getTranslateByKey('ft_wb_current_content') }}<span class="formtool-workbench__code">{{$workbench['title']}}</span>
                                        {{ getTranslateByKey('ft_ml_table_colon') }}<span class="formtool-workbench__code">{{$workbench['tableName']}}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="formtool-workbench__actions">
                                @foreach(($workbench['actions'] ?? []) as $action)
                                    <a href="{{$action['url'] ?? 'javascript:;'}}" class="{{$action['class'] ?? 'btn btn-default'}}" @if(!empty($action['target'])) target="{{$action['target']}}" @endif @if(!empty($action['confirm'])) onclick='return formtoolConfirmAction(this, @json($action["confirm"]))' @endif>
                                        {{$action['label'] ?? getTranslateByKey('operation')}}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div class="formtool-workbench__stats">
                            @if(!empty($pageData['pageWorkbench']))
                                @foreach(($workbench['stats'] ?? []) as $stat)
                                    <div class="formtool-workbench__stat">
                                        <p class="formtool-workbench__stat-label">{{$stat['label'] ?? ''}}</p>
                                        <p class="formtool-workbench__stat-value" @if(!empty($stat['valueStyle'])) style="{{$stat['valueStyle']}}" @endif>{{$stat['value'] ?? ''}}</p>
                                        @if(!empty($stat['desc']))
                                            <div class="formtool-workbench__stat-desc">{{$stat['desc']}}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="formtool-workbench__stat">
                                    <p class="formtool-workbench__stat-label">{{ getTranslateByKey('ft_wb_fields') }}</p>
                                    <p class="formtool-workbench__stat-value">{{$workbench['fieldCount'] ?? 0}}</p>
                                    <div class="formtool-workbench__stat-desc">{{ getTranslateByKey('ft_wb_fields_desc') }}</div>
                                </div>
                                <div class="formtool-workbench__stat">
                                    <p class="formtool-workbench__stat-label">{{ getTranslateByKey('ft_wb_required') }}</p>
                                    <p class="formtool-workbench__stat-value">{{$workbench['requiredCount'] ?? 0}}</p>
                                    <div class="formtool-workbench__stat-desc">{{ getTranslateByKey('ft_wb_required_desc') }}</div>
                                </div>
                                <div class="formtool-workbench__stat">
                                    <p class="formtool-workbench__stat-label">{{ getTranslateByKey('ft_wb_meta') }}</p>
                                    <p class="formtool-workbench__stat-value">{{$workbench['metaFieldCount'] ?? 0}}</p>
                                    <div class="formtool-workbench__stat-desc">{{ getTranslateByKey('ft_wb_meta_desc') }}</div>
                                </div>
                                <div class="formtool-workbench__stat">
                                    <p class="formtool-workbench__stat-label">{{ getTranslateByKey('ft_wb_seo') }}</p>
                                    <p class="formtool-workbench__stat-value">{{$workbench['seoFieldCount'] ?? 0}}</p>
                                    <div class="formtool-workbench__stat-desc">{{ getTranslateByKey('ft_wb_seo_desc') }}</div>
                                </div>
                                <div class="formtool-workbench__stat">
                                    <p class="formtool-workbench__stat-label">{{ getTranslateByKey('ft_wb_status') }}</p>
                                    <p class="formtool-workbench__stat-value" style="font-size: 20px; line-height: 1.3;">{{$workbench['statusLabel'] ?? getTranslateByKey('ft_status_unset')}}</p>
                                    <div class="formtool-workbench__stat-desc">{{ getTranslateByKey('ft_wb_status_desc') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="panel panel-flat">
                    <div class="panel-heading">
                        <form class="form-horizontal formtool-form-shell" action="{{$pageData['formaction']}}"
                              method="{{$pageData['method']}}" id="{{$pageData['formid']}}"
                              enctype="multipart/form-data">

                            <fieldset class="content-group">
                                <div>
                                    @foreach($pageData['listActions'] as $act)
                                        @php($formActionUrl = rtrim($act['actionUrl'].'?'.http_build_query($act['param'] ?? []), "?"))
                                        <a class="label {{$act['cssClass']}} pull-right m-t-xs"
                                           style="margin-right: 10px;padding: 3px 8px;font-size: 12px;"
                                           @if($act['popup'])
                                               onclick="popupPage('{{$formActionUrl}}')"
                                           @else
                                               href="{{$formActionUrl}}"
                                           @endif
                                           @if(!empty($act['target'])) target="{{$act['target']}}" @endif>
                                            {{$act['actionName']}}
                                        </a>
                                    @endforeach
                                    @if(!$pageData["fields"]['legend'])
                                        <legend class="text-bold">{{$pageData['subtitle']}}</legend>
                                    @endif
                                </div>
                                @if($pageData['tips'])
                                    <div class="alert alpha-orange-600 border-orange alert-styled-left">
                                        <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
                                        {{$pageData['tips']}}
                                    </div>
                                @endif
                                @if(!empty($pageData['contentTopHtml']))
                                    {!! $pageData['contentTopHtml'] !!}
                                @endif
                                @foreach($plainGroups as $group)
                                    @php($heading = $group['heading'] ?? null)
                                    @php($visibleFields = array_values(array_filter(($group['fields'] ?? []), function ($field) { return ($field['formtype'] ?? 'text') !== 'hidden'; })))
                                    @if(!$heading && !$visibleFields)
                                        @foreach(($group['fields'] ?? []) as $f)
                                            @include('formtools::admin.formtooltemp.field', compact('f'))
                                        @endforeach
                                    @else
                                        <div class="formtool-group-card @if(($heading['formtype'] ?? '') === 'legend') formtool-group-card--legend @endif">
                                            @if($heading)
                                                @include('formtools::admin.formtooltemplates.groupHeading', ['f' => $heading])
                                            @endif
                                            <div class="row">
                                                @foreach(($group['fields'] ?? []) as $f)
                                                    @include('formtools::admin.formtooltemp.field', compact('f'))
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($useTabbedLayout && $tabGroups)
                                    <div class="formtool-tabs" id="formtoolTabs">
                                        <div class="formtool-tabs__layout">
                                            <div class="formtool-tabs__sidebar">
                                                <ul class="nav formtool-tabs__nav" role="tablist">
                                                    @foreach($tabGroups as $index => $group)
                                                        @php($heading = $group['heading'] ?? [])
                                                        <li class="formtool-tabs__item @if($index === 0) active @endif" data-tab-target="#formtool-tab-{{$index}}">
                                                            <a href="javascript:;" class="formtool-tabs__link">
                                                                <span class="formtool-tabs__index">{{str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)}}</span>
                                                                <span class="formtool-tabs__meta">
                                                                    <span class="formtool-tabs__title">{{$heading['name'] ?? getTranslateByKey('ft_wb_group_with', 'admin', ['index' => $index + 1])}}</span>
                                                                    @if(!empty($heading['notes']))
                                                                        <span class="formtool-tabs__desc">{{$heading['notes']}}</span>
                                                                    @endif
                                                                </span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="formtool-tabs__helper">{{ getTranslateByKey('ft_wb_tabs_helper') }}</div>
                                            </div>
                                            <div class="formtool-tabs__main">
                                                <div class="formtool-tabs__content">
                                                    @foreach($tabGroups as $index => $group)
                                                        @php($heading = $group['heading'] ?? null)
                                                        <div class="formtool-tabs__pane @if($index === 0) active @endif" id="formtool-tab-{{$index}}">
                                                            <div class="formtool-group-card @if(($heading['formtype'] ?? '') === 'legend') formtool-group-card--legend @endif">
                                                                @if($heading)
                                                                    @include('formtools::admin.formtooltemplates.groupHeading', ['f' => $heading])
                                                                @endif
                                                                <div class="row">
                                                                    @foreach(($group['fields'] ?? []) as $f)
                                                                        @include('formtools::admin.formtooltemp.field', compact('f'))
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @foreach($tabGroups as $group)
                                        @php($heading = $group['heading'] ?? null)
                                        <div class="formtool-group-card @if(($heading['formtype'] ?? '') === 'legend') formtool-group-card--legend @endif">
                                            @if($heading)
                                                @include('formtools::admin.formtooltemplates.groupHeading', ['f' => $heading])
                                            @endif
                                            <div class="row">
                                                @foreach(($group['fields'] ?? []) as $f)
                                                    @include('formtools::admin.formtooltemp.field', compact('f'))
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="formtool-sticky-actions">
                                    <div class="formtool-sticky-actions__meta">{{ getTranslateByKey('ft_wb_sticky_meta') }}</div>
                                    @if($pageData['formaction'])
                                        @if($pageData['actionType']=='ajax')
                                            <button type="button" class="btn btn-info js-post-button">
                                                {{$pageData['actionName']}}
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-info js-post-button">
                                                {{$pageData['actionName']}}
                                            </button>
                                        @endif
                                    @endif
                                    @if(!empty($pageData['popup']))
                                    <a href="javascript:;" type="button" class="btn btn-danger" onclick="parent.layer.close(parent.layer.index);">
                                        {{$pageData['backName'] ?? getTranslateByKey('common_close')}}
                                    </a>
                                    @else
                                    <a href="javascript:history.back();" type="button" class="btn btn-danger">
                                        {{$pageData['backName']}}
                                    </a>
                                    @endif
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
                @include(moduleAdminTemplate($pageData['moduleName'])."public.footer")
            </div>
            <!-- /content area -->
        </div>
        <!-- /main content -->
    </div>
    <!-- /page content -->
</div>
<!-- /page container -->
<!-- 						Content End		 						-->
<!-- ============================================================== -->
@include(moduleAdminTemplate($pageData['moduleName'])."public.js")
<script>
    @if($pageData['actionType']=='ajax')
    $('.js-post-button').click(function () {
        layer.load(1);
        $.ajax({
            "method": "post",
            "url": "{{$pageData['formaction']}}",
            "data": new FormData($('#{{$pageData['formid']}}')[0]),
            "dataType": 'json',
            "cache": false,
            "processData": false,
            "contentType": false,
            "success": function (res) {
                layer.closeAll();
                if (res.status == 200) {
                    layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                        if ('{{request()->query('popup', '')}}') {
                            // 关闭当前的layer弹窗
                            parent.layer.close(parent.layer.index);
                            // 刷新父页面
                            parent.location.reload();
                        }else if (res.data.jumpUrl) {
                            window.location.href = res.data.jumpUrl;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    layer.msg(res.msg, {icon: 5})
                }
            },
            "error": function (res) {
                layer.closeAll();
                layer.msg('{{ getTranslateByKey('ft_tmp_contact_admin') }}', {icon: 5});
                console.log(res);
            }
        })
    });

    @endif
    function clickImage(src, w = 300) {
        if (w <= 0) w = 300;
        if (!src) return
        //自定义页
        layer.open({
            title: "",
            type: 1,
            skin: 'layui-layer-demo', //样式类名
            closeBtn: 0, //不显示关闭按钮
            anim: 7,
            shadeClose: true, //开启遮罩关闭
            content: "<img width='" + w + "' src='" + src + "'>"
        });
    }

    function popupPage(url) {
        var popupPageOpen = layer.open({
            title: '',
            type: 2,
            content: url,
            closeBtn: 1,
            area:['50%','65%'],
        });
        /*layer.full(popupPageOpen);*/
        $('.layui-layer-iframe').css({
            'transform': 'translateZ(10000px)',
            'scrollbar-width': 'none',
        });
    }

    function normalizeTagsValue(value) {
        return value
            .split(',')
            .map(function (item) { return item.trim(); })
            .filter(function (item) { return item !== ''; })
            .filter(function (item, index, arr) { return arr.indexOf(item) === index; })
            .join(', ');
    }

    $('.formtool-tags').on('blur', function () {
        $(this).val(normalizeTagsValue($(this).val()));
    });

    $('.formtool-switch-input').each(function () {
        var $input = $(this);
        var $hidden = $input.closest('div').find('input[type="hidden"][name="' + $input.data('target-name') + '"]').first();
        var $label = $input.closest('div').find('.formtool-switch-label').first();

        function syncSwitchState() {
            var checked = $input.is(':checked');
            if ($hidden.length) {
                $hidden.val(checked ? $input.data('on-value') : $input.data('off-value'));
            }
            if ($label.length) {
                $label.text(checked ? $input.data('on-label') : $input.data('off-label'));
            }
        }

        $input.on('change', syncSwitchState);
        syncSwitchState();
    });

    $('.formtool-json').each(function () {
        var $field = $(this);
        var $group = $field.closest('.form-group, .col-md-6');
        var $error = $('<span class="help-block text-danger formtool-json-error" style="display:none;"></span>');
        $field.after($error);

        var validateJson = function (formatWhenValid) {
            var raw = $.trim($field.val());
            if (!raw) {
                $group.removeClass('has-error');
                $error.hide().text('');
                return true;
            }
            try {
                var parsed = JSON.parse(raw);
                if (formatWhenValid) {
                    $field.val(JSON.stringify(parsed, null, 2));
                }
                $group.removeClass('has-error');
                $error.hide().text('');
                return true;
            } catch (e) {
                $group.addClass('has-error');
                $error.text($field.data('json-error') || '{{ getTranslateByKey('ft_json_invalid') }}').show();
                return false;
            }
        };

        $field.on('blur', function () {
            validateJson(true);
        });
        $field.on('input', function () {
            validateJson(false);
        });
    });

    function formtoolConfirmAction(anchor, message) {
        if (typeof window.adminConfirmAction === 'function' && anchor && anchor.href) {
            return window.adminConfirmAction(anchor.href, {
                content: message || '{{ getTranslateByKey('ft_tmp_confirm') }}',
                target: anchor.target || ''
            });
        }
        return window.confirm(message);
    }

    $(document).on('click', '.formtool-tabs__item', function () {
        var target = $(this).data('tab-target');
        if (!target) {
            return;
        }
        $(this).addClass('active').siblings('.formtool-tabs__item').removeClass('active');
        $(target).addClass('active').siblings('.formtool-tabs__pane').removeClass('active');
    });
</script>
@if(!empty($pageData['inlineScript']))
    <script>{!! $pageData['inlineScript'] !!}</script>
@endif
@foreach(($pageData['inlineScripts'] ?? []) as $inlineScript)
    <script>
{!! $inlineScript !!}
    </script>
@endforeach
{{--必须放在最后--}}
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/forms/selects/select2.min.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/pages/form_select2.js"></script>
</body>
</html>
