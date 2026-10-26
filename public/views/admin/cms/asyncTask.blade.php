@include("admin.public.header")

<body class="horizontal">
@include("admin.public.topbar")
@include("admin.public.nav")

<div class="row page-header">
    <div class="col-lg-6 align-self-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('admin')}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey('main_async_task')}}</li>
        </ol>
    </div>
</div>

<section class="main-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row" style="margin-bottom: 12px;">
                        <div class="col-md-8">
                            <div class="form-group" style="margin:0;">
                                <label style="margin-bottom:6px;">async_id</label>
                                <input type="text" class="form-control" id="async-id" value="{{ $async_id ?? '' }}" placeholder="{{getTranslateByKey('main_enter_async_id')}}" />
                            </div>
                        </div>
                        <div class="col-md-4 text-right" style="padding-top: 26px;">
                            <button class="btn btn-primary" type="button" onclick="loadAsyncTaskStatus(true)">{{getTranslateByKey('main_refresh')}}</button>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 12px;">
                        <div class="col-md-12">
                            <div id="async-summary" class="text-muted" style="line-height: 1.8;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label style="margin-bottom:6px;">{{getTranslateByKey('main_output')}}</label>
                                <pre id="async-output" style="min-height: 200px; white-space: pre-wrap; word-break: break-word; background: #0b1020; color: #d5e0ff; padding: 12px; border-radius: 6px;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include("admin.public.footer")
@include("admin.public.js")

<script>
    var asyncTimer = null;
    function escapeHtml(text) {
        return String(text || '').replace(/[&<>"']/g, function (m) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'})[m];
        });
    }

    function describeStatus(code) {
        code = parseInt(code, 10) || 0;
        if (code === 102) return '{{getTranslateByKey('main_async_status_running')}}';
        if (code === 200) return '{{getTranslateByKey('main_async_status_done')}}';
        if (code === 400) return '{{getTranslateByKey('main_async_status_bad_request')}}';
        if (code === 403) return '{{getTranslateByKey('main_async_status_forbidden')}}';
        return '{{getTranslateByKey('main_async_status_failed')}}';
    }

    function loadAsyncTaskStatus(force) {
        var asyncId = $('#async-id').val() || '';
        asyncId = $.trim(asyncId);
        if (!asyncId) {
            $('#async-summary').html('<span class="text-danger">{{getTranslateByKey('main_async_id_required')}}</span>');
            $('#async-output').text('');
            return;
        }
        $.ajax({
            method: 'get',
            url: @json(url('admin/cms/asyncTask/status')),
            dataType: 'json',
            data: {async_id: asyncId}
        }).done(function (res) {
            if (!res || res.status !== 200 || !res.data) {
                var msg = (res && res.msg) ? res.msg : '{{getTranslateByKey('main_async_status_fetch_failed')}}';
                $('#async-summary').html('<span class="text-danger">' + escapeHtml(msg) + '</span>');
                $('#async-output').text('');
                stopAsyncPolling();
                return;
            }
            var data = res.data || {};
            var statusCode = parseInt(data.status_code, 10) || 0;
            var title = (data.moduleName ? data.moduleName + ' ' : '') + (data.actionName || '');
            var meta = [
                '<strong>' + escapeHtml(title || asyncId) + '</strong>',
                '{{getTranslateByKey('main_async_label_status')}}' + escapeHtml(describeStatus(statusCode)) + ' (' + escapeHtml(statusCode) + ')',
                data.created_at ? ('{{getTranslateByKey('main_async_label_start')}}' + escapeHtml(data.created_at)) : '',
                data.updated_at ? ('{{getTranslateByKey('main_async_label_update')}}' + escapeHtml(data.updated_at)) : '',
                (typeof data.exit_code !== 'undefined' && data.exit_code !== null) ? ('{{getTranslateByKey('main_async_label_exit_code')}}' + escapeHtml(data.exit_code)) : '',
                data.duration_ms ? ('{{getTranslateByKey('main_async_label_duration')}}' + escapeHtml(data.duration_ms) + 'ms') : '',
                data.reason_code ? ('{{getTranslateByKey('main_async_label_reason')}}' + escapeHtml(data.reason_code)) : ''
            ].filter(function (v) { return v !== ''; }).join('，');
            $('#async-summary').html(meta);
            var out = data.output || '';
            if (!out && data.error) {
                out = 'ERROR: ' + data.error;
            }
            $('#async-output').text(out);
            if (statusCode === 102) {
                startAsyncPolling();
            } else {
                stopAsyncPolling();
            }
        }).fail(function () {
            if (force) {
                $('#async-summary').html('<span class="text-danger">{{getTranslateByKey('main_network_error')}}</span>');
                $('#async-output').text('');
            }
        });
    }

    function startAsyncPolling() {
        if (asyncTimer) {
            return;
        }
        asyncTimer = window.setInterval(function () {
            loadAsyncTaskStatus(false);
        }, 1500);
    }

    function stopAsyncPolling() {
        if (!asyncTimer) {
            return;
        }
        window.clearInterval(asyncTimer);
        asyncTimer = null;
    }

    $(function () {
        loadAsyncTaskStatus(true);
    });
</script>
</body>
</html>

