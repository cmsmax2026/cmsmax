@include("admin.public.header")

<body class="horizontal">
@include("admin.public.topbar")
@include("admin.public.nav")

<style>
    .admin-dashboard {
        padding: 4px 28px 24px;
    }

    .admin-dashboard .card {
        border: 1px solid #e8edf5;
        border-radius: 18px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .05);
    }

    .admin-dashboard .card-body {
        padding: 24px;
    }

    .admin-dashboard__section {
        margin-bottom: 24px;
    }

    @media (min-width: 992px) {
        .admin-dashboard__split {
            margin-left: -18px;
            margin-right: -18px;
        }

        .admin-dashboard__split > [class*="col-"] {
            padding-left: 18px;
            padding-right: 18px;
        }
    }

    .admin-dashboard__section-title {
        margin: 0 0 6px;
        color: #111827;
        font-size: 18px;
        font-weight: 700;
    }

    .admin-dashboard__section-desc {
        margin: 0 0 16px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.7;
    }

    .admin-dashboard__stat {
        height: 100%;
        padding: 22px;
        border: 1px solid #e8edf5;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .admin-dashboard__stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .admin-dashboard__stat-label {
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-dashboard__stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #eef4ff;
        color: #2563eb;
        font-size: 18px;
    }

    .admin-dashboard__stat-icon > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1em;
        height: 1em;
        font-size: inherit;
        line-height: 1;
        color: inherit;
    }

    .admin-dashboard__stat-icon > i::before {
        display: block;
        font-size: inherit;
        line-height: 1;
        color: inherit;
    }

    .admin-dashboard__stat-icon > .icon-users {
        font-family: "simple-line-icons";
        font-style: normal;
        font-weight: normal;
    }

    .admin-dashboard__stat-icon > .icon-users::before {
        content: "\e001";
    }

    .admin-dashboard__stat-value {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
    }

    .admin-dashboard__stat-desc {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.7;
    }

    .admin-dashboard__stat-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 16px;
        color: #2563eb;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-dashboard__action-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .admin-dashboard__action-item {
        display: block;
        height: 100%;
        padding: 20px;
        border: 1px solid #e8edf5;
        border-radius: 18px;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .admin-dashboard__action-item:hover {
        border-color: #bfd7ff;
        box-shadow: 0 14px 32px rgba(37, 99, 235, .08);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .admin-dashboard__action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 14px;
        border-radius: 14px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 18px;
    }

    .admin-dashboard__action-title {
        margin: 0 0 8px;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
    }

    .admin-dashboard__action-desc {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.7;
    }

    .admin-dashboard__history-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 12px;
    }

    .admin-dashboard__history-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-dashboard__history-item:hover {
        color: #2563eb;
        text-decoration: none;
        background: #eff6ff;
    }

    .admin-dashboard__info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .admin-dashboard__info-card {
        padding: 18px;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        background: #fff;
    }

    .admin-dashboard__info-label {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .admin-dashboard__info-value {
        margin: 0 0 6px;
        color: #111827;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        word-break: break-word;
    }

    .admin-dashboard__info-desc {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
        word-break: break-word;
    }

    .admin-dashboard__health-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .admin-dashboard__health-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        background: #fff;
    }

    .admin-dashboard__health-label {
        margin: 0 0 6px;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
    }

    .admin-dashboard__health-value {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
        word-break: break-word;
    }

    .admin-dashboard__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .admin-dashboard__badge--ok {
        background: #ecfdf3;
        color: #15803d;
    }

    .admin-dashboard__badge--warn {
        background: #fff7ed;
        color: #c2410c;
    }

    .admin-dashboard__reference {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .admin-dashboard__reference-card {
        padding: 18px;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        background: #fff;
    }

    .admin-dashboard__reference-card h5 {
        margin: 0 0 12px;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
    }

    .admin-dashboard__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .admin-dashboard__chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        line-height: 1.5;
        word-break: break-word;
    }

    .admin-dashboard__sdk {
        min-height: 64px;
        padding: 16px 18px;
        border: 1px dashed #dbe4f0;
        border-radius: 16px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
        word-break: break-word;
    }

    @media (max-width: 1199px) {
        .admin-dashboard__action-grid,
        .admin-dashboard__info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .admin-dashboard__health-list,
        .admin-dashboard__reference {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .admin-dashboard {
            padding-left: 16px;
            padding-right: 16px;
        }

        .admin-dashboard__action-grid,
        .admin-dashboard__info-grid {
            grid-template-columns: 1fr;
        }

        .admin-dashboard .card-body {
            padding: 18px;
        }

    }
</style>

<div class="row page-header">
    <div class="col-lg-12 align-self-center"></div>
</div>

<section class="main-content admin-dashboard">
    <div class="row admin-dashboard__section">
        @foreach($dashboardStats as $stat)
            <div class="col-xl-3 col-md-6 margin-b-20">
                <div class="admin-dashboard__stat">
                    <div class="admin-dashboard__stat-top">
                        <span class="admin-dashboard__stat-label">{{$stat['label']}}</span>
                        <span class="admin-dashboard__stat-icon"><i class="{{$stat['icon']}}"></i></span>
                    </div>
                    <p class="admin-dashboard__stat-value">{{$stat['value']}}</p>
                    <p class="admin-dashboard__stat-desc">{{$stat['desc']}}</p>
                    @if(!empty($stat['url']))
                        <a href="{{$stat['url']}}" class="admin-dashboard__stat-link">
                            <span>{{getTranslateByKey('enter_management')}}</span>
                            <i class="fa fa-angle-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($historicalEntryList)
        <div class="row admin-dashboard__section">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="admin-dashboard__section-title">{{getTranslateByKey("history_entry")}}</h4>
                        <p class="admin-dashboard__section-desc">{{getTranslateByKey('main_history_entry_desc')}}</p>
                        <div class="admin-dashboard__history-list">
                            @foreach($historicalEntryList as $item)
                                <a target="_blank" href="{{$item['url']}}" class="admin-dashboard__history-item">
                                    <i class="fa fa-history"></i>
                                    <span>{{$item['name']}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row admin-dashboard__section admin-dashboard__split">
        <div class="col-lg-7 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <h4 class="admin-dashboard__section-title">{{getTranslateByKey('main_runtime_environment')}}</h4>
                    <p class="admin-dashboard__section-desc">{{getTranslateByKey('main_runtime_environment_desc')}}</p>
                    <div class="admin-dashboard__info-grid">
                        @foreach($environmentCards as $item)
                            <div class="admin-dashboard__info-card">
                                <p class="admin-dashboard__info-label">{{$item['label']}}</p>
                                <p class="admin-dashboard__info-value">{{$item['value']}}</p>
                                <p class="admin-dashboard__info-desc">{{$item['desc']}}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="admin-dashboard__reference" style="margin-top: 18px;">
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey('main_system_info')}}</h5>
                            <div class="admin-dashboard__chips">
                                <span class="admin-dashboard__chip">{{getTranslateByKey('main_label_system_name')}}{{getenv("APP_NAME")}}</span>
                                <span class="admin-dashboard__chip">{{getTranslateByKey('main_label_system_version')}}{{config("app.app_version")}}</span>
                                <span class="admin-dashboard__chip">{{getTranslateByKey('main_label_current_time')}}{{date("Y-m-d H:i:s")}}</span>
                                <span class="admin-dashboard__chip">{{getTranslateByKey('main_label_server')}}{{PHP_OS}}</span>
                                <span class="admin-dashboard__chip">{{getTranslateByKey('main_label_database')}}{{$version}}</span>
                                <span class="admin-dashboard__chip">Laravel：{{app()::VERSION}}</span>
                            </div>
                            <div style="margin-top: 12px;">
                                {!! hook("CmsUpdateVersion",['version'=>env("APP_VERSION"),"moduleName"=>"System"])[0] !!}
                            </div>
                        </div>
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey('main_capability_check')}}</h5>
                            <div class="admin-dashboard__chips">
                                <span class="admin-dashboard__chip">GD：{{$gdinfo}}</span>
                                <span class="admin-dashboard__chip">FreeType：{{$freetype}}</span>
                                <span class="admin-dashboard__chip">allow_url_fopen：{{$allowurl}}</span>
                                <span class="admin-dashboard__chip">ZIP：{{$zip}}</span>
                                <span class="admin-dashboard__chip">Composer：{{$composer}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <h4 class="admin-dashboard__section-title">{{getTranslateByKey('main_health_check')}}</h4>
                    <p class="admin-dashboard__section-desc">{{getTranslateByKey('main_health_check_desc')}}</p>
                    <div class="admin-dashboard__health-list">
                        @foreach($healthChecks as $check)
                            <div class="admin-dashboard__health-item">
                                <div>
                                    <p class="admin-dashboard__health-label">{{$check['label']}}</p>
                                    <p class="admin-dashboard__health-value">{{$check['value']}}</p>
                                </div>
                                <span class="admin-dashboard__badge admin-dashboard__badge--{{$check['status']}}">
                                    {{$check['status'] === 'ok' ? getTranslateByKey('main_status_ok') : getTranslateByKey('main_status_warn')}}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row admin-dashboard__section">
        <div class="col-lg-7 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <h4 class="admin-dashboard__section-title">{{getTranslateByKey("other_reference")}}</h4>
                    <p class="admin-dashboard__section-desc">{{getTranslateByKey('main_install_reference_desc')}}</p>
                    <div class="admin-dashboard__reference">
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey("required_extensions")}}</h5>
                            <div class="admin-dashboard__chips">
                                @foreach($requiredExtensions as $extension)
                                    <span class="admin-dashboard__chip">{{$extension}}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey("required_functions")}}</h5>
                            <div class="admin-dashboard__chips">
                                @foreach($requiredFunctions as $function)
                                    <span class="admin-dashboard__chip">{{$function}}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey("installed_extensions")}}</h5>
                            <div class="admin-dashboard__chips">
                                @foreach(array_filter(array_map('trim', explode(',', $loadedExtensions))) as $extension)
                                    <span class="admin-dashboard__chip">{{$extension}}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="admin-dashboard__reference-card">
                            <h5>{{getTranslateByKey("disabled_functions")}}</h5>
                            <div class="admin-dashboard__chips">
                                @php($disabledFunctionsList = array_values(array_filter(array_map('trim', explode(',', (string) $disableFunctions)))))
                                @if($disabledFunctionsList)
                                    @foreach($disabledFunctionsList as $function)
                                        <span class="admin-dashboard__chip">{{$function}}</span>
                                    @endforeach
                                @else
                                    <span class="admin-dashboard__chip">{{getTranslateByKey('main_no_disabled_functions')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <h4 class="admin-dashboard__section-title">{{getTranslateByKey("third_party_sdk_list")}}</h4>
                    <p class="admin-dashboard__section-desc">{{getTranslateByKey('main_sdk_list_desc')}}</p>
                    <div class="admin-dashboard__sdk" id="sdks"></div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.public.footer')
</section>

@include('admin.public.js',['load'=> ["custom"]])
<script>
    getsdks();
</script>
</body>
</html>
