@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => 'template', 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5">
    <div class="row pb-5">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{getTranslateByKey('main_template_files')}}</h5>
                    <select id="themeSwitch" class="form-control form-control-sm" style="width:auto;">
                        @foreach($themes as $t)
                            <option value="{{ $t['identification'] }}" {{ $t['identification'] === $theme ? 'selected' : '' }}>
                                {{ $t['name'] }} ({{ $t['identification'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body" style="max-height:72vh;overflow:auto;">
                    <ul class="mx-filetree list-unstyled mb-0">
                        @foreach($tree as $node)
                            @include('admin.func._templateNode', ['node' => $node, 'theme' => $theme, 'current' => $currentFile])
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="currentFileName">{{ $currentFile ?: getTranslateByKey('main_no_file_selected') }}</h5>
                    <div>
                        <select id="shortTagInsert" class="form-control form-control-sm d-inline-block" style="width:auto;">
                            <option value="">{{getTranslateByKey('main_insert_shorttag')}}</option>
                            @foreach($catalog as $c)
                                <option value="{{ $c['tag'] }}">{{ $c['label'] }}（{mx:{{ $c['tag'] }}…}）</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary btn-sm ml-2" id="saveTemplate">{{getTranslateByKey('common_save')}}</button>
                        <span class="ml-2" id="saveMsg"></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <textarea id="templateCode">{{ $content }}</textarea>
                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.public.footer')
@include('admin.public.js', ['load' => ["custom"]])

<link rel="stylesheet" href="{{ asset('diy/libs/codemirror/lib/codemirror.css') }}">
<script src="{{ asset('diy/libs/codemirror/lib/codemirror.js') }}"></script>
<script src="{{ asset('diy/libs/codemirror/lib/xml.js') }}"></script>
<script src="{{ asset('diy/libs/codemirror/lib/css.js') }}"></script>
<script src="{{ asset('diy/libs/codemirror/lib/formatting.js') }}"></script>
<style>
    .mx-filetree a { color: #495057; text-decoration: none; }
    .mx-filetree a:hover { color: #2563eb; }
    .mx-dir-name { font-weight: 600; color: #6c757d; }
    .CodeMirror { border: 1px solid #e3e6f0; font-size: 13px; }
</style>
<script>
(function () {
    var code = document.getElementById('templateCode');
    var editor = CodeMirror.fromTextArea(code, {
        lineNumbers: true,
        mode: 'xml',
        lineWrapping: true,
        matchBrackets: true,
        indentUnit: 4,
        tabSize: 4
    });
    editor.setSize('100%', '70vh');

    var theme = '{{ $theme }}';
    var currentFile = '{{ $currentFile }}';

    // 文件树：点击读取内容（raw=1 返回 JSON，不渲染整页）
    document.querySelectorAll('.mx-filetree a[data-file]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var f = a.getAttribute('data-file');
            fetch('?file=' + encodeURIComponent(f) + '&raw=1', {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.status === 200) {
                        currentFile = f;
                        editor.setValue(res.data.content);
                        document.getElementById('currentFileName').textContent = f;
                        document.querySelectorAll('.mx-filetree a[data-file]').forEach(function (x) {
                            x.classList.remove('font-weight-bold', 'text-primary');
                        });
                        a.classList.add('font-weight-bold', 'text-primary');
                    } else {
                        alert('{{getTranslateByKey('main_read_failed')}}');
                    }
                });
        });
    });

    // 切换主题
    var sw = document.getElementById('themeSwitch');
    if (sw) sw.addEventListener('change', function () { window.location.href = '?m=' + sw.value; });

    // 插入短标签
    var insert = document.getElementById('shortTagInsert');
    insert.addEventListener('change', function () {
        var tag = insert.value;
        if (!tag) return;
        editor.replaceSelection('{mx:' + tag + ' /}');
        insert.value = '';
        editor.focus();
    });

    // 保存（写盘 + 清视图编译缓存）
    document.getElementById('saveTemplate').addEventListener('click', function () {
        if (!currentFile) { alert('{{getTranslateByKey('main_select_file_first')}}'); return; }
        var msg = document.getElementById('saveMsg');
        msg.textContent = '{{getTranslateByKey('main_saving')}}';
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('m', theme);
        fd.append('file', currentFile);
        fd.append('content', editor.getValue());
        fetch('{{ url("admin/theme/templateSave") }}', {
            method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (r) { return r.json(); })
          .then(function (res) {
              msg.textContent = res.msg || '';
              msg.style.color = res.status === 200 ? 'green' : 'red';
          }).catch(function () { msg.textContent = '{{getTranslateByKey('main_request_failed')}}'; msg.style.color = 'red'; });
    });
})();
</script>
</body>
</html>
