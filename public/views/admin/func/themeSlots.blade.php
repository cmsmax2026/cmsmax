@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => 'slots', 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5">
    <div class="row pb-5">
        <div class="col-12">
            <div class="alert alert-info">
                {!! getTranslateByKey('main_slots_intro_1') !!}
                <br><span class="small">{{getTranslateByKey('main_slots_baseline')}}{{ date('Y-m-d H:i:s', $baseline) }}{{getTranslateByKey('main_slots_baseline_note')}}</span>
            </div>

            @if(empty($versions))
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        {{getTranslateByKey('main_slots_empty')}}
                    </div>
                </div>
            @else
                @foreach($versions as $version)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                {{getTranslateByKey('main_slots_version')}} {{ $version['version'] }}
                                <span class="badge badge-warning ml-2">{{getTranslateByKey('main_slots_files_count', 'admin', ['count' => $version['count']])}}</span>
                            </h6>
                            <div>
                                <button class="btn btn-sm btn-primary" data-slot-apply="{{ $version['version'] }}">{{getTranslateByKey('main_apply_all')}}</button>
                                <button class="btn btn-sm btn-outline-danger" data-slot-discard="{{ $version['version'] }}">{{getTranslateByKey('main_discard')}}</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" data-slot-checkall="{{ $version['version'] }}"></th>
                                    <th>{{getTranslateByKey('main_file')}}</th>
                                    <th style="width:180px;">{{getTranslateByKey('main_reason')}}</th>
                                    <th style="width:120px;">{{getTranslateByKey('main_written_at')}}</th>
                                    <th style="width:150px;">{{getTranslateByKey('operation')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(($version['manifest']['files'] ?? []) as $file)
                                    <tr>
                                        <td><input type="checkbox" class="slot-file" data-version="{{ $version['version'] }}" value="{{ $file['path'] }}"></td>
                                        <td><code>{{ $file['path'] }}</code></td>
                                        <td>{{ $file['reason'] ?? '' }}</td>
                                        <td class="small text-muted">{{ $file['written_at'] ?? '' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-link" data-slot-diff="{{ $file['path'] }}"
                                                    data-version="{{ $version['version'] }}">{{getTranslateByKey('main_diff')}}</button>
                                            <button class="btn btn-sm btn-link" data-slot-applyone="{{ $file['path'] }}"
                                                    data-version="{{ $version['version'] }}">{{getTranslateByKey('main_apply')}}</button>
                                        </td>
                                    </tr>
                                    <tr class="slot-diff-row d-none" data-diff-for="{{ $file['path'] }}">
                                        <td colspan="5">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="small text-muted mb-1">{{getTranslateByKey('main_your_current_version')}}</div>
                                                    <pre class="p-2 bg-light" style="max-height:320px;overflow:auto;font-size:12px;"><code class="slot-current"></code></pre>
                                                </div>
                                                <div class="col-6">
                                                    <div class="small text-muted mb-1">{{getTranslateByKey('main_new_version_from_upgrade')}}</div>
                                                    <pre class="p-2 bg-light" style="max-height:320px;overflow:auto;font-size:12px;"><code class="slot-new"></code></pre>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!empty($pending))
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">{{getTranslateByKey('main_other_theme_slots')}}</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            @foreach($pending as $item)
                                <tr>
                                    <td><strong>{{ $item['theme'] }}</strong></td>
                                    <td>{{ $item['version'] }}</td>
                                    <td>{{getTranslateByKey('main_slots_files_count', 'admin', ['count' => $item['count']])}}</td>
                                    <td><a href="{{ url('admin/theme/themeSlots?m=' . $item['theme']) }}">{{getTranslateByKey('main_go_handle')}}</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@include('admin.public.footer')
@include('admin.public.js', ['load' => ["custom"]])
<script>
    (function () {
        var base = window.location.href.split('?')[0];
        var fileUrl = '{{ url("admin/theme/themeSlotFile") }}';

        function post(payload, url) {
            payload._token = '{{ csrf_token() }}';
            payload.m = '{{ $theme }}';
            var body = new FormData();
            Object.keys(payload).forEach(function (key) {
                if (Array.isArray(payload[key])) {
                    payload[key].forEach(function (v) { body.append(key + '[]', v); });
                } else {
                    body.append(key, payload[key]);
                }
            });
            return fetch(url || base, {
                method: 'POST', body: body, headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function (res) { return res.json(); });
        }

        document.querySelectorAll('[data-slot-checkall]').forEach(function (box) {
            box.addEventListener('change', function () {
                var version = box.getAttribute('data-slot-checkall');
                document.querySelectorAll('.slot-file[data-version="' + version + '"]').forEach(function (item) {
                    item.checked = box.checked;
                });
            });
        });

        document.querySelectorAll('[data-slot-diff]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var path = btn.getAttribute('data-slot-diff');
                var version = btn.getAttribute('data-version');
                var row = document.querySelector('[data-diff-for="' + CSS.escape(path) + '"]');
                if (!row) return;
                row.classList.toggle('d-none');

                post({act: 'read', version: version, path: path}, fileUrl).then(function (res) {
                    var data = (res.data || {});
                    row.querySelector('.slot-current').textContent = data.current === null ? '{{getTranslateByKey('main_file_not_exists')}}' : (data.current || '');
                    row.querySelector('.slot-new').textContent = data.new === null ? '{{getTranslateByKey('main_file_not_exists')}}' : (data.new || '');
                });
            });
        });

        document.querySelectorAll('[data-slot-apply]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('{{getTranslateByKey('main_slots_apply_confirm')}}')) return;
                post({act: 'apply', version: btn.getAttribute('data-slot-apply'), files: []})
                    .then(function (res) { alert(res.msg); if (res.status === 200) location.reload(); });
            });
        });

        document.querySelectorAll('[data-slot-applyone]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('{{getTranslateByKey('main_slots_applyone_confirm')}}')) return;
                post({act: 'apply', version: btn.getAttribute('data-version'), files: [btn.getAttribute('data-slot-applyone')]})
                    .then(function (res) { alert(res.msg); if (res.status === 200) location.reload(); });
            });
        });

        document.querySelectorAll('[data-slot-discard]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('{{getTranslateByKey('main_slots_discard_confirm')}}')) return;
                post({act: 'discard', version: btn.getAttribute('data-slot-discard')})
                    .then(function (res) { alert(res.msg); if (res.status === 200) location.reload(); });
            });
        });
    })();
</script>
</body>
</html>
