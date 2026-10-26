<!doctype html>
<html>
<head>
    @include("install.head")
    <style>
        .rewrite-correct, .rewrite-error, .rewrite-detail {
            display: none;
        }
    </style>
</head>
<body>
@include("install.header")
@php
    $summary = $installCheckSummary ?? [
        'total' => 0,
        'passed' => 0,
        'failed_required' => 0,
        'failed_optional' => 0,
        'failed_items' => [],
    ];
    $databaseDrivers = $data['database_drivers'] ?? [];
    $supportedDbLabels = implode(installTrans('install_list_separator'), array_column(array_filter($databaseDrivers, static fn($item) => !empty($item['available'])), 'label'));
@endphp
<section class="container install-shell">
    <div class="install-step-nav">
        <div class="install-step-nav__item is-active">
            <div><span class="install-step-nav__step">1</span><span class="install-step-nav__label">{{ installTrans('install_step_env_check') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_env_check_desc2') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">2</span><span class="install-step-nav__label">{{ installTrans('install_step_create_data') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_create_data_desc') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">3</span><span class="install-step-nav__label">{{ installTrans('install_step_finish') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_finish_desc') }}</div>
        </div>
    </div>

    <div class="install-panel mt-4">
        <div class="install-panel__body">
            <div class="install-kpi-grid mb-4">
                <div class="install-kpi">
                    <div class="install-kpi__label">{{ installTrans('install_kpi_total') }}</div>
                    <div class="install-kpi__value">{{$summary['total']}}</div>
                    <div class="install-kpi__hint">{{ installTrans('install_kpi_total_hint') }}</div>
                </div>
                <div class="install-kpi">
                    <div class="install-kpi__label">{{ installTrans('install_kpi_passed') }}</div>
                    <div class="install-kpi__value">{{$summary['passed']}}</div>
                    <div class="install-kpi__hint">{{ installTrans('install_kpi_passed_hint') }}</div>
                </div>
                <div class="install-kpi">
                    <div class="install-kpi__label">{{ installTrans('install_kpi_blocked') }}</div>
                    <div class="install-kpi__value">{{$summary['failed_required']}}</div>
                    <div class="install-kpi__hint">{{ installTrans('install_kpi_blocked_hint') }}</div>
                </div>
                <div class="install-kpi">
                    <div class="install-kpi__label">{{ installTrans('install_kpi_warn') }}</div>
                    <div class="install-kpi__value">{{$summary['failed_optional']}}</div>
                    <div class="install-kpi__hint">{{ installTrans('install_kpi_warn_hint') }}</div>
                </div>
            </div>

            <div class="alert install-alert {{$summary['failed_required'] ? 'alert-danger' : 'alert-success'}}">
                <div class="font-weight-bold">
                    {{$summary['failed_required'] ? installTrans('install_check_failed') : installTrans('install_check_passed')}}
                </div>
                <div class="small mt-2">
                    {{ installTrans('install_current_php_label') }}<strong>{{$data['phpversion'] ?? phpversion()}}</strong>{{ installTrans('install_comma_label') }}{{ installTrans('install_requirement_label') }}<strong>{{$data['php_requirement'] ?? '^8.0.2'}}</strong>{{ installTrans('install_period_label') }}
                    {{ installTrans('install_supported_db_prefix') }}<strong>{{$supportedDbLabels !== '' ? $supportedDbLabels : installTrans('install_no_db_types')}}</strong>{{ installTrans('install_supported_db_suffix') }}
                    {{ installTrans('install_fix_env_first') }}
                </div>
            </div>

            @if(!empty($summary['failed_items']))
                <div class="install-info-card mt-4">
                    <h6>{{ installTrans('install_failed_items_title') }}</h6>
                    <p class="install-section-desc">{{ installTrans('install_failed_items_desc') }}</p>
                    <div class="row">
                        @foreach($summary['failed_items'] as $item)
                            <div class="col-md-6 mb-2">
                                <div class="border rounded p-3 h-100">
                                    <div class="font-weight-bold text-danger">
                                        <i class="fa fa-remove"></i> {{$item['title']}}
                                    </div>
                                    <div class="small mt-2 install-muted">
                                        {{ installTrans('install_item_type') }}{{$item['required'] ? installTrans('install_type_required') : installTrans('install_type_optional')}}<br>
                                        {{ installTrans('install_item_message_label') }}{{$item['message']}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="install-summary-grid mt-4">
                <div class="install-summary-card">
                    <h6>{{ installTrans('install_runtime_env') }}</h6>
                    <ul class="install-meta-list">
                        <li>
                            <span class="install-meta-list__label">{{ installTrans('install_os') }}</span>
                            <span class="install-meta-list__value">{{$data['os']}}</span>
                        </li>
                        <li>
                            <span class="install-meta-list__label">{{ installTrans('install_php_requirement') }}</span>
                            <span class="install-meta-list__value">{{$data['php_requirement'] ?? '^8.0.2'}}</span>
                        </li>
                        <li>
                            <span class="install-meta-list__label">{{ installTrans('install_php_status') }}</span>
                            <span class="install-meta-list__value">{!! $data['phpversion_msg'] !!}</span>
                        </li>
                        <li>
                            <span class="install-meta-list__label">{{ installTrans('install_upload_ability') }}</span>
                            <span class="install-meta-list__value">{!! $data['upload_size'] !!}</span>
                        </li>
                    </ul>
                </div>
                <div class="install-summary-card">
                    <h6>{{ installTrans('install_core_extensions') }}</h6>
                    <ul class="install-meta-list">
                        <li><span class="install-meta-list__label">Session</span><span class="install-meta-list__value">{!! $data['session'] !!}</span></li>
                        <li><span class="install-meta-list__label">PDO</span><span class="install-meta-list__value">{!! $data['pdo'] !!}</span></li>
                        <li><span class="install-meta-list__label">CURL</span><span class="install-meta-list__value">{!! $data['curl'] !!}</span></li>
                        <li><span class="install-meta-list__label">GD</span><span class="install-meta-list__value">{!! $data['gd'] !!}</span></li>
                        <li><span class="install-meta-list__label">MBstring</span><span class="install-meta-list__value">{!! $data['mbstring'] !!}</span></li>
                        <li><span class="install-meta-list__label">fileinfo</span><span class="install-meta-list__value">{!! $data['fileinfo'] !!}</span></li>
                    </ul>
                </div>
                <div class="install-summary-card">
                    <h6>{{ installTrans('install_supported_db_card') }}</h6>
                    <ul class="install-meta-list">
                        @foreach($databaseDrivers as $dbDriverItem)
                            <li>
                                <span class="install-meta-list__label">{{$dbDriverItem['label']}}</span>
                                <span class="install-meta-list__value">
                                    @if($dbDriverItem['available'])
                                        <i class="fa fa-check correct"></i> {{ installTrans('install_status_supported') }}
                                    @else
                                        <i class="fa fa-remove error"></i> {{ installTrans('install_status_unsupported') }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="small text-muted mt-2">
                        {{ installTrans('install_db_card_hint') }}
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="install-section-title">{{ installTrans('install_detail_check_list') }}</div>
                <p class="install-section-desc">{{ installTrans('install_detail_check_desc') }}</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm install-table">
                        <thead>
                        <tr>
                            <th>{{ installTrans('install_th_check_item') }}</th>
                            <th>{{ installTrans('install_th_recommended') }}</th>
                            <th>{{ installTrans('install_th_current_status') }}</th>
                            <th>{{ installTrans('install_th_minimum') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ installTrans('install_os') }}</td>
                            <td>Linux / Windows Server</td>
                            <td><i class="fa fa-check correct"></i> {{$data['os']}}</td>
                            <td>{{ installTrans('install_no_limit') }}</td>
                        </tr>
                        <tr>
                            <td>{{ installTrans('install_php_version') }}</td>
                            <td>{{$data['php_requirement'] ?? '^8.0.2'}}</td>
                            <td>{!! $data['phpversion_msg'] !!}</td>
                            <td>{{$data['php_requirement'] ?? '^8.0.2'}}</td>
                        </tr>
                        <tr class="section-row">
                            <td colspan="4">{{ installTrans('install_section_extensions') }}</td>
                        </tr>
                        <tr>
                            <td>Session</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['session'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>PDO</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['pdo'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>CURL</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['curl'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>GD</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['gd'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>MBstring</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['mbstring'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>fileinfo</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>{!! $data['fileinfo'] !!}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr class="section-row">
                            <td colspan="4">{{ installTrans('install_section_db_support') }}</td>
                        </tr>
                        @foreach($databaseDrivers as $dbDriverItem)
                            <tr>
                                <td>{{$dbDriverItem['label']}}</td>
                                <td>{{ installTrans('install_enable_ext', ['ext' => $dbDriverItem['required_extensions']]) }}</td>
                                <td>
                                    @if($dbDriverItem['available'])
                                        <i class="fa fa-check correct"></i> {{ installTrans('install_status_supported') }}
                                    @else
                                        <i class="fa fa-remove error"></i> {{ installTrans('install_status_unsupported') }}
                                    @endif
                                </td>
                                <td>{{ installTrans('install_at_least_one') }}</td>
                            </tr>
                        @endforeach
                        <tr class="section-row">
                            <td colspan="4">{{ installTrans('install_section_capability') }}</td>
                        </tr>
                        <tr>
                            <td>{{ installTrans('install_server_rewrite') }}</td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                            <td>
                                <span class="rewrite-checking">{{ installTrans('install_detecting') }}</span>
                                <span class="rewrite-correct"><i class="fa fa-check correct"></i> {{ installTrans('install_status_supported') }}</span>
                                <span class="rewrite-error"><i class="fa fa-remove error"></i> {{ installTrans('install_status_unsupported') }}</span>
                                <div class="rewrite-detail small text-muted mt-2"></div>
                            </td>
                            <td>{{ installTrans('install_status_enabled') }}</td>
                        </tr>
                        <tr>
                            <td>{{ installTrans('install_attachment_upload') }}</td>
                            <td>>= 2M</td>
                            <td>{!! $data['upload_size'] !!}</td>
                            <td>{{ installTrans('install_upload_allowed') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <div class="install-section-title">{{ installTrans('install_dir_permission_check') }}</div>
                <p class="install-section-desc">{{ installTrans('install_dir_permission_desc') }}</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm install-table">
                        <thead>
                        <tr>
                            <th>{{ installTrans('install_th_dir_file') }}</th>
                            <th>{{ installTrans('install_th_write') }}</th>
                            <th>{{ installTrans('install_th_read') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($data['folders'])
                            @foreach($data['folders'] as $dir => $vo)
                                <tr>
                                    <td>{{$dir}}</td>
                                    <td>
                                        @if($vo['w'])
                                            <i class="fa fa-check correct"></i> {{ installTrans('install_writable') }}
                                        @else
                                            <i class="fa fa-remove error"></i> {{ installTrans('install_not_writable') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($vo['r'])
                                            <i class="fa fa-check correct"></i> {{ installTrans('install_readable') }}
                                        @else
                                            <i class="fa fa-remove error"></i> {{ installTrans('install_not_readable') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="install-bottom-actions">
                <a href="{{url('/install?install=2')}}" class="btn btn-outline-primary">{{ installTrans('install_recheck_btn') }}</a>
                @if($summary['failed_required'] > 0)
                    <button type="button" class="btn btn-secondary" disabled>{{ installTrans('install_fix_env_first_btn') }}</button>
                @else
                    <a href="{{url('/install?install=3')}}" class="btn btn-primary">{{ installTrans('install_goto_create_data') }}</a>
                @endif
            </div>
        </div>
    </div>
</section>

@include("install.footer")
<script>
    function buildRewriteHelpHtml(serverType, serverSoftware) {
        var serverLine = '';
        if (serverSoftware) {
            serverLine = '<div class="mt-2"><span class="text-muted">' + '{{ installTrans('install_detected_web_server') }}' + '</span><code>' + serverSoftware + '</code></div>';
        } else if (serverType && serverType !== 'unknown') {
            serverLine = '<div class="mt-2"><span class="text-muted">' + '{{ installTrans('install_detected_web_server') }}' + '</span><code>' + serverType + '</code></div>';
        }

        var apache = '<div class="mt-3 font-weight-bold">' + '{{ installTrans('install_rewrite_help_apache') }}' + '</div>'
            + '<pre class="mt-2 mb-2"><code>&lt;IfModule mod_rewrite.c&gt;\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^ index.php [L]\n&lt;/IfModule&gt;</code></pre>';
        var nginx = '<div class="mt-3 font-weight-bold">' + '{{ installTrans('install_rewrite_help_nginx') }}' + '</div>'
            + '<pre class="mt-2 mb-0"><code>location / {\n    try_files $uri $uri/ /index.php?$query_string;\n}\n\nlocation ~ \\.php$ {\n    include fastcgi_params;\n    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n    fastcgi_pass 127.0.0.1:9000;\n}</code></pre>';
        if (serverType === 'nginx') {
            return serverLine + nginx + apache;
        }
        if (serverType === 'apache') {
            return serverLine + apache + nginx;
        }
        return serverLine + apache + nginx;
    }

    function updateRewriteView(result) {
        var supported = !!(result && result.data && result.data.supported);
        $('.rewrite-checking').hide();
        $('.rewrite-correct').toggle(supported);
        $('.rewrite-error').toggle(!supported);
        var detail = result && result.msg ? result.msg : '{{ installTrans('install_rewrite_check_failed') }}';
        if (!supported) {
            var serverType = result && result.data ? result.data.server_type : '';
            var serverSoftware = result && result.data ? result.data.server_software : '';
            detail += buildRewriteHelpHtml(serverType, serverSoftware);
        }
        $('.rewrite-detail').html(detail).show();
    }

    function detectRewriteSupport() {
        $.ajax({
            url: "{{url('install/rewrite-check')}}?t=" + Date.now(),
            type: 'GET',
            dataType: 'json',
            success: function (result) {
                updateRewriteView(result);
            },
            error: function () {
                updateRewriteView({
                    status: 40000,
                    msg: '{{ installTrans('install_rewrite_not_detected') }}',
                    data: {
                        supported: false
                    }
                });
            }
        });
    }

    $(function () {
        detectRewriteSupport();
    });
</script>
</body>
</html>
