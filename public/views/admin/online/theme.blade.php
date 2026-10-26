@include("admin.public.header")
@php($queryParams = request()->query())
@php($queryCloudType = $currentFilters['cloud_type'] ?? ($queryParams['cloud_type'] ?? 'theme'))
@php($queryIndex = intval($currentFilters['index'] ?? ($queryParams['index'] ?? 0)))
@php($queryCatePid = intval($currentFilters['cate_pid'] ?? ($queryParams['cate_pid'] ?? 0)))
@php($queryCateId = intval($currentFilters['cate_id'] ?? ($queryParams['cate_id'] ?? 0)))
@php($queryPlatform = $currentFilters['platform'] ?? ($queryParams['platform'] ?? ''))
@php($queryIsfree = $currentFilters['isfree'] ?? ($queryParams['isfree'] ?? ''))
@php($querySort = $currentFilters['sort'] ?? ($queryParams['sort'] ?? ''))
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
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 4px rgba(17, 24, 39, 0.06);
    }

    .box img {
        width: 100%;
        height: 160px;
        object-fit: contain;
        background: #f8f9fa;
        display: block;
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

    .cloud-filter-line {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        line-height: 1.8;
    }

    .cloud-filter-line__label {
        color: #495057;
        font-size: 13px;
        font-weight: 600;
        margin-right: 2px;
        white-space: nowrap;
    }

    .cloud-filter-item {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 12px;
        color: #6c757d;
        background: #f8f9fa;
        text-decoration: none;
    }

    .cloud-filter-item:hover {
        color: #495057;
        border-color: #dee2e6;
        text-decoration: none;
    }

    .cloud-filter-item.is-active {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.08);
        border-color: rgba(13, 110, 253, 0.18);
    }

    .cloud-action {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        line-height: 1.6;
        background: #f8f9fa;
        margin-left: 8px;
    }

    .cloud-action--muted {
        color: #6c757d;
    }

    .cloud-action--install {
        color: #111827;
    }

    .cloud-action--update {
        color: #198754;
    }

    .cloud-price {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        color: #dc3545;
    }

</style>
<div class="row page-header">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url("admin/index")}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("online_theme_list")}}</li>
        </ol>
    </div>
</div>

<section class="container-fluid pl-5 pr-5 ">

    <div class="row pb-5">
        <div class="col-12 bg-light" style="min-height: calc(100vh - 250px)">
            <div class="">
                <div class="card-body">
                    <div class="row mb-3 pb-3">
                        <div class="col-md-12 mb-2">
                            <a href="{{url("admin/cloud?cloud_type=module")}}"
                               class="btn btn-sm btn-light mr-3 btn-sm @if($queryCloudType=="module") btn-primary active @endif ">{{getTranslateByKey('online_module')}}</a>
                            <a href="{{url("admin/cloud?cloud_type=plugin")}}"
                               class="btn btn-sm btn-light mr-3 btn-sm @if($queryCloudType=='plugin') btn-primary active @endif  ">{{getTranslateByKey('online_plugin')}}</a>
                            <a href="{{url("admin/cloud?cloud_type=theme")}}"
                               class="btn btn-sm btn-light mr-3 btn-sm @if($queryCloudType=="theme") btn-primary active @endif  ">{{getTranslateByKey('online_theme')}}</a>
                        </div>
                        <div class="col-md-12">
                            <div class="cloud-filter-line">
                                <span class="cloud-filter-line__label">{{getTranslateByKey('primary_category')}}：</span>
                                <a class="cloud-filter-item @if(!$queryCatePid) is-active @endif" href="{{url("admin/cloud?cloud_type=".$queryCloudType)}}" >{{getTranslateByKey('common_all')}}</a>
                                @foreach($listDatas['cates'] as $index=>$cate_data)
                                    <a class="cloud-filter-item @if($cate_data['id'] == $queryCatePid) is-active @endif"
                                       href="{{url("admin/cloud?index=".$index."&cloud_type=".$queryCloudType."&cate_pid=".$cate_data['id'])}}">{{$cate_data['cate_name']}}</a>
                                @endforeach
                            </div>
                        </div>

                        @if(!empty($listDatas['cates'][$queryIndex]['children']))
                            <div class="col-md-12">
                                <div class="cloud-filter-line">
                                    <span class="cloud-filter-line__label">{{getTranslateByKey('secondary_category')}}：</span>
                                    <a class="cloud-filter-item @if(!$queryCateId) is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['cate_id' => ''])))}}" >{{getTranslateByKey('common_all')}}</a>
                                    @foreach($listDatas['cates'][$queryIndex]['children'] as $index=>$cate_data)
                                        <a class="cloud-filter-item @if($cate_data['id'] == $queryCateId) is-active @endif"
                                           href="{{url("admin/cloud?index=".$queryIndex."&cloud_type=".$queryCloudType."&cate_pid=".$queryCatePid."&cate_id=".$cate_data['id'])}}">{{$cate_data['cate_name']}}</a>
                                    @endforeach
                                </div>
                            </div>

                        @endif

                        <div class="col-md-12">
                            <div class="cloud-filter-line">
                                <span class="cloud-filter-line__label">{{getTranslateByKey('supported_platform')}}：</span>
                                <a class="cloud-filter-item @if(!$queryPlatform) is-active @endif" href="{{url("admin/cloud?".http_build_query($queryParams))}}">{{getTranslateByKey('common_all')}}</a>
                                <a class="cloud-filter-item @if($queryPlatform=="pc") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['platform' => 'pc'])))}}">PC</a>
                                <a class="cloud-filter-item @if($queryPlatform=="moblic") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['platform' => 'moblic'])))}}">{{getTranslateByKey('mobile')}}</a>
                                <a class="cloud-filter-item @if($queryPlatform=="H5") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['platform' => 'H5'])))}}">H5</a>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="cloud-filter-line">
                                <span class="cloud-filter-line__label">{{getTranslateByKey('is_paid')}}：</span>
                                <a class="cloud-filter-item @if(!$queryIsfree) is-active @endif" href="{{url("admin/cloud?".http_build_query($queryParams))}}">{{getTranslateByKey('common_all')}}</a>
                                <a class="cloud-filter-item @if($queryIsfree=="n") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['isfree' => 'n'])))}}">{{getTranslateByKey('charged')}}</a>
                                <a class="cloud-filter-item @if($queryIsfree=="y") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['isfree' => 'y'])))}}">{{getTranslateByKey('free')}}</a>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="cloud-filter-line">
                                <span class="cloud-filter-line__label">{{getTranslateByKey('sort_attribute')}}：</span>
                                <a class="cloud-filter-item @if(!$querySort) is-active @endif" href="{{url("admin/cloud?".http_build_query($queryParams))}}">{{getTranslateByKey('default_sort')}}</a>
                                <a class="cloud-filter-item @if($querySort=="download") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['sort' => 'download'])))}}">{{getTranslateByKey('download_count')}}</a>
                                <a class="cloud-filter-item @if($querySort=="commont") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['sort' => 'commont'])))}}">{{getTranslateByKey('score')}}</a>
                                <a class="cloud-filter-item @if($querySort=="time") is-active @endif" href="{{url("admin/cloud?".http_build_query(array_merge($queryParams, ['sort' => 'time'])))}}">{{getTranslateByKey('publish_time')}}</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if($listDatas['list']['data'])
                            @foreach($listDatas['list']['data'] as $modules_install_data)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="box">
                                        <div class="">
                                            <div class="thumbnail">
                                                <img
                                                    class="img-fluid"
                                                    src="{{$modules_install_data['cover']}}" alt="">
                                            </div>
                                            <div class="bottomtitle clearfix mt-3">
                                                <h5>
                                                    {{$modules_install_data['name']}}
                                                    <span class="float-right small text-info"
                                                          style="font-size: 12px">
                                                            {{$modules_install_data['username']}}
                                                    </span>
                                                </h5>
                                                <p class="text-secondary small">
                                                    V{{$modules_install_data['version']}}

                                                    <span class="float-right">
                                                        <span class="text-primary">
                                                            {{$modules_install_data['cate_name']}}
                                                        </span>
                                                    </span>
                                                </p>
                                                <div class="mb-2">
                                                    <span class="package-meta">{{$modules_install_data['level_label']}}</span>
                                                    @if($modules_install_data['compatibility_summary'])
                                                        <span class="package-meta">{{$modules_install_data['compatibility_summary']}}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="clearfix scroll-container" style="height: 80px;overflow: auto">
                                                <p class="text-secondary">
                                                    {{$modules_install_data['description']}}
                                                </p>
                                            </div>
                                            @if($modules_install_data['dependency_names'])
                                                <div class="package-tip mb-2">
                                                    {{getTranslateByKey('dependency_label')}}：{{implode('、', $modules_install_data['dependency_names'])}}
                                                </div>
                                            @endif

                                            <div class="cloud-price">
                                                {{$modules_install_data['price']>0?"￥".$modules_install_data['price']:getTranslateByKey('free')}}
                                            </div>

                                            @if(!empty($local_data[$modules_install_data['identification']]))
                                                @php($lv = $local_data[$modules_install_data['identification']]['version'] ?? '')
                                                @php($mv = $modules_install_data['version'])
                                                @if($lv == $mv)
                                                    <div class="cloud-action cloud-action--muted">
                                                        {{getTranslateByKey('latest_version_installed')}}
                                                    </div>
                                                @elseif(version_compare($mv, $lv, '>'))
                                                    <div class="cloud-action cloud-action--update cursor"
                                                         onclick="updateVersion('{{$modules_install_data['identification']}}','theme','{{session()->get('versionLimit')["theme_".$modules_install_data['identification']]}}')">
                                                        <span class="fa fa-refresh"></span>
                                                        {{getTranslateByKey('common_update')}}
                                                    </div>
                                                    <div class="package-tip mt-2">
                                                        {{getTranslateByKey('current_version')}}：V{{$lv}}
                                                        ，{{getTranslateByKey('next_version')}}：V{{$mv}}
                                                    </div>
                                                    @if(session()->get('versionLimit')["theme_".$modules_install_data['identification']])
                                                        <div class="package-tip mt-2 text-danger">
                                                            {{getTranslateByKey('update_limit')}}：{{session()->get('versionLimit')["theme_".$modules_install_data['identification']]}}
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="cloud-action cloud-action--muted">
                                                        {{getTranslateByKey('main_local_version_newer')}}
                                                    </div>
                                                @endif
                                            @else
                                                @if($modules_install_data['install_allowed'])
                                                    <div class="cloud-action cloud-action--install cursor"
                                                         onclick="@if(!empty($modules_install_data['should_prompt_before_install'])) privatization('{{$modules_install_data['identification']}}','theme') @else update('{{$modules_install_data['identification']}}','theme') @endif">
                                                        <span class="icon-cloud-download"></span>
                                                        {{getTranslateByKey('install')}}
                                                    </div>
                                                @else
                                                    <div class="cloud-action action-disabled"
                                                         title="{{$modules_install_data['install_reason']}}">
                                                        <span class="icon-cloud-download"></span>
                                                        {{getTranslateByKey('install')}}
                                                    </div>
                                                @endif
                                                @if(!empty($modules_install_data['should_prompt_before_install']))
                                                    <div style="width: 600px;min-height:200px;max-height:350px;padding:10px 20px;scrollbar-width: thin;scrollbar-color: #888 #f1f1f1;display:none;"
                                                         class="h-privatization-top privatization_theme_{{$modules_install_data['identification']}}">
                                                        <div class="package-prompt-title">{{$modules_install_data['download_prompt_title']}}</div>
                                                        {!! $modules_install_data['download_prompt_html'] !!}
                                                        @if(!empty($modules_install_data['prompt_allows_continue_download']) || !empty($modules_install_data['prompt_requires_license_check']))
                                                            <div class="package-prompt-actions">
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="layer.closeAll(); update('{{$modules_install_data['identification']}}','theme')">{{$modules_install_data['prompt_continue_button_text'] ?? getTranslateByKey('main_continue_download')}}</button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                            @if($modules_install_data['install_reason'])
                                                <div class="package-tip mt-2 text-danger">
                                                    {{getTranslateByKey('install_limit')}}：{{$modules_install_data['install_reason']}}
                                                </div>
                                            @endif

                                        </div>
                                    </div>

                                </div>

                            @endforeach
                        @else
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                                <p>
                                    {{getTranslateByKey('no_data')}}
                                </p>
                            </div>
                        @endif


                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            @include('admin.online.pagination',['links'=>$listDatas['list']])
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>


    @include('admin.public.footer')


</section>

@include('admin.public.js',['load'=> ["custom"]])

<script type="text/javascript">



</script>
<style>
    .h-privatization-top > p {
        margin-bottom: 0;
    }
    .package-prompt-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 14px;
        color: #111827;
    }
    .package-prompt-actions {
        margin-top: 16px;
        text-align: right;
    }
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
