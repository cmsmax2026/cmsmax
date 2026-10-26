<!doctype html>
<html>
<head>
    @include("install.head")
</head>
<body>
<div class="wrap install-shell">
    @php
        $installDefaults = session('install_form_defaults') ?: [];
        $installAdmin = session('install.admin') ?: [];
        $installEnv = session('install.env') ?: [];
        $installSettings = session('install.settings') ?: [];
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
    @include("install.header")
    <section class="container">
        <div class="install-step-nav mt-3">
            <div class="install-step-nav__item"><div><span class="install-step-nav__step">1</span><span class="install-step-nav__label">{{ installTrans('install_step_env_check') }}</span></div><div class="install-step-nav__desc">{{ installTrans('install_step_env_check_desc5') }}</div></div>
            <div class="install-step-nav__item"><div><span class="install-step-nav__step">2</span><span class="install-step-nav__label">{{ installTrans('install_step_create_data') }}</span></div><div class="install-step-nav__desc">{{ installTrans('install_step_create_data_desc3') }}</div></div>
            <div class="install-step-nav__item is-active"><div><span class="install-step-nav__step">3</span><span class="install-step-nav__label">{{ installTrans('install_step_finish') }}</span></div><div class="install-step-nav__desc">{{ installTrans('install_step_finish_desc4') }}</div></div>
        </div>
        <div class="install-panel mt-4">
            <div class="install-panel__body">
                <div class="text-center">
                    <div class="install-section-title">{{ installTrans('install_congrats') }}</div>
                    <div class="install-muted">{{ installTrans('install_congrats_desc') }}</div>
                </div>
                <div class="alert alert-danger install-alert mt-4 text-left">
                    {{ installTrans('install_security_notice') }}
                </div>
                <div class="install-summary-grid mt-4">
                    <div class="install-summary-card">
                        <h6>{{ installTrans('install_site_info') }}</h6>
                        <ul class="install-meta-list">
                            <li><span class="install-meta-list__label">{{ installTrans('install_site_name_label') }}</span><span class="install-meta-list__value">{{$websiteName ?: $cms_name}}</span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_home_url') }}</span><span class="install-meta-list__value"><code id="js-site-home-url">{{url('/')}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_hidden_admin_entrance') }}</span><span class="install-meta-list__value"><code id="js-hidden-admin-url">{{$adminLoginUrl}}</code></span></li>
                        </ul>
                    </div>
                    <div class="install-summary-card">
                        <h6>{{ installTrans('install_admin_info') }}</h6>
                        <ul class="install-meta-list">
                            <li><span class="install-meta-list__label">{{ installTrans('install_admin_account_label') }}</span><span class="install-meta-list__value"><code>{{$installAdmin['username'] ?? 'admin'}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_initial_password') }}</span><span class="install-meta-list__value"><code id="js-admin-password">{{$installAdmin['password'] ?? ''}}</code></span></li>
                            <li><span class="install-meta-list__label">{{ installTrans('install_security_advice') }}</span><span class="install-meta-list__value">{{ installTrans('install_change_pwd_immediately') }}</span></li>
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
                <div class="install-result-actions mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-hidden-admin-url', '{{ installTrans('install_copy_admin_entrance_done') }}')">{{ installTrans('install_copy_admin_entrance_btn') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-admin-password', '{{ installTrans('install_copy_admin_password_done') }}')">{{ installTrans('install_copy_admin_password_btn') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyValueById('js-site-home-url', '{{ installTrans('install_copy_site_home_done') }}')">{{ installTrans('install_copy_home_btn') }}</button>
                </div>
                <div class="install-result-actions mt-4">
                    <a class="btn btn-success" href="{{url('/')}}/">{{ installTrans('install_goto_frontend') }}</a>
                    <a class="btn btn-primary" href="{{$adminLoginUrl}}">{{ installTrans('install_goto_hidden_admin') }}</a>
                </div>
            </div>
        </div>
    </section>
</div>

@include("install.footer")
<script>
    function copyValueById(elementId, successMessage) {
        var text = $('#' + elementId).text().trim();
        if (!text) {
            alert('{{ installTrans('install_copy_empty') }}');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                alert(successMessage);
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
        alert(successMessage);
    }
</script>
</body>
</html>
