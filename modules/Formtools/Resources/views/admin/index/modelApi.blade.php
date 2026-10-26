@php($moduleName = $pageData['moduleName'] ?? 'Formtools')
@php($moduleViewName = strtolower($moduleName))
@php($enabledAppCount = $apps->where('status', 1)->count())
@php($fieldCount = count($metaDocument['fields'] ?? []) + count($metaDocument['meta_fields'] ?? []))
@php($metaUrl = $metaDocument['endpoints']['meta']['url'] ?? '')
@php($submitUrl = $metaDocument['endpoints']['submit']['url'] ?? '')
@php($sampleAppId = $apps->first()->app_id ?? 'YOUR_APP_ID')
@php($samplePayload = [])
@foreach(($metaDocument['fields'] ?? []) as $field)
    @php($samplePayload[$field['identification']] = $field['formtype'] === 'number' ? 1 : ($field['name'] ?? $field['identification']))
@endforeach
@if(($metaDocument['config']['allow_seo'] ?? 1) === 1)
    @php($samplePayload['seo_title'] = 'SEO 标题')
@endif
@php($sampleJson = json_encode($samplePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .page-content > .content-wrapper { min-width: 0; }
    .ft-openapi { display: grid; gap: 18px; min-width: 0; }
    .ft-openapi-note { margin-bottom: 0; border-radius: 16px; border: 1px solid #dbeafe; background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%); color: #1e3a8a; }
    .ft-openapi-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; }
    .ft-openapi-stat { padding: 18px 20px; border-radius: 18px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
    .ft-openapi-stat__label { margin: 0; color: #64748b; font-size: 13px; }
    .ft-openapi-stat__value { margin: 10px 0 8px; font-size: 30px; font-weight: 700; color: #0f172a; }
    .ft-openapi-stat__desc { color: #94a3b8; line-height: 1.7; font-size: 12px; }
    .ft-openapi-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr); gap: 18px; align-items: start; }
    .ft-openapi-panel { overflow: hidden; border-radius: 20px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08); min-width: 0; }
    .ft-openapi-panel__head { display: flex; justify-content: space-between; gap: 16px; padding: 22px 24px 16px; border-bottom: 1px solid #eef2f7; }
    .ft-openapi-panel__title { margin: 0 0 8px; color: #0f172a; font-size: 18px; font-weight: 700; }
    .ft-openapi-panel__desc { margin: 0; color: #64748b; line-height: 1.8; }
    .ft-openapi-panel__body { padding: 22px 24px 24px; }
    .ft-openapi-code { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-family: Consolas, Monaco, monospace; font-size: 12px; }
    .ft-openapi-kv { display: grid; gap: 14px; }
    .ft-openapi-kv__item { padding: 14px 16px; border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .ft-openapi-kv__label { display: block; margin-bottom: 6px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
    .ft-openapi-kv__value { color: #0f172a; line-height: 1.7; word-break: break-all; }
    .ft-openapi-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .ft-openapi-form-grid__full { grid-column: 1 / -1; }
    .ft-openapi-table-wrap { overflow-x: auto; }
    .ft-openapi-table { width: 100%; min-width: 980px; margin-bottom: 0; }
    .ft-openapi-table > thead > tr > th { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; color: #64748b; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; background: #f8fafc; }
    .ft-openapi-table > tbody > tr > td { padding: 16px; border-top: 1px solid #edf2f7; vertical-align: top; }
    .ft-openapi-table > tbody > tr:hover { background: #f8fbff; }
    .ft-openapi-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .ft-openapi-badge--success { background: #dcfce7; color: #15803d; }
    .ft-openapi-badge--danger { background: #fee2e2; color: #b91c1c; }
    .ft-openapi-badge--warning { background: #fef3c7; color: #b45309; }
    .ft-openapi-badge--primary { background: #dbeafe; color: #1d4ed8; }
    .ft-openapi-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .ft-openapi-pre { margin: 0; padding: 16px 18px; border-radius: 16px; background: #0f172a; color: #e2e8f0; font-size: 12px; line-height: 1.8; overflow-x: auto; }
    .ft-openapi-secret { margin-top: 8px; word-break: break-all; font-family: Consolas, Monaco, monospace; font-size: 13px; }
    .ft-openapi-empty { padding: 30px 18px; text-align: center; color: #94a3b8; }
    @media (max-width: 1199px) {
        .ft-openapi-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767px) {
        .ft-openapi-form-grid { grid-template-columns: 1fr; }
        .ft-openapi-panel__head, .ft-openapi-panel__body { padding-left: 16px; padding-right: 16px; }
    }
</style>
<body>
@include(moduleAdminTemplate($moduleName)."public.nav")
<div class="page-container">
    <div class="page-content">
        @include(moduleAdminTemplate($moduleName)."public.left")
        <div class="content-wrapper">
            <div class="content" style="margin-top: 1rem;">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
                <div class="ft-openapi">
                    <div class="alert alert-info alert-styled-left ft-openapi-note">
                        <span>{{ getTranslateByKey('ft_api_note') }}</span>
                    </div>

                    @if($newSecretInfo)
                        <div class="alert alert-warning alert-styled-left">
                            <div>{{ getTranslateByKey('ft_api_secret_once') }}</div>
                            <div class="ft-openapi-secret">AppId：{{ $newSecretInfo['app_id'] ?? '-' }}</div>
                            <div class="ft-openapi-secret">AppSecret：{{ $newSecretInfo['app_secret'] ?? '-' }}</div>
                        </div>
                    @endif

                    @if(!$model->access_identification)
                        <div class="alert alert-danger alert-styled-left">
                            <span>{{ getTranslateByKey('ft_api_no_access_id') }}</span>
                        </div>
                    @endif

                    <div class="ft-openapi-overview">
                        <div class="ft-openapi-stat">
                            <p class="ft-openapi-stat__label">{{ getTranslateByKey('ft_api_model_status') }}</p>
                            <div class="ft-openapi-stat__value">{{ ($modelConfig['enabled'] ?? 0) == 1 ? getTranslateByKey('ft_api_enabled') : getTranslateByKey('ft_api_not_enabled') }}</div>
                            <div class="ft-openapi-stat__desc">{{ getTranslateByKey('ft_api_model_status_desc') }}</div>
                        </div>
                        <div class="ft-openapi-stat">
                            <p class="ft-openapi-stat__label">{{ getTranslateByKey('ft_api_open_apps') }}</p>
                            <div class="ft-openapi-stat__value">{{ $apps->count() }}</div>
                            <div class="ft-openapi-stat__desc">{{ getTranslateByKey('ft_api_open_apps_desc', 'admin', ['count' => $enabledAppCount]) }}</div>
                        </div>
                        <div class="ft-openapi-stat">
                            <p class="ft-openapi-stat__label">{{ getTranslateByKey('ft_api_field_doc') }}</p>
                            <div class="ft-openapi-stat__value">{{ $fieldCount }}</div>
                            <div class="ft-openapi-stat__desc">{{ getTranslateByKey('ft_api_field_doc_desc') }}</div>
                        </div>
                        <div class="ft-openapi-stat">
                            <p class="ft-openapi-stat__label">{{ getTranslateByKey('ft_api_recent_logs') }}</p>
                            <div class="ft-openapi-stat__value">{{ $logs->total() }}</div>
                            <div class="ft-openapi-stat__desc">{{ getTranslateByKey('ft_api_recent_logs_desc') }}</div>
                        </div>
                    </div>

                    <div class="ft-openapi-grid">
                        <div class="ft-openapi-panel">
                            <div class="ft-openapi-panel__head">
                                <div>
                                    <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_model_config') }}</h3>
                                    <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_model_config_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-openapi-panel__body">
                                <form method="post" action="{{ url('admin/formtools/modelApi?id='.$model->id) }}">
                                    @csrf
                                    <div class="ft-openapi-form-grid">
                                        <div>
                                            <label>{{ getTranslateByKey('ft_model_name') }}</label>
                                            <input type="text" class="form-control" value="{{ $model->name }}" disabled>
                                        </div>
                                        <div>
                                            <label>{{ getTranslateByKey('ft_access_identification') }}</label>
                                            <input type="text" class="form-control" value="{{ $model->access_identification ?: getTranslateByKey('ft_not_configured') }}" disabled>
                                        </div>
                                        <div>
                                            <label>{{ getTranslateByKey('ft_api_open_status') }}</label>
                                            <select name="enabled" class="form-control">
                                                <option value="1" @if(($modelConfig['enabled'] ?? 0) == 1) selected @endif>{{ getTranslateByKey('common_enable') }}</option>
                                                <option value="0" @if(($modelConfig['enabled'] ?? 0) != 1) selected @endif>{{ getTranslateByKey('ft_api_stop') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>{{ getTranslateByKey('ft_api_default_status') }}</label>
                                            <select name="default_status" class="form-control">
                                                <option value="1" @if(($modelConfig['default_status'] ?? 1) == 1) selected @endif>{{ getTranslateByKey('ft_api_pass') }}</option>
                                                <option value="0" @if(($modelConfig['default_status'] ?? 1) == 0) selected @endif>{{ getTranslateByKey('ft_api_not_pass') }}</option>
                                                <option value="2" @if(($modelConfig['default_status'] ?? 1) == 2) selected @endif>{{ getTranslateByKey('ft_api_offline') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>{{ getTranslateByKey('ft_api_seo_open') }}</label>
                                            <select name="allow_seo" class="form-control">
                                                <option value="1" @if(($modelConfig['allow_seo'] ?? 1) == 1) selected @endif>{{ getTranslateByKey('ft_api_allow_submit') }}</option>
                                                <option value="0" @if(($modelConfig['allow_seo'] ?? 1) != 1) selected @endif>{{ getTranslateByKey('ft_api_forbid_submit') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>{{ getTranslateByKey('ft_api_global_switch') }}</label>
                                            <input type="text" class="form-control" value="{{ ($globalSettings['enabled'] ?? 0) == 1 ? getTranslateByKey('ft_api_enabled') : getTranslateByKey('ft_api_global_disabled') }}" disabled>
                                        </div>
                                        <div class="ft-openapi-form-grid__full">
                                            <label>{{ getTranslateByKey('ft_api_model_ip_whitelist') }}</label>
                                            <textarea name="ip_whitelist" rows="4" class="form-control" placeholder="127.0.0.1&#10;192.168.1.0/24">{{ $modelConfig['ip_whitelist'] ?? '' }}</textarea>
                                        </div>
                                        <div class="ft-openapi-form-grid__full">
                                            <label>{{ getTranslateByKey('ft_api_remark_label') }}</label>
                                            <textarea name="remark" rows="4" class="form-control" placeholder="{{ getTranslateByKey('ft_api_remark_placeholder') }}">{{ $modelConfig['remark'] ?? '' }}</textarea>
                                        </div>
                                        <div class="ft-openapi-form-grid__full">
                                            <button type="submit" class="btn btn-primary">{{ getTranslateByKey('ft_api_save_model_config') }}</button>
                                            <a href="{{ url('admin/formtools/modelEdit?id='.$model->id) }}" class="btn btn-default">{{ getTranslateByKey('ft_api_back_model_config') }}</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="ft-openapi-panel">
                            <div class="ft-openapi-panel__head">
                                <div>
                                    <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_interface_info') }}</h3>
                                    <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_interface_info_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-openapi-panel__body">
                                <div class="ft-openapi-kv">
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_meta_url') }}</span>
                                        <div class="ft-openapi-kv__value">{{ $metaUrl ?: getTranslateByKey('ft_api_no_access_id_short') }}</div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_submit_url') }}</span>
                                        <div class="ft-openapi-kv__value">{{ $submitUrl ?: getTranslateByKey('ft_api_no_access_id_short') }}</div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_auth_header') }}</span>
                                        <div class="ft-openapi-kv__value">`X-Formtools-AppId` / `X-Formtools-AppSecret` / `X-Formtools-Idempotency-Key`</div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_auth_desc') }}</span>
                                        <div class="ft-openapi-kv__value">
                                            <pre class="ft-openapi-pre">{{ getTranslateByKey('ft_api_auth_desc_text') }}</pre>
                                        </div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_request_json') }}</span>
                                        <div class="ft-openapi-kv__value">
                                            <pre class="ft-openapi-pre">{{ $sampleJson }}</pre>
                                        </div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_curl_example') }}</span>
                                        <div class="ft-openapi-kv__value">
                                            <pre class="ft-openapi-pre">curl -X POST "{{ $submitUrl }}"
  -H "Content-Type: application/json"
  -H "X-Formtools-AppId: {{ $sampleAppId }}"
  -H "X-Formtools-AppSecret: YOUR_APP_SECRET"
  -H "X-Formtools-Idempotency-Key: idem_{{ date('YmdHis') }}"
  -d @payload.json</pre>
                                        </div>
                                    </div>
                                    <div class="ft-openapi-kv__item">
                                        <span class="ft-openapi-kv__label">{{ getTranslateByKey('ft_api_form_example') }}</span>
                                        <div class="ft-openapi-kv__value">
                                            <pre class="ft-openapi-pre">POST {{ $submitUrl }}
Content-Type: application/x-www-form-urlencoded

app_id={{ $sampleAppId }}
app_secret=YOUR_APP_SECRET
idempotency_key=idem_{{ date('YmdHis') }}
title=示例标题&content=示例内容</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ft-openapi-panel">
                        <div class="ft-openapi-panel__head">
                            <div>
                                <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_open_apps') }}</h3>
                                <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_apps_desc') }}</p>
                            </div>
                        </div>
                        <div class="ft-openapi-panel__body">
                            <form method="post" action="{{ url('admin/formtools/modelApi/appCreate') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $model->id }}">
                                <div class="ft-openapi-form-grid">
                                    <div>
                                        <label>{{ getTranslateByKey('ft_api_app_name') }}</label>
                                        <input type="text" name="app_name" class="form-control" placeholder="{{ getTranslateByKey('ft_api_app_name_placeholder') }}">
                                    </div>
                                    <div>
                                        <label>{{ getTranslateByKey('ft_api_rate_limit') }}</label>
                                        <input type="number" name="rate_limit" class="form-control" min="1" value="{{ $globalSettings['rate_limit'] ?? 60 }}">
                                    </div>
                                    <div>
                                        <label>{{ getTranslateByKey('ft_api_init_status') }}</label>
                                        <select name="status" class="form-control">
                                            <option value="1">{{ getTranslateByKey('common_enable') }}</option>
                                            <option value="0">{{ getTranslateByKey('ft_api_stop') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label>{{ getTranslateByKey('ft_api_app_ip_whitelist') }}</label>
                                        <input type="text" name="ip_whitelist" class="form-control" placeholder="127.0.0.1,192.168.1.0/24">
                                    </div>
                                    <div class="ft-openapi-form-grid__full">
                                        <label>{{ getTranslateByKey('ft_remark') }}</label>
                                        <input type="text" name="remark" class="form-control" placeholder="{{ getTranslateByKey('ft_api_app_remark_placeholder') }}">
                                    </div>
                                    <div class="ft-openapi-form-grid__full">
                                        <button type="submit" class="btn btn-success" @if(!$model->access_identification) disabled @endif>{{ getTranslateByKey('ft_api_create_app') }}</button>
                                    </div>
                                </div>
                            </form>

                            <div class="ft-openapi-table-wrap" style="margin-top: 22px;">
                                <table class="table ft-openapi-table">
                                    <thead>
                                    <tr>
                                        <th>{{ getTranslateByKey('ft_api_th_app') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_status_secret') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_rate_whitelist') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_recent_call') }}</th>
                                        <th>{{ getTranslateByKey('operation') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($apps as $app)
                                        <tr>
                                            <td>
                                                <div><strong>{{ $app->app_name }}</strong></div>
                                                <div style="margin-top: 8px;">AppId <span class="ft-openapi-code">{{ $app->app_id }}</span></div>
                                                <div style="margin-top: 8px; color: #64748b;">{{ $app->remark ?: getTranslateByKey('ft_api_no_remark') }}</div>
                                            </td>
                                            <td>
                                                @if((int) $app->status === 1)
                                                    <span class="ft-openapi-badge ft-openapi-badge--success">{{ getTranslateByKey('status_enabled') }}</span>
                                                @else
                                                    <span class="ft-openapi-badge ft-openapi-badge--danger">{{ getTranslateByKey('ft_api_disabled') }}</span>
                                                @endif
                                                <div style="margin-top: 10px;">{{ getTranslateByKey('ft_api_secret_fingerprint') }} <span class="ft-openapi-code">{{ $app->secret_hint ?: '-' }}</span></div>
                                                <div style="margin-top: 8px; color: #64748b;">{{ getTranslateByKey('ft_api_created_with', 'admin', ['name' => $app->created_at ?: '-']) }}</div>
                                            </td>
                                            <td>
                                                <div>{{ getTranslateByKey('ft_api_rate_with', 'admin', ['name' => $app->rate_limit ?: 0]) }}</div>
                                                <div style="margin-top: 8px; color: #64748b;">{{ $app->ip_whitelist ?: getTranslateByKey('ft_api_no_ip_limit') }}</div>
                                            </td>
                                            <td>
                                                <div>{{ getTranslateByKey('ft_api_time_with', 'admin', ['name' => $app->last_used_at ?: getTranslateByKey('ft_api_none')]) }}</div>
                                                <div style="margin-top: 8px;">{{ getTranslateByKey('ft_api_ip_with', 'admin', ['name' => $app->last_ip ?: '-']) }}</div>
                                            </td>
                                            <td>
                                                <div class="ft-openapi-actions">
                                                    <form method="post" action="{{ url('admin/formtools/modelApi/appStatus') }}" style="display: inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $model->id }}">
                                                        <input type="hidden" name="app_row_id" value="{{ $app->id }}">
                                                        <input type="hidden" name="status" value="{{ (int) $app->status === 1 ? 0 : 1 }}">
                                                        <button type="submit" class="btn btn-default btn-xs">{{ (int) $app->status === 1 ? getTranslateByKey('ft_api_stop') : getTranslateByKey('common_enable') }}</button>
                                                    </form>
                                                    <form method="post" action="{{ url('admin/formtools/modelApi/resetSecret') }}" style="display: inline-block;" onsubmit="return confirm('{{ getTranslateByKey('ft_api_reset_confirm') }}');">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $model->id }}">
                                                        <input type="hidden" name="app_row_id" value="{{ $app->id }}">
                                                        <button type="submit" class="btn btn-warning btn-xs">{{ getTranslateByKey('ft_api_reset_secret') }}</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="ft-openapi-empty">{{ getTranslateByKey('ft_api_no_apps') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="ft-openapi-panel">
                        <div class="ft-openapi-panel__head">
                            <div>
                                <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_fields_title') }}</h3>
                                <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_fields_desc') }}</p>
                            </div>
                        </div>
                        <div class="ft-openapi-panel__body">
                            <div class="ft-openapi-table-wrap">
                                <table class="table ft-openapi-table">
                                    <thead>
                                    <tr>
                                        <th>{{ getTranslateByKey('ft_api_th_field_id') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_field_name') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_type') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_required') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_remark') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach(($metaDocument['fields'] ?? []) as $field)
                                        <tr>
                                            <td><span class="ft-openapi-code">{{ $field['identification'] }}</span></td>
                                            <td>{{ $field['name'] ?? $field['identification'] }}</td>
                                            <td>{{ $field['formtype'] ?? 'text' }}</td>
                                            <td>
                                                @if(!empty($field['required']))
                                                    <span class="ft-openapi-badge ft-openapi-badge--warning">{{ getTranslateByKey('ft_api_required') }}</span>
                                                @else
                                                    <span class="ft-openapi-badge ft-openapi-badge--primary">{{ getTranslateByKey('ft_api_optional') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $field['remark'] ?: getTranslateByKey('ft_api_field_default_remark') }}</td>
                                        </tr>
                                    @endforeach
                                    @foreach(($metaDocument['meta_fields'] ?? []) as $field)
                                        <tr>
                                            <td><span class="ft-openapi-code">{{ $field['identification'] }}</span></td>
                                            <td>{{ $field['name'] ?? $field['identification'] }}</td>
                                            <td>{{ $field['formtype'] ?? 'text' }}</td>
                                            <td><span class="ft-openapi-badge ft-openapi-badge--primary">{{ getTranslateByKey('ft_api_optional') }}</span></td>
                                            <td>{{ $field['remark'] ?? getTranslateByKey('ft_api_meta_field_remark') }}</td>
                                        </tr>
                                    @endforeach
                                    @if($fieldCount === 0)
                                        <tr>
                                            <td colspan="5" class="ft-openapi-empty">{{ getTranslateByKey('ft_api_no_fields') }}</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="ft-openapi-grid">
                        <div class="ft-openapi-panel">
                            <div class="ft-openapi-panel__head">
                                <div>
                                    <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_example_title') }}</h3>
                                    <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_example_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-openapi-panel__body">
<pre class="ft-openapi-pre">POST {{ $submitUrl ?: '/api/formtools/open/{access_identification}/submit' }}
X-Formtools-AppId: {{ $sampleAppId }}
X-Formtools-AppSecret: YOUR_APP_SECRET
X-Formtools-Idempotency-Key: UNIQUE_KEY
Content-Type: application/json

{{ $sampleJson }}</pre>
                            </div>
                        </div>

                        <div class="ft-openapi-panel">
                            <div class="ft-openapi-panel__head">
                                <div>
                                    <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_meta_interface') }}</h3>
                                    <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_meta_interface_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-openapi-panel__body">
<pre class="ft-openapi-pre">GET {{ $metaUrl ?: '/api/formtools/open/{access_identification}/meta' }}</pre>
                            </div>
                        </div>
                    </div>

                    <div class="ft-openapi-panel">
                        <div class="ft-openapi-panel__head">
                            <div>
                                <h3 class="ft-openapi-panel__title">{{ getTranslateByKey('ft_api_logs_title') }}</h3>
                                <p class="ft-openapi-panel__desc">{{ getTranslateByKey('ft_api_logs_desc') }}</p>
                            </div>
                        </div>
                        <div class="ft-openapi-panel__body">
                            <div class="ft-openapi-table-wrap">
                                <table class="table ft-openapi-table">
                                    <thead>
                                    <tr>
                                        <th>{{ getTranslateByKey('ft_th_time') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_channel') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_status') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_app_ip') }}</th>
                                        <th>{{ getTranslateByKey('ft_api_th_summary') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($logs as $log)
                                        <tr>
                                            <td>{{ $log->created_at ?: '-' }}</td>
                                            <td>
                                                <span class="ft-openapi-badge ft-openapi-badge--primary">
                                                    openapi
                                                </span>
                                            </td>
                                            <td>
                                                @if((int) $log->status_code === 200)
                                                    <span class="ft-openapi-badge ft-openapi-badge--success">{{ getTranslateByKey('ft_api_success') }}</span>
                                                @else
                                                    <span class="ft-openapi-badge ft-openapi-badge--danger">{{ getTranslateByKey('ft_api_failed') }}</span>
                                                @endif
                                                <div style="margin-top: 8px;">HTTP/{{ $log->http_method }} {{ $log->status_code }}</div>
                                            </td>
                                            <td>
                                                <div>{{ getTranslateByKey('ft_api_appid_with', 'admin', ['name' => $log->app_id ?: '-']) }}</div>
                                                <div style="margin-top: 8px;">{{ getTranslateByKey('ft_api_ip_with', 'admin', ['name' => $log->client_ip ?: '-']) }}</div>
                                            </td>
                                            <td>
                                                <div>{{ $log->message ?: '-' }}</div>
                                                @if($log->request_id)
                                                    <div style="margin-top: 8px;">RequestId：<span class="ft-openapi-code">{{ $log->request_id }}</span></div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="ft-openapi-empty">{{ getTranslateByKey('ft_api_no_logs') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right text-center-xs" style="margin-top: 16px;">
                                {{ $logs->appends(request()->except('page'))->links($moduleViewName.'::admin.public.pagination',["data"=>$logs]) }}
                            </div>
                        </div>
                    </div>
                </div>
                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>
