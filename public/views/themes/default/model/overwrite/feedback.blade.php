@php
    $fields = $model['frontend_schema']['form'] ?? [];
    $pageTitle = $model['home_config']['home_page_title'] ?? ($model['name'] ?? themeTrans('form.feedback_default_title'));
    $pageDescription = $model['home_config']['home_page_describe'] ?? themeTrans('form.feedback_default_desc');
    $records = [];
    if (isset($data)) {
        if (is_object($data) && method_exists($data, 'items')) {
            $records = $data->items();
        } elseif (is_iterable($data)) {
            foreach ($data as $item) {
                $records[] = $item;
            }
        }
    }
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
                            <div class="mx-empty mx-field--full">{{ themeTrans('form.empty_feedback_fields') }}</div>
                        @endforelse
                    </div>

                    @if($fields)
                        <div class="mx-submit-bar">
                            <span class="mx-submit-tip">{{ themeTrans('form.submit_tip') }}</span>
                            <button type="submit" class="button h-sub">{{ themeTrans('form.submit_message') }}</button>
                        </div>
                    @endif
                </form>
            </div>

            <aside class="mx-side-card">
                <div class="mx-stack">
                    <div>
                        <h3 class="contacts-info-title">{{ themeTrans('form.latest_messages') }}</h3>
                        @if($records)
                            <div class="mx-card-list">
                                @foreach(array_slice($records, 0, 6) as $record)
                                    @php($item = (array) $record)
                                    <div class="mx-card">
                                        <h4 class="mx-card__title" style="font-size:17px;">{{ $item['full_name'] ?? themeTrans('home.anonymous_visitor') }}</h4>
                                        <p class="mx-card__desc">{{ $item['content'] ?? themeTrans('form.no_content') }}</p>
                                        <div class="mx-card__meta">
                                            <span>{{ $item['company'] ?? themeTrans('home.visitor_message') }}</span>
                                            @if(!empty($item['created_at']))
                                                <span>{{ $item['created_at'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mx-empty">{{ themeTrans('form.empty_records') }}</div>
                        @endif
                    </div>

                    <div>
                        <h3 class="contacts-info-title">{{ themeTrans('form.notes') }}</h3>
                        <p class="contacts-info-description">{{ themeTrans('form.feedback_page_note') }}</p>
                    </div>
                </div>
            </aside>
        </div>

        @if(isset($data) && is_object($data) && method_exists($data, 'links'))
            <div class="row">
                <div class="col-md-12">
                    {{ $data->appends($_GET)->links(theme_view('public.pagination'),['data'=>['side_num'=>2,'page_position'=>data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center')]]) }}
                </div>
            </div>
        @endif
    </div>
</div>
