@include("admin.public.header")
<body class="horizontal">
@include("admin.public.topbar")
@include("admin.public.nav")

<style type="text/css">
    .display-none {
        display: none;
    }

    .onlineModuleList {
        cursor: pointer;
    }

    .cursor {
        cursor: pointer;
    }

    .mokuai .row div.col-md-3 {
        height: 340px;
        margin: 2rem 0;
    }

    .box {
        box-shadow: 0 0 8px #ccc;
        border-radius: 15px;
    }

    .box img {
        width: 101%;
        height: 215px;
    }

    .clearfix:after {
        visibility: hidden;
        clear: both;
        display: block;
        content: ".";
        height: 0;
    }

    .clearfix {
        *zoom: 1;
    }

    .package-meta {
        display: inline-block;
        padding: 2px 8px;
        margin-right: 6px;
        margin-bottom: 6px;
        border-radius: 12px;
        background: #f3f5f7;
        color: #59636e;
        font-size: 12px;
    }

    .package-tip {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.6;
    }

    .action-disabled {
        color: #adb5bd;
        cursor: not-allowed;
    }

    .page-header {
        margin-bottom: 20px;
    }

    .container-fluid {
        padding-left: 28px !important;
        padding-right: 28px !important;
    }

    .col-12.bg-light {
        background: transparent !important;
    }

    .card-header.card-default {
        padding: 20px 24px;
        border: 0;
        border-bottom: 1px solid #edf2f7;
        background: #fff;
    }

    .card-body {
        padding: 24px;
        background: #fff;
    }

    .package-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 14px;
        font-size: 14px;
        line-height: 1.7;
    }

    .package-toolbar .small {
        font-size: 13px !important;
    }

    .package-toolbar .onlineModuleList,
    .package-toolbar .float-right {
        margin-left: auto;
    }

    .box {
        height: 100%;
        padding: 18px;
        border: 1px solid #e7edf5;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }

    .bottomtitle h5,
    .box h5 {
        margin: 0;
        color: #1f2937;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.5;
        word-break: break-word;
    }

    .bottomtitle p,
    .box p.text-secondary.small {
        margin: 0;
        color: #64748b !important;
        font-size: 13px !important;
        line-height: 1.6;
    }

    .package-head {
        margin: 12px 0;
    }

    .package-head-title {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .package-head-title h5 {
        flex: 1;
        min-width: 0;
    }

    .package-head-status {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        margin-top: 1px;
        font-size: 12px;
        white-space: nowrap;
    }

    .package-head-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 10px;
    }

    .package-head-meta p,
    .package-head-meta .mb-2 {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
        margin: 0 !important;
    }

    .package-tip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 4px 10px;
        margin: 8px 10px 0 0;
        border-radius: 999px;
        background: #f8fafc;
        font-size: 12px;
        color: #64748b;
        line-height: 1.6;
        vertical-align: top;
        word-break: break-word;
    }

    .package-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        margin-top: 14px;
    }

    .package-actions .d-inline-block,
    .package-actions a,
    .package-actions span,
    .package-actions div {
        font-size: 13px;
        line-height: 1.6;
    }

    .scroll-container {
        height: 88px !important;
        padding-right: 4px;
    }

    .plugin-boundary-notice {
        margin: 18px 0 4px;
        border: 1px solid #f3d19e;
        border-left: 4px solid #e6a23c;
        border-radius: 10px;
        background: #fffaf3;
    }

    .plugin-boundary-notice__head {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        color: #a16207;
        font-size: 14px;
    }

    .plugin-boundary-notice__head strong {
        flex: 1;
        font-weight: 600;
    }

    .plugin-boundary-notice__toggle {
        flex-shrink: 0;
        font-size: 13px;
        color: #b8791f;
        text-decoration: none;
    }

    .plugin-boundary-notice__toggle:hover {
        text-decoration: underline;
    }

    .plugin-boundary-notice__body {
        padding: 0 16px 14px 16px;
        font-size: 13px;
        color: #5b4a2f;
        line-height: 1.7;
    }

    .plugin-boundary-notice__body ul {
        margin: 0 0 10px;
        padding-left: 18px;
    }

    .plugin-boundary-notice__body li {
        margin-bottom: 4px;
    }

    .plugin-boundary-notice__body code {
        padding: 1px 5px;
        border-radius: 4px;
        background: #f5efe4;
        color: #8a5a12;
        font-size: 12px;
    }

    .plugin-boundary-notice__title {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 600;
    }

    .plugin-boundary-notice__tip {
        margin: 8px 0 0;
        padding: 8px 10px;
        border-radius: 6px;
        background: #fdf3e3;
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .card-header.card-default,
        .card-body {
            padding: 18px;
        }
    }

</style>
<div class="row page-header">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url("admin/index")}}">{{getTranslateByKey("common_home_page")}}</a>
            </li>
            <li class="breadcrumb-item"><a href="{{url("admin/plugin")}}">{{getTranslateByKey("plugin_management")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("plugin_list")}}</li>
        </ol>
    </div>
</div>


<section class="container-fluid pl-5 pr-5 ">

    <div class="row pb-5">
        <div class="col-12 bg-light" style="min-height: calc(100vh - 250px)">
            <div class="">
                {{-- 插件使用说明：面向站点运营者，不出现代码路径与开发术语 --}}
                <div class="plugin-boundary-notice" id="pluginBoundaryNotice">
                    <div class="plugin-boundary-notice__head">
                        <span class="fa fa-info-circle"></span>
                        <strong>{{getTranslateByKey('main_plugin_notice_title')}}</strong>
                        <a href="javascript:void(0);" class="plugin-boundary-notice__toggle" id="pluginBoundaryToggle">{{getTranslateByKey('main_collapse')}}</a>
                    </div>
                    <div class="plugin-boundary-notice__body" id="pluginBoundaryBody">
                        <p class="plugin-boundary-notice__tip" style="margin-top: 0;">
                            {!! getTranslateByKey('main_plugin_notice_intro') !!}
                        </p>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <p class="plugin-boundary-notice__title">{{getTranslateByKey('main_plugin_diff_title')}}</p>
                                <ul>
                                    <li>{!! getTranslateByKey('main_plugin_diff_plugin') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_diff_module') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_diff_conclusion') !!}</li>
                                </ul>
                                <p class="plugin-boundary-notice__title">{{getTranslateByKey('main_plugin_ops_title')}}</p>
                                <ul>
                                    <li>{{getTranslateByKey('main_plugin_ops_1')}}</li>
                                    <li>{!! getTranslateByKey('main_plugin_ops_2') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_ops_3') !!}</li>
                                </ul>
                                <p class="plugin-boundary-notice__tip">
                                    {!! getTranslateByKey('main_plugin_notice_tip') !!}
                                </p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="plugin-boundary-notice__title">{{getTranslateByKey('main_plugin_upgrade_title')}}</p>
                                <ul>
                                    <li>{!! getTranslateByKey('main_plugin_upgrade_1') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_upgrade_2') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_upgrade_3') !!}</li>
                                </ul>
                                <p class="plugin-boundary-notice__title">{{getTranslateByKey('main_plugin_settings_title')}}</p>
                                <ul>
                                    <li>{!! getTranslateByKey('main_plugin_settings_1') !!}</li>
                                    <li>{!! getTranslateByKey('main_plugin_settings_2') !!}</li>
                                    <li>{{getTranslateByKey('main_plugin_settings_3')}}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-header card-default">
                    <div class="row">
                        <div class="col-md-12 col-12 package-toolbar">
                            {{getTranslateByKey('plugin_installed')}}
                            <a href="#" onclick="clearCache();" class="small">{{getTranslateByKey('clear_cache')}}</a>
                            @if(cacheGlobalSettingsByKey('use_of_cloud')==1)
                                <a href="{{url('admin/cloud?cloud_type='.\Modules\Main\Models\Modules::Plugin)}}"
                                   class="text-right float-right onlineModuleList">
                                    {{getTranslateByKey('online_plugin')}} <i class="fa fa-arrow-circle-o-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row">
                        @if($plugin_install_datas)
                            @foreach($plugin_install_datas as $install_data)
                                <div class="col-lg-2 col-md-3 col-sm-4 mb-4" style="min-width: 260px">
                                    <div class="box">
                                        <div class="">
                                            <div class="package-head">
                                                <div class="package-head-title">
                                                    <h5>{{$install_data['name']}}</h5>
                                                    @if($install_data['status']==1)
                                                        <span class="package-head-status text-success">{{getTranslateByKey('status_enabled')}}</span>
                                                    @else
                                                        <span class="package-head-status text-danger">{{getTranslateByKey('status_disabled')}}</span>
                                                    @endif
                                                </div>
                                                <div class="package-head-meta">
                                                    <p class="text-secondary small">
                                                        <span>{{$install_data['author']}}</span>
                                                        <span>V{{$install_data['version']}}</span>
                                                    </p>
                                                    <div class="mb-2">
                                                        <span class="package-meta">{{$install_data['level_label']}}</span>
                                                        @if($install_data['compatibility_summary'])
                                                            <span class="package-meta">{{$install_data['compatibility_summary']}}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="clearfix scroll-container" style="height: 80px;overflow: auto">
                                                <p class="text-secondary">
                                                    {{$install_data['description']}}
                                                </p>
                                            </div>
                                            @if($install_data['dependency_names'])
                                                <div class="package-tip mb-2">
                                                    {{getTranslateByKey('dependency_label')}}：{{implode('、', $install_data['dependency_names'])}}
                                                </div>
                                            @endif
                                            @if($install_data['dependents'])
                                                <div class="package-tip mb-2 text-danger">
                                                    {{getTranslateByKey('dependent_label')}}：{{implode('、', $install_data['dependents'])}}
                                                </div>
                                            @endif
                                            <div class="package-actions">
                                                @if($install_data['config'])
                                                    <div class="d-inline-block">
                                                        <a href="{{url('admin/plugin/config?identification='.$install_data['identification'])}}">
                                                            <span class="fa fa-cog"></span> {{getTranslateByKey('settings')}}
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="d-inline-block text-secondary">
                                                        <span class="fa fa-cog"></span> {{getTranslateByKey('settings')}}
                                                    </div>
                                                @endif

                                                @if(intval($install_data['order'] ?? 0) >= 9999)
                                                    <div class="d-inline-block">
                                                        <div class="text-danger cursor"
                                                             onclick="changeTopQuestion(0,'{{url('admin/module/changeTop?m='.$install_data['identification'].'&top=0&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')">
                                                            <span class="fa fa-thumb-tack"></span>
                                                            {{getTranslateByKey('main_cancel_top')}}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-inline-block">
                                                        <div class="cursor text-primary"
                                                             onclick="changeTopQuestion(1,'{{url('admin/module/changeTop?m='.$install_data['identification'].'&top=1&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')">
                                                            <span class="fa fa-thumb-tack"></span>
                                                            {{getTranslateByKey('main_top')}}
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="d-inline-block module-update-{{$install_data['identification']}} text-danger"
                                                     style="cursor: pointer;visibility: hidden;"
                                                     onclick="updateVersion('{{$install_data['identification']}}','plugin','{{session()->get('versionLimit')["plugin_".$install_data["identification"]]}}')">
                                                    {{getTranslateByKey('update_version')}}
                                                </div>

                                                @if($install_data['status'] == 0)
                                                    <div class="d-inline-block cursor text-success"
                                                         onclick="changeStatusQuestion(1, '{{url('admin/module/changeStatus?m='.$install_data['identification'].'&status=1&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')">
                                                        <span class="fa fa-circle-o-notch"></span>
                                                        {{getTranslateByKey('common_enable')}}
                                                    </div>
                                                @else
                                                    @if($install_data['disable_allowed'])
                                                        <div class="d-inline-block text-danger cursor"
                                                             onclick="changeStatusQuestion(0, '{{url('admin/module/changeStatus?m='.$install_data['identification'].'&status=0&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')">
                                                            <span class="fa fa-ban"></span>
                                                            {{getTranslateByKey('common_disable')}}
                                                        </div>
                                                        
                                                    @else
                                                        <div class="d-inline-block action-disabled"
                                                             title="{{$install_data['disable_reason']}}">
                                                            <span class="fa fa-ban"></span>
                                                            {{getTranslateByKey('common_disable')}}
                                                        </div>
                                                        
                                                    @endif
                                                @endif

                                                @if($install_data['uninstall_allowed'])
                                                    <div class="d-inline-block text-secondary cursor"
                                                         onclick="uninstallQuestion('{{url('admin/module/uninstall?m='.$install_data['identification'].'&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')">
                                                        <span class=" fa fa-trash-o"></span> {{getTranslateByKey('common_uninstall')}}
                                                    </div>
                                                    
                                                @else
                                                    <div class="d-inline-block action-disabled"
                                                         title="{{$install_data['uninstall_reason']}}">
                                                        <span class=" fa fa-trash-o"></span> {{getTranslateByKey('common_uninstall')}}
                                                    </div>
                                                    
                                                @endif
                                            </div>
                                            @if($install_data['disable_reason'])
                                                <div class="package-tip mt-2 text-danger">
                                                    {{getTranslateByKey('disable_limit')}}：{{$install_data['disable_reason']}}
                                                </div>
                                            @endif
                                            @if($install_data['uninstall_reason'])
                                                <div class="package-tip mt-1 text-danger">
                                                    {{getTranslateByKey('uninstall_limit')}}：{{$install_data['uninstall_reason']}}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                                <script>
                                    $(function () {
                                        checkModuleVersion('{{$install_data['identification']}}', 'plugin', '{{$install_data['version']}}');
                                    });
                                </script>
                            @endforeach
                        @else
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-4 text-center mt-4" style="min-width: 260px">
                                <div>{{getTranslateByKey('no_installed_plugins')}}，<a href="{{url("admin/cloud?cloud_type=plugin")}}">{{getTranslateByKey('go_install')}}</a></div>
                            </div>
                        @endif


                    </div>

                </div>

                @if($plugin_not_install_datas)
                    <div class="card-header card-default">
                        <div class="row">
                            <div class="col-md-6">
                                {{getTranslateByKey('plugin_not_installed')}}
                            </div>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row">
                            @foreach($plugin_not_install_datas as $not_install_data)

                                <div class="col-md-2 mb-3 " style="min-width: 260px">
                                    <div class="box">
                                        <div class="">
                                            <div class="package-head">
                                                <div class="package-head-title">
                                                    <h5>{{$not_install_data['name']}}</h5>
                                                </div>
                                                <div class="package-head-meta">
                                                    <p class="text-secondary small">
                                                        <span>{{$not_install_data['author']}}</span>
                                                        <span>V{{$not_install_data['version']}}</span>
                                                    </p>
                                                    <div class="mb-2">
                                                        <span class="package-meta">{{$not_install_data['level_label']}}</span>
                                                        @if($not_install_data['compatibility_summary'])
                                                            <span class="package-meta">{{$not_install_data['compatibility_summary']}}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                @if($not_install_data['dependency_names'])
                                                    <div class="package-tip mt-2">
                                                        {{getTranslateByKey('dependency_label')}}：{{implode('、', $not_install_data['dependency_names'])}}
                                                    </div>
                                                @endif
                                                <div class="package-actions">
                                                        <span class="glyphicon glyphicon-cog">
                                                               @if($not_install_data['install_allowed'])
                                                                   <a href="{{url('admin/module/install?m='
                                                                        .$not_install_data['identification']
                                                                        .'&form=local'
                                                                        .'&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}"
                                                                      class="">
                                                                       {{getTranslateByKey('main_fresh_install')}}
                                                                   </a>
                                                               @else
                                                                   <span class="action-disabled"
                                                                         title="{{$not_install_data['install_reason']}}">
                                                                       {{getTranslateByKey('main_fresh_install')}}
                                                                   </span>
                                                               @endif
                                                        </span>

                                                        <span class="glyphicon glyphicon-repeat">
                                                               <a href="{{url('admin/module/recover-install?m='
                                                                    .$not_install_data['identification']
                                                                    .'&form=local-recover'
                                                                    .'&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}"
                                                                  onclick="return adminConfirmAction(this.href, {content: '{{getTranslateByKey('main_recover_install_confirm_plugin')}}', type: 'warning', confirmText: '{{getTranslateByKey('main_confirm_recover_install')}}'});">
                                                                   {{getTranslateByKey('main_recover_install')}}
                                                               </a>
                                                        </span>

                                                        <span class="glyphicon glyphicon-cog ">
                                                               @if($not_install_data['delete_allowed'])
                                                                   <span onclick="DelQuestion('{{url('admin/module/delete?m='
                                                                        .$not_install_data['identification']
                                                                        .'&form=local'
                                                                        .'&cloud_type='.\Modules\Main\Models\Modules::Plugin)}}')"
                                                                         class="text-danger" style="cursor: pointer;">
                                                                       {{getTranslateByKey('common_delete')}}
                                                                   </span>
                                                                    
                                                               @else
                                                                   <span class="action-disabled"
                                                                         title="{{$not_install_data['delete_reason']}}">
                                                                       {{getTranslateByKey('common_delete')}}
                                                                   </span>
                                                                    
                                                               @endif
                                                        </span>
                                                </div>
                                                @if($not_install_data['install_reason'])
                                                    <div class="package-tip mt-2 text-danger">
                                                        {{getTranslateByKey('install_limit')}}：{{$not_install_data['install_reason']}}
                                                    </div>
                                                @endif
                                                @if($not_install_data['delete_reason'])
                                                    <div class="package-tip mt-1 text-danger">
                                                        {{getTranslateByKey('delete_limit')}}：{{$not_install_data['delete_reason']}}
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>
                @endif


            </div>
        </div>
    </div>


    @include('admin.public.footer')


</section>

@include('admin.public.js',['load'=> ["custom"]])

<script type="text/javascript">

    function changeStatusQuestion(status, url) {
        return adminConfirmAction(url, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: status == 1 ? '{{getTranslateByKey("common_sure_to_enabling")}}' : '{{getTranslateByKey("common_sure_to_forbidden")}}'
        });
    }

    function uninstallQuestion(url) {
        return adminConfirmAction(url, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: '{{getTranslateByKey("common_sure_to_uninstall")}}',
            type: 'warning',
            confirmClass: 'btn-danger'
        });
    }

    function changeTopQuestion(status, url) {
        return adminConfirmAction(url, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: status == 1 ? '{{getTranslateByKey('main_sure_top_plugin')}}' : '{{getTranslateByKey('main_sure_cancel_top_plugin')}}'
        });
    }

    function DelQuestion(url) {
        return adminConfirmAction(url, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: '{{getTranslateByKey("common_delete_confirm")}}',
            type: 'warning',
            confirmClass: 'btn-danger'
        });
    }

    // 插件能力边界提示：可折叠，状态记在本地
    (function () {
        var $notice = $('#pluginBoundaryNotice');
        var $body = $('#pluginBoundaryBody');
        var $toggle = $('#pluginBoundaryToggle');
        if (!$notice.length) {
            return;
        }

        var storageKey = 'mxz_plugin_boundary_notice_hidden';

        var setCollapsed = function (collapsed) {
            $body.toggle(!collapsed);
            $toggle.text(collapsed ? '{{getTranslateByKey('main_expand')}}' : '{{getTranslateByKey('main_collapse')}}');
            try {
                if (collapsed) {
                    localStorage.setItem(storageKey, '1');
                } else {
                    localStorage.removeItem(storageKey);
                }
            } catch (e) {
            }
        };

        var collapsed = false;
        try {
            collapsed = localStorage.getItem(storageKey) === '1';
        } catch (e) {
        }

        setCollapsed(collapsed);

        $toggle.on('click', function () {
            setCollapsed($body.is(':visible'));
        });
    })();
</script>
<style>
    /* Scrollbar size */
    .scroll-container {
        scrollbar-width: thin; /* Scrollbar width */
        scrollbar-color: #888 #f1f1f1; /* Thumb and track colors */
    }

    /* Scrollbar size */
    .scroll-container::-webkit-scrollbar {
        width: 4px;
        height: 12px;
    }

    /* Track color */
    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    /* Thumb color */
    .scroll-container::-webkit-scrollbar-thumb {
        background: #888;
    }

    /* Thumb color on hover */
    .scroll-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Track color on hover */
    .scroll-container:hover {
        scrollbar-color: #555 #f1f1f1; /* Hover colors */
    }
</style>
</body>
</html>
