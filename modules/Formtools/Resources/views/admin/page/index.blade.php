@php($moduleName = $pageData['moduleName'] ?? 'Formtools')
@php($moduleViewName = strtolower($moduleName))
@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .page-content > .content-wrapper {
        min-width: 0;
    }

    .ft-page-index {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .ft-page-note {
        margin-bottom: 0;
        border-radius: 16px;
        border: 1px solid #dbeafe;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        color: #1e3a8a;
    }

    .ft-page-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        min-width: 0;
    }

    .ft-page-stat {
        padding: 18px 20px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .ft-page-stat__label {
        margin: 0;
        color: #64748b;
        font-size: 13px;
    }

    .ft-page-stat__value {
        margin: 10px 0 8px;
        font-size: 30px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-page-stat__desc {
        color: #94a3b8;
        line-height: 1.7;
        font-size: 12px;
    }

    .ft-page-panel {
        overflow: hidden;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        min-width: 0;
        max-width: 100%;
    }

    .ft-page-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 24px 18px;
        border-bottom: 1px solid #eef2f7;
    }

    .ft-page-toolbar__title {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 20px;
        font-weight: 700;
    }

    .ft-page-toolbar__desc {
        margin: 0;
        color: #64748b;
        line-height: 1.8;
    }

    .ft-page-toolbar__actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ft-page-table-wrap {
        padding: 0 22px 18px;
        min-width: 0;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: visible;
    }

    .ft-page-table {
        margin-bottom: 0;
        min-width: 1080px;
    }

    .ft-page-table > thead > tr > th {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: #f8fafc;
    }

    .ft-page-table > tbody > tr > td {
        padding: 18px 16px;
        border-top: 1px solid #edf2f7;
        vertical-align: top;
    }

    .ft-page-table > tbody > tr:hover {
        background: #f8fbff;
    }

    .ft-page-main__title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        overflow-wrap: anywhere;
    }

    .ft-page-meta {
        margin-top: 8px;
        color: #64748b;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .ft-page-code {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .ft-page-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .ft-page-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
    }

    .ft-page-badge--primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .ft-page-badge--success {
        background: #dcfce7;
        color: #15803d;
    }

    .ft-page-badge--warning {
        background: #fef3c7;
        color: #b45309;
    }

    .ft-page-badge--danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .ft-page-actions {
        display: grid;
        gap: 10px;
        min-width: 200px;
    }

    .ft-page-action-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .ft-page-action-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ft-page-empty {
        padding: 36px 20px !important;
        text-align: center;
        color: #94a3b8;
    }

    .ft-page-pagination {
        margin-top: 18px;
    }

    .ft-page-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    @media (max-width: 991px) {
        .ft-page-toolbar {
            flex-direction: column;
        }
    }

    @media (max-width: 767px) {
        .ft-page-overview {
            grid-template-columns: 1fr;
        }

        .ft-page-table-wrap {
            padding: 0 14px 14px;
        }
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

                <div class="ft-page-index">
                    <div class="alert alert-info alert-styled-left ft-page-note">
                        <span>{{ getTranslateByKey('ft_pg_note') }}</span>
                    </div>

                    <div class="ft-page-overview">
                        <div class="ft-page-stat">
                            <p class="ft-page-stat__label">{{ getTranslateByKey('ft_pg_total') }}</p>
                            <p class="ft-page-stat__value">{{$statistics['total']}}</p>
                            <div class="ft-page-stat__desc">{{ getTranslateByKey('ft_pg_total_desc') }}</div>
                        </div>
                        <div class="ft-page-stat">
                            <p class="ft-page-stat__label">{{ getTranslateByKey('ft_pg_enabled') }}</p>
                            <p class="ft-page-stat__value">{{$statistics['enabled']}}</p>
                            <div class="ft-page-stat__desc">{{ getTranslateByKey('ft_pg_enabled_desc') }}</div>
                        </div>
                        <div class="ft-page-stat">
                            <p class="ft-page-stat__label">{{ getTranslateByKey('ft_pg_visual') }}</p>
                            <p class="ft-page-stat__value">{{$statistics['visual']}}</p>
                            <div class="ft-page-stat__desc">{{ getTranslateByKey('ft_pg_visual_desc') }}</div>
                        </div>
                        <div class="ft-page-stat">
                            <p class="ft-page-stat__label">{{ getTranslateByKey('ft_pg_bound_model') }}</p>
                            <p class="ft-page-stat__value">{{$statistics['bind_model']}}</p>
                            <div class="ft-page-stat__desc">{{ getTranslateByKey('ft_pg_bound_model_desc') }}</div>
                        </div>
                        <div class="ft-page-stat">
                            <p class="ft-page-stat__label">{{ getTranslateByKey('ft_pc_title') }}</p>
                            <p class="ft-page-stat__value">{{$statistics['category']}}</p>
                            <div class="ft-page-stat__desc">{{ getTranslateByKey('ft_pg_category_desc') }}</div>
                        </div>
                    </div>

                    <div class="ft-page-panel">
                        <div class="ft-page-toolbar">
                            <div>
                                <h3 class="ft-page-toolbar__title">{{ getTranslateByKey('ft_pg_list') }}</h3>
                                <p class="ft-page-toolbar__desc">{{ getTranslateByKey('ft_pg_list_desc') }}</p>
                            </div>
                            <div class="ft-page-toolbar__actions">
                                <a href="{{url('admin/formtools/pageAdd')}}" class="btn btn-info btn-sm">{{ getTranslateByKey('ft_pg_add') }}</a>
                                <a href="{{url('admin/formtools/pageCategoryList')}}" class="btn btn-default btn-sm">{{ getTranslateByKey('ft_pc_title') }}</a>
                                <a href="{{url('admin/formtools/index')}}" class="btn btn-default btn-sm">{{ getTranslateByKey('ft_pg_back_model') }}</a>
                            </div>
                        </div>

                        <div class="table-responsive ft-page-table-wrap">
                            <table class="table ft-page-table m-b-none">
                                <thead>
                                <tr>
                                    <th>{{ getTranslateByKey('ft_pg_th_info') }}</th>
                                    <th>{{ getTranslateByKey('ft_pg_th_layout') }}</th>
                                    <th>{{ getTranslateByKey('ft_pg_th_bind') }}</th>
                                    <th>{{ getTranslateByKey('ft_th_time') }}</th>
                                    <th>{{ getTranslateByKey('operation') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($pageData['datas'] as $page)
                                    <tr>
                                        <td>
                                            <div class="ft-page-main">
                                                <h4 class="ft-page-main__title">
                                                    {{$page->name}}
                                                    <span class="ft-page-code">#{{$page->id}}</span>
                                                </h4>
                                                <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_identification') }} <span class="ft-page-code">{{$page->identification}}</span></div>
                                                <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_path') }} <span class="ft-page-code">{{$page->getPublicPath() ?: getTranslateByKey('ft_status_unset')}}</span></div>
                                                <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_admin_preview') }} <span class="ft-page-code">{{$page->getPreviewUrl() ?: getTranslateByKey('ft_pg_after_save')}}</span></div>
                                                @if($page->remark)
                                                    <div class="ft-page-meta">{{ getTranslateByKey('ft_pc_remark_with', 'admin', ['name' => $page->remark]) }}</div>
                                                @endif
                                                <div class="ft-page-badges">
                                                    <span class="ft-page-badge ft-page-badge--primary">{{strtoupper($page->type)}}</span>
                                                    @if($page->status)
                                                        <span class="ft-page-badge ft-page-badge--success">{{ getTranslateByKey('ft_api_enabled') }}</span>
                                                    @else
                                                        <span class="ft-page-badge ft-page-badge--danger">{{ getTranslateByKey('ft_api_global_disabled') }}</span>
                                                    @endif
                                                    @if($page->is_home)
                                                        <span class="ft-page-badge ft-page-badge--warning">{{ getTranslateByKey('ft_pg_home_badge') }}</span>
                                                    @endif
                                                    @if($page->is_nav)
                                                        <span class="ft-page-badge">{{ getTranslateByKey('ft_pg_nav_visible') }}</span>
                                                    @endif
                                                @if($page->category)
                                                    <span class="ft-page-badge">{{ getTranslateByKey('ft_pg_category_with', 'admin', ['name' => $page->category->name]) }}</span>
                                                @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="ft-page-badges" style="margin-top: 0;">
                                                @if($page->builder_type === 'visual')
                                                    <span class="ft-page-badge ft-page-badge--warning">{{ getTranslateByKey('ft_pg_layout_json') }}</span>
                                                @else
                                                    <span class="ft-page-badge ft-page-badge--primary">{{ getTranslateByKey('ft_pg_html_code') }}</span>
                                                @endif
                                                <span class="ft-page-badge">{{ getTranslateByKey('ft_pg_template_with', 'admin', ['name' => $page->template ?: 'default']) }}</span>
                                            </div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_layout_schema') }}{{trim((string) $page->layout_schema) !== '' ? getTranslateByKey('ft_pg_configured') : getTranslateByKey('ft_pg_not_configured')}}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_html_schema') }}{{trim((string) $page->page_html) !== '' ? getTranslateByKey('ft_pg_configured') : getTranslateByKey('ft_pg_not_configured')}}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_css') }}{{trim((string) $page->custom_css) !== '' ? getTranslateByKey('ft_pg_configured') : getTranslateByKey('ft_pg_not_configured')}}{{ getTranslateByKey('ft_pg_js') }}{{trim((string) $page->custom_js) !== '' ? getTranslateByKey('ft_pg_configured') : getTranslateByKey('ft_pg_not_configured')}}</div>
                                        </td>
                                        <td>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_bound_model_with', 'admin', ['name' => $page->model->name ?? getTranslateByKey('ft_pg_not_bound')]) }}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_sort_with', 'admin', ['name' => $page->sort]) }}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_home_take') }}{{ $page->is_home ? getTranslateByKey('ft_yes') : getTranslateByKey('ft_no') }}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_pg_seo_title_with', 'admin', ['name' => $page->seo_title ?: getTranslateByKey('ft_status_unset')]) }}</div>
                                        </td>
                                        <td>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_created_at_with', 'admin', ['name' => $page->created_at ?: '-']) }}</div>
                                            <div class="ft-page-meta">{{ getTranslateByKey('ft_updated_at_with', 'admin', ['name' => $page->updated_at ?: '-']) }}</div>
                                        </td>
                                        <td>
                                            <div class="ft-page-actions">
                                                @php($menuAddQuery = http_build_query(array_filter([
                                                    'm' => request()->query('m'),
                                                    'name' => $page->name,
                                                    'url' => $page->getPublicPath(),
                                                    'menu_type' => 'page',
                                                    'source_module' => 'Formtools',
                                                    'source_value' => 'page:' . $page->identification,
                                                ], function ($value) {
                                                    return !is_null($value) && $value !== '';
                                                })))
                                                <div class="ft-page-action-row">
                                                    <span class="ft-page-action-label">{{ getTranslateByKey('common_edit') }}</span>
                                                    <a href="{{url('admin/formtools/pageEdit?id='.$page->id)}}" class="btn btn-success btn-xs">{{ getTranslateByKey('ft_pg_config') }}</a>
                                                    <a href="{{url('admin/formtools/pageCopy?id='.$page->id)}}" class="btn btn-primary btn-xs">{{ getTranslateByKey('ft_pg_copy') }}</a>
                                                    <a href="{{url('admin/theme/themeMenuAdd'.($menuAddQuery ? ('?' . $menuAddQuery) : ''))}}" class="btn btn-default btn-xs">{{ getTranslateByKey('ft_pg_add_menu') }}</a>
                                                </div>
                                                <div class="ft-page-action-row">
                                                    <span class="ft-page-action-label">{{ getTranslateByKey('ft_pg_home_badge') }}</span>
                                                    @if($page->is_home)
                                                        <a href="{{url('admin/formtools/pageSetHome?id='.$page->id.'&is_home=0')}}" class="btn btn-warning btn-xs">{{ getTranslateByKey('ft_pg_cancel_home') }}</a>
                                                    @elseif($page->status)
                                                        <a href="{{url('admin/formtools/pageSetHome?id='.$page->id.'&is_home=1')}}" class="btn btn-warning btn-xs">{{ getTranslateByKey('ft_pg_set_home') }}</a>
                                                    @else
                                                        <span class="btn btn-default btn-xs disabled">{{ getTranslateByKey('ft_pg_disabled_no_home') }}</span>
                                                    @endif
                                                </div>
                                                <div class="ft-page-action-row">
                                                    <span class="ft-page-action-label">{{ getTranslateByKey('ft_pg_preview_label') }}</span>
                                                    @if($page->getPreviewUrl())
                                                        <a href="{{$page->getPreviewUrl()}}" target="_blank" class="btn btn-info btn-xs">{{ getTranslateByKey('ft_pg_admin_preview') }}</a>
                                                    @endif
                                                    @if($page->getPublicUrl())
                                                        <a href="{{$page->getPublicUrl()}}" target="_blank" class="btn btn-default btn-xs">{{ getTranslateByKey('ft_pg_public_url') }}</a>
                                                    @endif
                                                </div>
                                                <div class="ft-page-action-row">
                                                    <span class="ft-page-action-label">{{ getTranslateByKey('ft_action_risk') }}</span>
                                                    <a href="#" onclick="del('{{url('admin/formtools/pageDelete?id='.$page->id)}}');return false;" class="btn btn-danger btn-xs">{{ getTranslateByKey('common_delete') }}</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="ft-page-empty">{{ getTranslateByKey('ft_pg_empty') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 text-right text-center-xs ft-page-pagination">
                    <?php
                        $qs = $_GET ?: [];
                        unset($qs['page']);
                    ?>
                    {{ $pageData['datas']->appends($qs)->links($moduleViewName.'::admin.public.pagination',["data"=>$pageData['datas']]) }}
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>
@include(moduleAdminTemplate($moduleName)."public.js")
</body>
