@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => 'settings', 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5">
    <div class="row pb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{getTranslateByKey('theme_configuration')}}</h5>
                    <span class="text-muted small">
                        {{getTranslateByKey('main_theme_settings_desc')}}
                    </span>
                </div>
                <div class="card-body">
                    @if(empty($schema))
                        <div class="alert alert-info mb-0">
                            {!! getTranslateByKey('main_theme_settings_empty', 'admin', ['theme' => '<strong>'.$theme.'</strong>']) !!}
                            <br>{{getTranslateByKey('main_theme_settings_empty_guide')}}
                            <pre class="mt-2 mb-0 p-2 bg-light" style="font-size:12px;">"settings": {
  "schema": [
    {"key":"primary_color","label":"主色","type":"color","default":"#2563eb","group":"配色"}
  ]
}</pre>
                        </div>
                    @else
                        <form id="themeSettingsForm">
                            @csrf
                            <input type="hidden" name="m" value="{{ $theme }}">
                            @php $groups = collect($schema)->groupBy('group'); @endphp
                            @foreach($groups as $group => $items)
                                <h6 class="mt-3 mb-3 text-primary">{{ $group }}</h6>
                                @foreach($items as $item)
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">
                                            {{ $item['label'] }}
                                            <small class="d-block text-muted font-weight-normal">{{ $item['key'] }}</small>
                                        </label>
                                        <div class="col-sm-6">
                                            @php $value = $values[$item['key']] ?? $item['default']; @endphp
                                            @if($item['type'] === 'color')
                                                <div class="input-group">
                                                    <input type="color" class="form-control" style="max-width:70px;"
                                                           name="{{ $item['key'] }}" value="{{ $value }}">
                                                    <input type="text" class="form-control" name="{{ $item['key'] }}_text"
                                                           value="{{ $value }}" data-color-for="{{ $item['key'] }}">
                                                </div>
                                            @elseif($item['type'] === 'select')
                                                <select class="form-control" name="{{ $item['key'] }}">
                                                    @foreach($item['options'] as $optValue => $optLabel)
                                                        <option value="{{ $optValue }}"
                                                            {{ (string) $value === (string) $optValue ? 'selected' : '' }}>
                                                            {{ $optLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($item['type'] === 'textarea')
                                                <textarea class="form-control" rows="3"
                                                          name="{{ $item['key'] }}">{{ $value }}</textarea>
                                            @else
                                                <input type="text" class="form-control" name="{{ $item['key'] }}"
                                                       value="{{ $value }}"
                                                       placeholder="{{ $item['description'] }}">
                                            @endif
                                            @if($item['description'])
                                                <small class="form-text text-muted">{{ $item['description'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach

                            <div class="form-group row mb-0">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="button" class="btn btn-primary" id="saveThemeSettings">{{getTranslateByKey('main_save_config')}}</button>
                                    <span class="ml-3" id="themeSettingsMsg"></span>
                                </div>
                            </div>
                        </form>

                        <hr>
                        <h6 class="text-primary">{{getTranslateByKey('main_use_in_template')}}</h6>
                        {{-- 示例代码里的 {!! !!} / {{ }} 要转义，否则会被 Blade 当成指令提前结束 --}}
                        <pre class="p-2 bg-light" style="font-size:12px;">{{ '{!! theme_css_variables() !!}' }}          {{getTranslateByKey('main_tpl_comment_css')}}
@{{ theme_setting('primary_color') }}      {{getTranslateByKey('main_tpl_comment_get')}}
@@themeSetting('primary_color')            {{getTranslateByKey('main_tpl_comment_directive')}}
{mx:setting key="primary_color" /}         {{getTranslateByKey('main_tpl_comment_shorttag')}}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.public.footer')
@include('admin.public.js', ['load' => ["custom"]])
<script>
    (function () {
        var form = document.getElementById('themeSettingsForm');
        if (!form) return;

        // 颜色输入框与文本框联动
        form.addEventListener('input', function (event) {
            var target = event.target;
            var name = target.getAttribute('name');
            if (!name) return;
            if (target.type === 'color') {
                var text = form.querySelector('[data-color-for="' + name + '"]');
                if (text) text.value = target.value;
            } else if (target.getAttribute('data-color-for')) {
                var color = form.querySelector('input[type="color"][name="' + target.getAttribute('data-color-for') + '"]');
                if (color && /^#[0-9a-fA-F]{6}$/.test(target.value)) color.value = target.value;
            }
        });

        document.getElementById('saveThemeSettings').addEventListener('click', function () {
            var msg = document.getElementById('themeSettingsMsg');
            msg.textContent = '{{getTranslateByKey('main_saving')}}';
            var data = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: data,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function (res) { return res.json(); })
              .then(function (res) {
                  msg.textContent = res.msg || '';
                  msg.style.color = res.status === 200 ? 'green' : 'red';
              })
              .catch(function () {
                  msg.textContent = '{{getTranslateByKey('main_request_failed')}}';
                  msg.style.color = 'red';
              });
        });
    })();
</script>
</body>
</html>
