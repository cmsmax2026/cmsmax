@php
    $fields = $model['frontend_schema']['form'] ?? [];
    $pageTitle = $model['home_config']['detail_page_title'] ?? ($model['home_config']['home_page_title'] ?? ($model['name'] ?? themeTrans('form.handle_default_title')));
    $pageDescription = $model['home_config']['detail_page_describe'] ?? ($model['home_config']['home_page_describe'] ?? themeTrans('form.handle_default_desc'));
@endphp

<div id="content">
    <div class="container">
        @if(session('pageDataMsg'))
            <div class="alert {{session('pageDataStatus') == 200 ? 'alert-success' : 'alert-danger'}}">{{session('pageDataMsg')}}</div>
        @endif

        <div class="mx-page-shell">
            <div class="mx-main-card">
                <h1 class="mx-form-title">{{ $pageTitle }}</h1>
                <p class="mx-form-description">{{ $pageDescription }}</p>

                <form method="post" action="{{ url('handle/' . $param['model']) }}" enctype="multipart/form-data" class="mx-stack">
                    {{ csrf_field() }}

                    <div class="mx-field-grid">
                        @forelse($fields as $field)
                            @php
                                $required = ($field['required'] ?? '') === 'required';
                                $formtype = $field['formtype'] ?? 'text';
                                $options = $field['datas'] ?? [];
                                if (is_string($options)) {
                                    $options = json_decode($options, true) ?: [];
                                }
                                $isFullWidth = in_array($formtype, ['textarea', 'editor', 'json', 'code', 'upload', 'image', 'checkbox', 'checkboxList', 'selectMore', 'multipleSelect'], true);
                            @endphp

                            <div class="{{ $isFullWidth ? 'mx-field--full' : '' }}">
                                <label class="contacts-label">
                                    {{ $field['name'] ?? $field['identification'] }}
                                    @if($required)
                                        <span style="color:#dc2626;">*</span>
                                    @endif
                                </label>

                                @if(in_array($formtype, ['textarea', 'editor', 'json', 'code'], true))
                                    <textarea name="{{ $field['identification'] }}" class="form-control" rows="6" @if($required) required @endif></textarea>
                                @elseif($formtype === 'select')
                                    <select name="{{ $field['identification'] }}" class="form-control" @if($required) required @endif>
                                        <option value="">{{ themeTrans('common.please_select') }}</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option['value'] ?? '' }}">{{ $option['name'] ?? ($option['value'] ?? '') }}</option>
                                        @endforeach
                                    </select>
                                @elseif(in_array($formtype, ['selectMore', 'multipleSelect'], true))
                                    <select name="{{ $field['identification'] }}[]" class="form-control" multiple @if($required) required @endif>
                                        @foreach($options as $option)
                                            <option value="{{ $option['value'] ?? '' }}">{{ $option['name'] ?? ($option['value'] ?? '') }}</option>
                                        @endforeach
                                    </select>
                                @elseif(in_array($formtype, ['radio', 'switch'], true))
                                    <div class="mx-stack">
                                        @foreach($options as $option)
                                            <label class="contacts-label" style="margin-bottom:0;font-weight:500;">
                                                <input type="radio" name="{{ $field['identification'] }}" value="{{ $option['value'] ?? '' }}" @if($required) required @endif>
                                                <span>{{ $option['name'] ?? ($option['value'] ?? '') }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif(in_array($formtype, ['checkbox', 'checkboxList'], true))
                                    <div class="mx-stack">
                                        @foreach($options as $option)
                                            <label class="contacts-label" style="margin-bottom:0;font-weight:500;">
                                                <input type="checkbox" name="{{ $field['identification'] }}[]" value="{{ $option['value'] ?? '' }}">
                                                <span>{{ $option['name'] ?? ($option['value'] ?? '') }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif(in_array($formtype, ['upload', 'image', 'file'], true))
                                    <input type="file" name="{{ $field['identification'] }}" class="form-control" @if($required) required @endif>
                                @else
                                    <input
                                        type="{{ in_array($formtype, ['email', 'url', 'number', 'date', 'time'], true) ? $formtype : 'text' }}"
                                        name="{{ $field['identification'] }}"
                                        class="form-control"
                                        @if($required) required @endif
                                    >
                                @endif

                                @if(!empty($field['notes']))
                                    <p class="mx-submit-tip">{{ $field['notes'] }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="mx-empty mx-field--full">{{ themeTrans('form.empty_fields') }}</div>
                        @endforelse
                    </div>

                    @if($fields)
                        <div class="mx-submit-bar">
                            <span class="mx-submit-tip">{{ themeTrans('form.submit_redirect_tip') }}</span>
                            <button type="submit" class="button h-sub">{{ themeTrans('form.submit_now') }}</button>
                        </div>
                    @endif
                </form>
            </div>

            <aside class="mx-side-card">
                <div class="mx-stack">
                    <div>
                        <h3 class="contacts-info-title">{{ themeTrans('form.submit_notes') }}</h3>
                        <p class="contacts-info-description">{{ themeTrans('form.field_types_tip') }}</p>
                    </div>

                    <div>
                        <h3 class="contacts-info-title">{{ themeTrans('form.model_info') }}</h3>
                        <ul class="mx-meta-list">
                            <li class="mx-meta-pill">{{ themeTrans('form.model_label', ['name' => ($model['name'] ?? $param['model'])]) }}</li>
                            <li class="mx-meta-pill">{{ themeTrans('form.type_label', ['type' => (($model['type'] ?? 'multi') === 'single' ? themeTrans('form.type_single') : themeTrans('form.type_multi'))]) }}</li>
                            <li class="mx-meta-pill">{{ themeTrans('form.template_label', ['template' => ($model['list_template'] ?? 'handle')]) }}</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="contacts-info-title">{{ themeTrans('form.field_overview') }}</h3>
                        <div class="mx-card-list">
                            @foreach($fields as $field)
                                <div class="mx-card">
                                    <h4 class="mx-card__title" style="font-size:16px;">{{ $field['name'] ?? $field['identification'] }}</h4>
                                    <p class="mx-card__desc">{{ $field['notes'] ?? themeTrans('form.field_display_tip') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
