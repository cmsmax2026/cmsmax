<!doctype html>
<html>
<head>
    @include("install.head")
    <script type="text/javascript">
        //全局变量
        var GV = {
            ROOT: "/",
            WEB_ROOT: "/",
            JS_ROOT: "/"
        };
    </script>
    <style type="text/css">
        .display-none {
            display: none;
        }
    </style>
</head>
<body>

@include("install.header")
<section class="container">
    @php
        $installAdmin = session('install.admin') ?: [];
        $installEnv = session('install.env') ?: [];
        $installSettings = session('install.settings') ?: [];
        $installDefaults = session('install_form_defaults') ?: [];
        $adminLoginEntrance = $installDefaults['admin_login_entrance'] ?? '';
        $adminLoginUrl = $adminLoginEntrance ? url('/admin/login/' . $adminLoginEntrance) : url('/admin/login');
        $websiteName = '';
        foreach ($installSettings as $setting) {
            if (($setting['key'] ?? '') === 'website_name') {
                $websiteName = (string) ($setting['value'] ?? '');
                break;
            }
        }
    @endphp
    <div class="install-step-nav mt-3">
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">1</span><span class="install-step-nav__label">{{ installTrans('install_step_env_check') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_env_check_desc4') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">2</span><span class="install-step-nav__label">{{ installTrans('install_step_create_data') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_create_data_desc2') }}</div>
        </div>
        <div class="install-step-nav__item is-active">
            <div><span class="install-step-nav__step">3</span><span class="install-step-nav__label">{{ installTrans('install_step_finish') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_finish_desc3') }}</div>
        </div>
    </div>
    <div class="install-panel mt-4">
        <div class="install-panel__body">
            <div class="alert alert-info install-alert">
                <div class="font-weight-bold">{{ installTrans('install_executing_title') }}</div>
                <div class="small mt-2">{{ installTrans('install_executing_desc') }}</div>
            </div>
            <div id="log" class="install-log">
                <ul id="install-msg-panel" class="pl-0 mb-0 small list-unstyled text-muted"></ul>
            </div>

            <div class="text-center mt-4">
                <a class="install-load btn btn-light border" href="javascript:;"><i class="fa fa-refresh fa-spin"></i>&nbsp;{{ installTrans('install_installing') }}</a>
            </div>

            <div class="install-info-card display-none install-ok mt-4">
                <h6>{{ installTrans('install_summary_title') }}</h6>
                <p class="install-section-desc">{{ installTrans('install_summary_desc') }}</p>
                <div class="install-summary-grid">
                    <div class="install-summary-card">
                        <h6>{{ installTrans('install_site_info') }}</h6>
                        <ul class="install-meta-list">
                            <li><span class="install-meta-list__label">{{ installTrans('install_site_name_label') }}</span><span class="install-meta-list__value">{{$websiteName ?: $cms_name}}</span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_site_home') }}</span><span class="install-meta-list__value"><code id="js-site-home-url">{{url('/')}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_hidden_admin_entrance') }}</span><span class="install-meta-list__value"><code id="js-hidden-admin-url">{{$adminLoginUrl}}</code></span></li>
                        </ul>
                    </div>
                    <div class="install-summary-card">
                        <h6>{{ installTrans('install_admin_info') }}</h6>
                        <ul class="install-meta-list">
                            <li><span class="install-meta-list__label">{{ installTrans('install_admin_account_label') }}</span><span class="install-meta-list__value"><code>{{$installAdmin['username'] ?? 'admin'}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_initial_password') }}</span><span class="install-meta-list__value"><code id="js-admin-password">{{$installAdmin['password'] ?? ''}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_hint') }}</span><span class="install-meta-list__value">{{ installTrans('install_change_pwd_after_login') }}</span></li>
                        </ul>
                    </div>
                    <div class="install-summary-card">
                        <h6>{{ installTrans('install_db_info_section') }}</h6>
                        <ul class="install-meta-list">
                            <li><span class="install-meta-list__label">{{ installTrans('install_db_host_label') }}</span><span class="install-meta-list__value">{{$installEnv['DB_HOST'] ?? env('DB_HOST')}}</span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_db_port_label') }}</span><span class="install-meta-list__value">{{$installEnv['DB_PORT'] ?? env('DB_PORT', 3306)}}</span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_db_name_label') }}</span><span class="install-meta-list__value">{{$installEnv['DB_DATABASE'] ?? env('DB_DATABASE')}}</span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_db_prefix_label') }}</span><span class="install-meta-list__value">{{$installEnv['DB_PREFIX'] ?? env('DB_PREFIX')}}</span></li>
                        </ul>
                    </div>
                </div>
                <div class="text-danger mt-3">{{ installTrans('install_keep_lock_notice') }}</div>
                <div class="install-result-actions mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-hidden-admin-url', '{{ installTrans('install_copy_admin_entrance_done') }}')">{{ installTrans('install_copy_admin_entrance_btn') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-admin-password', '{{ installTrans('install_copy_admin_password_done') }}')">{{ installTrans('install_copy_admin_password_btn') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-site-home-url', '{{ installTrans('install_copy_site_home_done') }}')">{{ installTrans('install_copy_home_btn') }}</button>
                </div>
                <div class="install-result-actions mt-3">
                    <a class="btn btn-success install-ok display-none" href="{{url('/')}}">{{ installTrans('install_goto_frontend') }}</a>
                    <a class="btn btn-primary install-ok display-none" href="{{$adminLoginUrl}}">{{ installTrans('install_goto_admin') }}</a>
                </div>
            </div>
        </div>
    </div>

</section>


<!-- 模态 -->
<div class="modal" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- 模态标题 -->
            <div class="modal-header">
                <h4 class="modal-title">{{ installTrans('install_toast_title') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- 模态主体 -->
            <div class="modal-body"></div>

            <!-- 模态页脚 -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


@include("install.footer")

<script type="text/html" id="exec-success-msg-tpl">
    <li>
        <i class="fa fa-check correct"></i>
        {message}<br>
        <!--<pre>{sql}</pre>-->
    </li>
</script>
<script type="text/html" id="exec-fail-msg-tpl">
    <li>
        <i class="fa fa-remove error"></i>
        {message}<br>
        <pre>{sql}</pre>
        <!--<pre>{exception}</pre>-->
    </li>
</script>
<script type="text/javascript">
    function copyValueById(elementId, successMessage) {
        var text = $('#' + elementId).text().trim();
        if (!text) {
            $('#myModal .modal-body').html('{{ installTrans('install_copy_empty') }}');
            $('#myModal').modal('show');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                $('#myModal .modal-body').html(successMessage);
                $('#myModal').modal('show');
            }).catch(function () {
                fallbackCopyValue(text, successMessage);
            });
            return;
        }

        fallbackCopyValue(text, successMessage);
    }

    function fallbackCopyValue(text, successMessage) {
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(text).trigger('select');
        document.execCommand('copy');
        tempInput.remove();
        $('#myModal .modal-body').html(successMessage);
        $('#myModal').modal('show');
    }

    $(function () {
        $installMsgPanel.append('{{ installTrans('install_wait_message') }}');
        install(0);
    });

    var $installMsgPanel = $('#install-msg-panel');
    var $log = $("#log");
    var execSuccessTpl = $('#exec-success-msg-tpl').html();
    var execFailTpl = $('#exec-fail-msg-tpl').html();
    var sqlExecResult;

    function install(sqlIndex) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "{{url('install/start')}}",
            data: {sql_index: sqlIndex},
            dataType: 'json',
            type: 'post',
            success: function (data) {
                console.log(data);
                var line = sqlIndex + 1;
                if (data.status == 200) {

                    if (!(data.data && data.data.done)) {
                        var tpl = execSuccessTpl;
                        tpl = tpl.replace(/\{message\}/g, line + '.' + data.msg);
                        // tpl     = tpl.replace(/\{sql\}/g, data.data.sql);
                        $installMsgPanel.append(tpl);

                    } else {
                        $installMsgPanel.append('<li><i class="fa fa-check correct"></i>' + '{{ installTrans('install_db_install_done') }}' + '</li>');

                        sqlExecResult = data.data;

                        if (data.data.error) {
                            $("#myModal .modal-body").html('{{ installTrans('install_sql_error_prefix') }}' + data.data.error + '{{ installTrans('install_sql_error_suffix') }}<br>');
                            $('#myModal').modal('show')
                        } else {
                            stepAction(0)
                        }
                    }

                } else {
                    // 安装接口返回失败（如迁移报错）：停止重试循环并明确展示错误，避免“正在安装...”无限转圈。
                    var tpl = execFailTpl;
                    tpl = tpl.replace(/\{message\}/g, line + '.' + (data.msg || '{{ installTrans('install_install_failed') }}'));
                    tpl = tpl.replace(/\{sql\}/g, (data.data && data.data.sql) || '');
                    tpl = tpl.replace(/\{exception\}/g, (data.data && data.data.exception) || '');
                    $installMsgPanel.append(tpl);
                    $("#myModal .modal-body").html(data.msg || '{{ installTrans('install_install_failed_detail') }}');
                    $('#myModal').modal('show');
                    $('a.install-load').html('<i class="fa fa-remove error"></i>' + '{{ installTrans('install_install_failed_check') }}');
                    $log.scrollTop(1000000000);
                    return;
                }

                $log.scrollTop(1000000000);

                if (!(data.data && data.data.done)) {
                    sqlIndex++;
                    install(sqlIndex);
                }


            },
            error: function () {
                $installMsgPanel.append('<li><i class="fa fa-remove error"></i>' + '{{ installTrans('install_request_failed') }}' + '</li>');
                $('a.install-load').html('<i class="fa fa-remove error"></i>' + '{{ installTrans('install_request_failed_short') }}');
            },
            complete: function () {

            }
        });
    }

    var stepUrls = [
        "{{url('install/setDbConfig')}}",
        "{{url('install/installModule?m=System')}}",
        "{{url('install/installModule?m=Auth')}}",
        "{{url('install/installModule?m=Formtools')}}",
        "{{url('install/installModule?m=Member')}}",
        "{{url('install/setSite')}}",
    ];

    function stepAction(index) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: stepUrls[index],
            dataType: 'json',
            data: {_hithinkcmf: 1},
            type: 'post',
            success: function (data) {
                if (data.status == 200) {
                    $installMsgPanel.append('<li><i class="fa fa-check correct"></i>' + data.msg + '</li>');
                    $log.scrollTop(1000000000);
                    if (index + 1 == stepUrls.length) {
                        $('a.install-load').html('<i class="fa fa-check correct"></i>' + '{{ installTrans('install_install_complete') }}');
                        $('.install-ok').show();
                    } else {
                        index++;
                        stepAction(index);
                    }
                } else {
                    $installMsgPanel.append('<li><i class="fa fa-remove error"></i>' + data.msg + '</li>');
                    $log.scrollTop(1000000000);
                    $("#myModal .modal-body").html(data.msg);
                    $('#myModal').modal('show')
                }

            },
            error: function () {

            }
        });
    }
</script>
</body>
</html>
