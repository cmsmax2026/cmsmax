<!doctype html>
<html>
<head>
    @include("install.head")
</head>
<body>
<div class="container install-shell">
    @include("install.header")
    <div class="install-step-nav mt-3">
        <div class="install-step-nav__item is-active">
            <div><span class="install-step-nav__step">1</span><span class="install-step-nav__label">{{ installTrans('install_step1_label') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step1_desc') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">2</span><span class="install-step-nav__label">{{ installTrans('install_step_env_check') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step_env_check_desc') }}</div>
        </div>
        <div class="install-step-nav__item">
            <div><span class="install-step-nav__step">3</span><span class="install-step-nav__label">{{ installTrans('install_step3_label') }}</span></div>
            <div class="install-step-nav__desc">{{ installTrans('install_step3_desc') }}</div>
        </div>
    </div>
    <div class="install-panel mt-4">
        <div class="install-panel__body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <div class="install-section-title mb-1">{{ installTrans('install_agreement_title', ['name' => $cms_name]) }}</div>
                    <div class="install-muted small">{{ installTrans('install_agreement_read_hint') }}</div>
                </div>
                <span class="install-badge"><i class="fa fa-file-text-o"></i> {{ installTrans('install_agreement_badge') }}</span>
            </div>
            <div class="install-info-card p-0">
                <div class="card-body p-3">
                    <div id="js-agreement-container" class="card-text overflow-auto text-secondary small" style="height: 60vh;overflow: auto">
                        <div class="text-muted">{{ installTrans('install_agreement_loading') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="install-bottom-actions">
        <a href="{{url('/install?install=2')}}" class="btn btn-primary">{{ installTrans('install_agreement_accept_btn') }}</a>
    </div>
</div>
@include("install.footer")
<script>
    function renderAgreementFallback(message, url) {
        var html = '';
        if (message) {
            html += '<div class="text-muted mb-2">' + message + '</div>';
        }
        if (url) {
            html += '<div><a href="' + url + '" target="_blank" rel="noopener">{{ installTrans('install_agreement_view_online') }}</a></div>';
        }
        $('#js-agreement-container').html(html || '<div class="text-muted">{{ installTrans('install_agreement_load_failed') }}</div>');
    }

    function loadAgreement() {
        $.ajax({
            url: "{{url('/install/agreement')}}?t=" + Date.now(),
            type: 'GET',
            dataType: 'json',
            timeout: 6000,
            success: function (result) {
                if (result && result.status === 200 && result.data && result.data.html) {
                    $('#js-agreement-container').html(result.data.html);
                    return;
                }
                renderAgreementFallback(result && result.msg ? result.msg : '{{ installTrans('install_agreement_load_failed_online') }}', result && result.data ? result.data.fallback_url : '');
            },
            error: function () {
                renderAgreementFallback('{{ installTrans('install_agreement_network_error') }}', 'https://www.mxzcloud.com/api/cloud/getAgrees');
            }
        });
    }

    $(function () {
        loadAgreement();
    });
</script>
</body>
</html>
