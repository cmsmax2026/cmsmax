<!doctype html>
<html>
<head>
    @include("install.head")
</head>
<body>

@include("install.header")
@php
    $defaults = $installDefaults ?? [];
    $dbDefaults = $installDbDefaults ?? [];
    $installDbDrivers = $installDbDrivers ?? [];
    $availableDbDrivers = array_values(array_filter($installDbDrivers, static fn($item) => !empty($item['available'])));
    $hasAvailableDbDriver = count($availableDbDrivers) > 0;
    $defaultManagerPassword = old('manager_pwd', $defaults['manager_pwd'] ?? '');
    $adminLoginEntrance = $defaults['admin_login_entrance'] ?? '';
    $adminLoginUrl = $adminLoginEntrance ? url('/admin/login/' . $adminLoginEntrance) : url('/admin/login');
    $dbDriver = old('dbdriver', $dbDefaults['dbdriver'] ?? 'mysql');
    //当前默认类型不被环境支持时，自动回退到第一个可用类型
    if (!$hasAvailableDbDriver || !in_array($dbDriver, array_column($availableDbDrivers, 'driver'), true)) {
        $dbDriver = $hasAvailableDbDriver ? $availableDbDrivers[0]['driver'] : 'mysql';
    }
    $dbHost = old('dbhost', $dbDefaults['dbhost'] ?? env('DB_HOST', '127.0.0.1'));
    $dbUser = old('dbuser', $dbDefaults['dbuser'] ?? env('DB_USERNAME', ''));
    $dbPassword = old('dbpw', $dbDefaults['dbpw'] ?? env('DB_PASSWORD', ''));
    $dbPort = old('dbport', $dbDefaults['dbport'] ?? env('DB_PORT', 3306));
    $dbName = old('dbname', $dbDefaults['dbname'] ?? env('DB_DATABASE', ''));
    $dbPrefix = old('dbprefix', $dbDefaults['dbprefix'] ?? env('DB_PREFIX', 'mxz_'));
    $dbCharset = old('dbcharset', $dbDefaults['dbcharset'] ?? env('DB_CHARSET', 'utf8mb4'));
    $dbSqlite = old('dbsqlite', $dbDefaults['dbsqlite'] ?? env('DB_SQLITE_DATABASE', database_path('database.sqlite')));
    //供前端动态展示每种数据库类型的选择建议
    $dbDriverJsInfo = [];
    foreach ($installDbDrivers as $dbDriverItem) {
        $dbDriverJsInfo[$dbDriverItem['driver']] = [
            'label' => $dbDriverItem['label'],
            'pros' => $dbDriverItem['pros'],
            'reason' => $dbDriverItem['reason'],
            'when' => $dbDriverItem['when'],
        ];
    }
@endphp

<section class="container install-shell">
    <div class="install-step-nav">
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">1</span><span class="install-step-nav__label">{{ installTrans('install_step_env_check') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_env_check_desc3') }}</div>
        </div>
        <div class="install-step-nav__item is-active">
            <div><span class="install-step-nav__step">2</span><span class="install-step-nav__label">{{ installTrans('install_step_create_data') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_create_data_desc') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">3</span><span class="install-step-nav__label">{{ installTrans('install_step_finish') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_finish_desc2') }}</div>
        </div>
    </div>

    <div class="install-panel mt-4">
        <div class="install-panel__body">
            <div class="install-form-grid">
                <div>
                    <form id="js-install-form" action="{{url('/install?install=4')}}" method="post">
                        {{csrf_field()}}
                        <input type="hidden" name="force" value="0"/>
                        <div class="alert alert-info install-alert">
                            <div class="font-weight-bold">{{ installTrans('install_prefill_ready') }}</div>
                            <div class="small mt-2">{{ installTrans('install_prefill_ready_desc') }}</div>
                        </div>
                        @if(!$hasAvailableDbDriver)
                            <div class="alert alert-danger install-alert">
                                <div class="font-weight-bold">{{ installTrans('install_no_db_driver_title') }}</div>
                                <div class="small mt-2">
                                    {{ installTrans('install_no_db_driver_desc_prefix') }}<a href="{{url('/install?install=2')}}">{{ installTrans('install_return_env_check') }}</a>{{ installTrans('install_no_db_driver_desc_suffix') }}
                                </div>
                            </div>
                        @endif
                        <div class="server">
                            <table class="table small install-table">
                        <tr>
                            <td class="td1" colspan="3" >{{ installTrans('install_db_info_section') }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_db_type') }}</td>
                            <td colspan="2">
                                <select name="dbdriver" id="dbdriver" class="form-control col-md-6 col-sm-9">
                                    @foreach($installDbDrivers as $dbDriverItem)
                                        @php
                                            $optionLabel = $dbDriverItem['available']
                                                ? $dbDriverItem['label']
                                                : $dbDriverItem['label'] . installTrans('install_db_driver_unsupported', ['ext' => $dbDriverItem['required_extensions']]);
                                        @endphp
                                        <option value="{{$dbDriverItem['driver']}}"
                                                @if(!$dbDriverItem['available']) disabled @endif
                                                @if($dbDriver === $dbDriverItem['driver']) selected @endif>{{$optionLabel}}</option>
                                    @endforeach
                                </select>
                                <div id="js-install-tip-dbdriver" class="small text-muted mt-1"></div>
                            </td>
                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--sqlite" style="display:none;">
                            <td class="text-left">{{ installTrans('install_sqlite_file') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbsqlite" id="dbsqlite" value="{{ $dbSqlite }}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-dbsqlite">
                                    <span class="text-muted">{{ installTrans('install_sqlite_path_hint') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--remote">
                            <td class="text-left">{{ installTrans('install_db_host') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbhost" id="dbhost" value="{{ $dbHost }}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-dbhost">
                                    <span class="text-muted">{{ installTrans('install_db_host_hint') }}</span>
                                </div>
                            </td>

                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--remote">
                            <td class="text-left">{{ installTrans('install_db_user') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbuser" id="dbuser" value="{{ $dbUser }}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-dbuser" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--remote">
                            <td class="text-left">{{ installTrans('install_db_password') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbpw" id="dbpw" value="{{ $dbPassword }}" class="form-control col-md-6 col-sm-9" autoComplete="off"
                                       onblur="checkDbPwd()">
                                <div id="js-install-tip-dbpw" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--remote">
                            <td class="text-left">{{ installTrans('install_db_port') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbport" id="dbport" value="{{$dbPort}}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-dbport">
                                    <span class="text-muted">{{ installTrans('install_db_port_hint') }}</span>
                                </div>
                            </td>

                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--remote">
                            <td class="text-left">{{ installTrans('install_db_name') }}</td>
                            <td colspan="2">
                                <input type="text" name="dbname" id="dbname" value="{{ $dbName }}" class="form-control col-md-6 col-sm-9"
                                       onblur="checkDbPwd()">
                                <div id="js-install-tip-dbname">
                                    <span class="text-muted">{{ installTrans('install_db_name_hint') }}</span>
                                </div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_db_prefix') }}</td>
                            <td colspan="2"><input type="text" name="dbprefix" id="dbprefix" placeholder="{{ env('DB_PREFIX') }}" value="{{ $dbPrefix }}"
                                       class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-dbprefix">
                                    <span class="text-muted">{{ installTrans('install_db_prefix_hint', ['name' => $cms_name]) }}</span>
                                </div>
                            </td>

                        </tr>
                        <tr class="js-db-driver-row js-db-driver-row--mysql">
                            <td class="text-left">{{ installTrans('install_db_charset') }}</td>
                            <td colspan="2">
                                <select type="text" name="dbcharset" id="dbcharset" value="" class="form-control col-md-6 col-sm-9">
                                    <option value="utf8mb4" @if($dbCharset === 'utf8mb4') selected @endif>utf8mb4</option>
                                    <option value="utf8" @if($dbCharset === 'utf8') selected @endif>utf8</option>
                                </select>
                                <div id="js-install-tip-dbcharset">
                                    <span class="text-muted">{{ installTrans('install_db_charset_hint') }}</span>
                                </div>
                            </td>

                        </tr>

                        <tr>
                            <td class="td1" colspan="3">{{ installTrans('install_site_config_section') }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_site_name') }}</td>
                            <td colspan="2">
                                <input type="text" name="website_name" value="{{old('website_name', $cms_name)}}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-website_name" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_site_keywords') }}</td>
                            <td colspan="2">
                                <input type="text" name="website_keys" value="{{ installTrans('install_default_website_keys', ['name' => $cms_name]) }}"
                                       class="form-control col-md-6 col-sm-9" autoComplete="off">
                                <div id="js-install-tip-website_keys" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_site_desc') }}</td>
                            <td colspan="2">
                                <textarea class="form-control col-md-6 col-sm-9 "  rows="10" name="website_desc">{{ installTrans('install_default_website_desc', ['name' => $cms_name]) }}</textarea>
                                <div id="js-install-tip-website_desc" class="text-muted"></div>
                            </td>
                        </tr>

                        <tr>
                            <td class="td1" colspan="3">{{ installTrans('install_founder_section') }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_admin_account') }}</td>
                            <td colspan="2">
                                <input type="text" name="manager" value="{{old('manager', $defaults['manager'] ?? 'admin')}}" class="form-control col-md-6 col-sm-9">
                                <div id="js-install-tip-manager" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_login_password') }}</td>
                            <td colspan="2">
                                <div class="input-group col-md-6 col-sm-9 p-0">
                                    <input type="password" name="manager_pwd" id="js-manager-pwd" class="form-control"
                                           value="{{$defaultManagerPassword}}" autoComplete="off">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="generateManagerPassword()">{{ installTrans('install_regenerate_btn') }}</button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleManagerPassword()">{{ installTrans('install_show_btn') }}</button>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2">
                                    {{ installTrans('install_current_random_password') }}<code class="js-generated-password">{{$defaultManagerPassword}}</code>
                                </div>
                                <div id="js-install-tip-manager_pwd" class="text-muted">
                                 <span class="text-muted">
                                    {{ installTrans('install_password_length_hint') }}
                                 </span>
                                </div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_confirm_password') }}</td>
                            <td colspan="2">
                                <input type="password" name="manager_ckpwd" id="js-manager-ckpwd" value="{{old('manager_ckpwd', $defaultManagerPassword)}}" class="form-control col-md-6 col-sm-9" autoComplete="off">
                                <div id="js-install-tip-manager_ckpwd" class="text-muted"></div>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-left">{{ installTrans('install_email') }}</td>
                            <td colspan="2">
                                <input type="text" name="manager_email" class="form-control col-md-6 col-sm-9" value="{{old('manager_email', $defaults['manager_email'] ?? '')}}">
                                <div id="js-install-tip-manager_email" class="text-muted"></div>
                            </td>
                        </tr>
                            </table>
                            <div id="js-response-tips" style="display: none;"></div>
                        </div>
                        <div class="install-bottom-actions justify-content-start">
                            <a href="{{url('/install?install=2')}}" class="btn btn-outline-primary">{{ installTrans('install_return_env_check') }}</a>
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDBInfo()">{{ installTrans('install_save_db_info_btn') }}</button>
                            <button type="submit" class="btn btn-primary">{{ installTrans('install_start_create_btn') }}</button>
                        </div>
                    </form>
                </div>

                <div>
                    <div class="install-side-card">
                        <h6>{{ installTrans('install_pre_install_confirm') }}</h6>
                        <ul class="install-meta-list">
                            <li>
                                <span class="install-meta-list__label">{{ installTrans('install_hidden_admin_entrance') }}</span>
                                <span class="install-meta-list__value"><code id="js-hidden-admin-url">{{$adminLoginUrl}}</code></span>
                            </li>
                            <li>
                                <span class="install-meta-list__label">{{ installTrans('install_founder_account') }}</span>
                                <span class="install-meta-list__value">{{old('manager', $defaults['manager'] ?? 'admin')}}</span>
                            </li>
                            <li>
                                <span class="install-meta-list__label">{{ installTrans('install_initial_password') }}</span>
                                <span class="install-meta-list__value"><code class="js-generated-password">{{$defaultManagerPassword}}</code></span>
                            </li>
                            <li>
                                <span class="install-meta-list__label">{{ installTrans('install_db_type_plain') }}</span>
                                <span class="install-meta-list__value"><span id="js-current-driver">{{ strtoupper($dbDriver) }}</span></span>
                            </li>
                            <li>
                                <span class="install-meta-list__label">{{ installTrans('install_db_charset_plain') }}</span>
                                <span class="install-meta-list__value"><span id="js-current-charset">utf8mb4</span></span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyTextById('js-hidden-admin-url', '{{ installTrans('install_copy_admin_entrance_done') }}')">{{ installTrans('install_copy_admin_entrance_btn') }}</button>
                        </div>
                    </div>

                    @if(count($availableDbDrivers) > 0)
                        <div class="install-side-card mt-3">
                            <h6>{{ installTrans('install_db_choose_title') }}</h6>
                            <ul class="small pl-3 mb-0 install-muted">
                                @foreach($availableDbDrivers as $dbDriverItem)
                                    <li class="mb-2">
                                        <span class="font-weight-bold">{{$dbDriverItem['label']}}{{ installTrans('install_label_colon') }}</span>{{$dbDriverItem['when']}}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="install-side-card mt-3">
                        <h6>{{ installTrans('install_tips_title') }}</h6>
                        <ul class="small pl-3 mb-0 install-muted">
                            <li class="mb-2">{{ installTrans('install_tip_1') }}</li>
                            <li class="mb-2">{{ installTrans('install_tip_2') }}</li>
                            <li class="mb-2">{{ installTrans('install_tip_3') }}</li>
                            <li>{{ installTrans('install_tip_4') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="position-fixed bottom-0 right-0 p-3" style="z-index: 1111; right: 0; bottom: 0;">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
        <div class="toast-header">
            <strong class="mr-auto">{{ installTrans('install_toast_title') }}</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@include("install.footer")

<script src="{{INSTALL_ASSET}}/assets/js/validate.js"></script>
<script src="{{INSTALL_ASSET}}/assets/js/ajaxForm.js"></script>
<script>
    var DB_DRIVER_INFO = @json($dbDriverJsInfo);

    function updateDriverTip() {
        var driver = $('#dbdriver').val();
        var info = DB_DRIVER_INFO[driver];
        if (!info) {
            return;
        }
        var html = ''
            + '<div class="mb-1"><span class="font-weight-bold">' + '{{ installTrans('install_db_pros_label') }}' + '</span>' + info.pros + '</div>'
            + '<div class="mb-1"><span class="font-weight-bold">' + '{{ installTrans('install_db_reason_label') }}' + '</span>' + info.reason + '</div>'
            + '<div><span class="font-weight-bold">' + '{{ installTrans('install_db_when_label') }}' + '</span>' + info.when + '</div>';
        $('#js-install-tip-dbdriver').html(html);
    }

    function randomString(length) {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        var result = '';
        for (var i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    function generateManagerPassword() {
        var password = randomString(12);
        $('#js-manager-pwd').val(password);
        $('#js-manager-ckpwd').val(password);
        $('.js-generated-password').text(password);
        $('#js-install-tip-manager_pwd').html('<span class="text-success">' + '{{ installTrans('install_pwd_regenerated') }}' + '</span>');
        $('#js-install-tip-manager_ckpwd').html('<span class="text-success">' + '{{ installTrans('install_pwd_synced') }}' + '</span>');
    }

    function toggleManagerPassword() {
        var currentType = $('#js-manager-pwd').attr('type');
        var nextType = currentType === 'password' ? 'text' : 'password';
        $('#js-manager-pwd').attr('type', nextType);
        $('#js-manager-ckpwd').attr('type', nextType);
    }

    function copyTextById(elementId, successMessage) {
        var text = $('#' + elementId).text().trim();
        if (!text) {
            $('.toast-body').html('{{ installTrans('install_copy_empty') }}');
            $('.toast').toast('show');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                $('.toast-body').html(successMessage);
                $('.toast').toast('show');
            }).catch(function () {
                fallbackCopyText(text, successMessage);
            });
            return;
        }

        fallbackCopyText(text, successMessage);
    }

    function fallbackCopyText(text, successMessage) {
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(text).trigger('select');
        document.execCommand('copy');
        tempInput.remove();
        $('.toast-body').html(successMessage);
        $('.toast').toast('show');
    }

    function checkDbPwd() {
        var dbDriver = $('#dbdriver').val();
        var dbHost = $('#dbhost').val();
        var dbUser = $('#dbuser').val();
        var dbPwd = $('#dbpw').val();
        var dbName = $('#dbname').val();
        var dbPort = $('#dbport').val();
        var dbSqlite = $('#dbsqlite').val();
        data = {
            'dbdriver': dbDriver,
            'hostname': dbHost,
            'username': dbUser,
            'password': dbPwd,
            'hostport': dbPort,
            'database': dbName,
            'dbsqlite': dbSqlite,
        };
        var url = "{{url('install/checkDbPwd')}}";
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            type: "POST",
            url: url,
            dataType: 'JSON',
            data: data,
            beforeSend: function () {
            },
            success: function (data) {
                var tig = data.status == 200 ? 'tips-success' : 'tips-error';
                if (data.code) {

                } else {
                    //数据库链接配置失败
                    $('#js-install-tip-dbpw').html('<span for="dbname" generated="true" class="' + tig + '" style="">' + data.msg + '</span>');
                    $('.toast-body').html(data.msg);
                    $('.toast').toast('show')
                }
            },
            complete: function () {
            },
            error: function () {
                $('#js-install-tip-dbpw').html('<span for="dbname" generated="true" class="tips-error" style="">' + '{{ installTrans('install_db_connect_failed') }}' + '</span>');
                $('#dbpw').val("");
                $('.toast-body').html('{{ installTrans('install_db_connect_failed') }}');
                $('.toast').toast('show')
            }
        });
    }

    function saveDBInfo() {
        var dbDriver = $('#dbdriver').val();
        var dbHost = $('#dbhost').val();
        var dbUser = $('#dbuser').val();
        var dbPwd = $('#dbpw').val();
        var dbName = $('#dbname').val();
        var dbPort = $('#dbport').val();
        var dbprefix = $('#dbprefix').val();
        var dbcharset = $('#dbcharset').val();
        var dbsqlite = $('#dbsqlite').val();
        data = {
            'dbdriver': dbDriver,
            'dbhost': dbHost,
            'dbuser': dbUser,
            'dbpw': dbPwd,
            'dbport': dbPort,
            'dbname': dbName,
            'dbprefix': dbprefix,
            'dbcharset': dbcharset,
            'dbsqlite': dbsqlite,
        };
        var url = "{{url('install/saveDBInfo')}}";
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            type: "POST",
            url: url,
            dataType: 'JSON',
            data: data,
            beforeSend: function () {
            },
            success: function (data) {
                var tig = data.status == 200 ? 'tips-success' : 'tips-error';
                $('#js-install-tip-dbpw').html('<span for="dbname" generated="true" class="' + tig + '" style="">' + data.msg + '</span>');
                $('.toast-body').html(data.msg);
                $('.toast').toast('show')
            },
            complete: function () {
            },
            error: function () {
                $('#js-install-tip-dbpw').html('<span for="dbname" generated="true" class="tips-error" style="">' + '{{ installTrans('install_db_save_failed') }}' + '</span>');
                $('.toast-body').html('{{ installTrans('install_db_save_failed') }}');
                $('.toast').toast('show')
            }
        });
    }

    function toggleInstallDriverFields() {
        var driver = $('#dbdriver').val();
        $('#js-current-driver').text(driver.toUpperCase());
        updateDriverTip();

        if (driver === 'sqlite') {
            $('.js-db-driver-row--remote').hide();
            $('.js-db-driver-row--mysql').hide();
            $('.js-db-driver-row--sqlite').show();
            $('#js-current-charset').text('N/A');
        } else {
            $('.js-db-driver-row--remote').show();
            $('.js-db-driver-row--sqlite').hide();
            if (driver === 'mysql') {
                $('.js-db-driver-row--mysql').show();
                $('#js-current-charset').text($('#dbcharset').val());
            } else {
                $('.js-db-driver-row--mysql').hide();
                $('#js-current-charset').text('N/A');
            }
        }

        var defaultPorts = {mysql: '3306', pgsql: '5432', sqlsrv: '1433'};
        var $port = $('#dbport');
        if (driver !== 'sqlite' && ($.trim($port.val()) === '' || $port.val() === '3306')) {
            $port.val(defaultPorts[driver] || '3306');
        }
    }

    $(function () {
        toggleInstallDriverFields();
        $('#dbcharset').on('change', function () {
            if ($('#dbdriver').val() === 'mysql') {
                $('#js-current-charset').text($(this).val());
            }
        });
        $('#dbdriver').on('change', function () {
            toggleInstallDriverFields();
        });

        //聚焦时默认提示
        var focus_tips = {
            dbdriver: '{{ installTrans('install_tip_dbdriver') }}',
            dbsqlite: '{{ installTrans('install_tip_dbsqlite') }}',
            dbhost: '{{ installTrans('install_db_host_hint') }}',
            dbport: '{{ installTrans('install_tip_dbport') }}',
            dbuser: '',
            dbpw: '',
            dbname: '',
            dbprefix: '{{ installTrans('install_db_prefix_hint', ['name' => $cms_name]) }}',
            dbcharset: '{{ installTrans('install_db_charset_hint') }}',
            manager: '{{ installTrans('install_tip_manager') }}',
            manager_pwd: '{{ installTrans('install_password_length_hint') }}',
            manager_ckpwd: '',
            sitename: '',
            siteurl: '{{ installTrans('install_tip_siteurl') }}',
            sitekeywords: '',
            siteinfo: '',
            manager_email: ''
        };

        var install_form = $("#js-install-form");

        //validate插件修改了remote ajax验证返回的response处理方式；增加密码强度提示 passwordRank
        install_form.validate({
            //debug : true,
            //onsubmit : false,
            errorPlacement: function (error, element) {
                //错误提示容器
                $('#js-install-tip-' + element[0].name).html(error);
                $('.toast-body').html(error.text());
                $('.toast').toast('show')
            },
            errorElement: 'span',
            //invalidHandler : , 未验证通过 回调
            //ignore : '.ignore' 忽略验证
            //onkeyup : true,
            errorClass: 'tips-error',
            validClass: 'tips-error',
            onkeyup: false,
            focusInvalid: false,
            rules: {
                dbdriver: {required: true},
                dbhost: {required: function () { return $('#dbdriver').val() !== 'sqlite'; }},
                dbport: {required: function () { return $('#dbdriver').val() !== 'sqlite'; }},
                dbuser: {required: function () { return $('#dbdriver').val() !== 'sqlite'; }},
                /* dbpw: {required  : true}, */
                dbname: {required: function () { return $('#dbdriver').val() !== 'sqlite'; }},
                dbcharset: {required: function () { return $('#dbdriver').val() === 'mysql'; }},
                dbsqlite: {required: function () { return $('#dbdriver').val() === 'sqlite'; }},
                dbprefix: {required: true},
                manager: {required: true},
                manager_pwd: {required: true, minlength: 6, maxlength: 32},
                manager_ckpwd: {required: true, equalTo: '#js-manager-pwd'},
                manager_email: {required: true, email: true}
            },
            highlight: false,
            unhighlight: function (element, errorClass, validClass) {
                var tip_elem = $('#js-install-tip-' + element.name);
                tip_elem.html('<span class="' + validClass + '" data-text="text"><span>');
            },
            onfocusin: function (element) {
                // var name = element.name;
                // $('#js-install-tip-' + name).html('<span data-text="text">' + focus_tips[name] + '</span>');
                // $(element).parents('tr').addClass('current');
            },
            onfocusout: function (element) {
                // var _this = this;
                // $(element).parents('tr').removeClass('current');
                //
                // if (element.name === 'email') {
                //     //邮箱匹配点击后，延时处理
                //     setTimeout(function () {
                //         _this.element(element);
                //     }, 150);
                // } else {
                //     _this.element(element);
                // }

            },
            messages: {
                dbdriver: {required: '{{ installTrans('install_validation_dbdriver') }}'},
                dbhost: {required: '{{ installTrans('install_validation_dbhost') }}'},
                dbport: {required: '{{ installTrans('install_validation_dbport') }}'},
                dbuser: {required: '{{ installTrans('install_validation_dbuser') }}'},
                dbpw: {required: '{{ installTrans('install_validation_dbpw') }}'},
                dbname: {required: '{{ installTrans('install_validation_dbname') }}'},
                dbsqlite: {required: '{{ installTrans('install_validation_dbsqlite') }}'},
                dbprefix: {required: '{{ installTrans('install_validation_dbprefix') }}'},
                manager: {required: '{{ installTrans('install_validation_manager') }}'},
                manager_pwd: {required: '{{ installTrans('install_validation_password_required') }}', minlength: '{{ installTrans('install_validation_password_min') }}', maxlength: '{{ installTrans('install_validation_password_max') }}'},
                manager_ckpwd: {required: '{{ installTrans('install_validation_ckpwd_required') }}', equalTo: '{{ installTrans('install_validation_ckpwd_equal') }}'},
                manager_email: {required: '{{ installTrans('install_validation_email_required') }}', email: '{{ installTrans('install_validation_email_format') }}'}
            },
            submitHandler: function (form) {
                form.submit();
                return true;
            }
        });
    });
</script>
</body>
</html>
