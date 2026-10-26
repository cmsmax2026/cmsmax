@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => 'shorttags', 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5">
    <div class="row pb-5">
        <div class="col-12">
            <div class="alert alert-info">
                {!! getTranslateByKey('main_shorttags_intro_1') !!}
                <br><span class="small">{!! getTranslateByKey('main_shorttags_pair_note') !!}</span>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">{{getTranslateByKey('main_realtime_preview')}}</h6></div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea class="form-control" rows="3" id="tagInput"
                                  placeholder='{{getTranslateByKey('main_shorttags_input_placeholder')}}'></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" id="previewTag">{{getTranslateByKey('main_preview')}}</button>
                    <span class="ml-3 small" id="previewMsg"></span>
                    <div class="mt-3">
                        <div class="small text-muted mb-1">{{getTranslateByKey('main_render_result')}}</div>
                        <div class="p-3 bg-light" id="previewHtml" style="min-height:60px;"></div>
                        <div class="small text-muted mb-1 mt-3">{{getTranslateByKey('main_html_source')}}</div>
                        <pre class="p-2 bg-light" id="previewSource" style="max-height:240px;overflow:auto;font-size:12px;"></pre>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{getTranslateByKey('main_available_tags')}}</h6>
                    <span class="text-muted small">{{getTranslateByKey('main_copy_hint')}}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:110px;">{{getTranslateByKey('main_tag_label')}}</th>
                            <th style="width:110px;">{{getTranslateByKey('main_label_desc')}}</th>
                            <th>{{getTranslateByKey('main_example')}}</th>
                            <th style="width:150px;">{{getTranslateByKey('main_available_attrs')}}</th>
                            <th style="width:80px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($catalog as $item)
                            <tr>
                                <td><strong>{{ $item['tag'] }}</strong></td>
                                <td>{{ $item['label'] }}</td>
                                <td><code class="small">{{ $item['example'] }}</code></td>
                                <td class="small text-muted">{{ implode(', ', $item['attrs']) }}</td>
                                <td><button class="btn btn-sm btn-outline-primary" data-copy="{{ $item['example'] }}">{{getTranslateByKey('main_copy')}}</button></td>
                            </tr>
                            <tr><td colspan="5" class="pt-0 pb-2 small text-muted border-top-0">{{ $item['description'] }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(!empty($dataKeys))
                <div class="card mt-3">
                    <div class="card-header"><h6 class="mb-0">{!! getTranslateByKey('main_registered_providers') !!}</h6></div>
                    <div class="card-body">
                        @foreach($dataKeys as $key)
                            <code class="mr-3 d-inline-block mb-1">{{ $key }}</code>
                        @endforeach
                        <div class="small text-muted mt-2">
                            {!! getTranslateByKey('main_register_more_data') !!}
                        </div>
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
        function copy(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () { alert('{{getTranslateByKey('main_copied')}}' + text); });
                return;
            }
            var input = document.createElement('textarea');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('{{getTranslateByKey('main_copied')}}' + text);
        }

        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () { copy(btn.getAttribute('data-copy')); });
        });

        document.getElementById('previewTag').addEventListener('click', function () {
            var tag = document.getElementById('tagInput').value;
            var msg = document.getElementById('previewMsg');
            msg.textContent = '{{getTranslateByKey('main_rendering')}}';

            var body = new FormData();
            body.append('_token', '{{ csrf_token() }}');
            body.append('m', '{{ $theme }}');
            body.append('tag', tag);

            fetch(window.location.href.split('?')[0], {
                method: 'POST', body: body, headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function (res) { return res.json(); })
              .then(function (res) {
                  msg.textContent = '';
                  var html = (res.data && res.data.html) || '';
                  document.getElementById('previewHtml').innerHTML = html;
                  document.getElementById('previewSource').textContent = html || '{{getTranslateByKey('main_empty_result')}}';
              })
              .catch(function () { msg.textContent = '{{getTranslateByKey('main_request_failed')}}'; });
        });
    })();
</script>
</body>
</html>
