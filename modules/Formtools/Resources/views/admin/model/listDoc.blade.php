@include(moduleAdminTemplate($moduleName)."public.header")
@php
    $moduleName = strtolower((string) $moduleName);
    $treeData = $pageData['datas'] ?? [];
    $labelColumn = $pageData['labelColumn'] ?? 'title';
    $selectedId = (int) ($pageData['selectedId'] ?? 0);
    $selectedRow = $pageData['selectedRow'] ?? null;
    $selectedDetail = $pageData['selectedDetail'] ?? [];
    $selectedBreadcrumb = $pageData['selectedBreadcrumb'] ?? [];
    $selectedTitle = $pageData['selectedTitle'] ?? '';
    $hasPid = !empty($pageData['tableColumns']) && in_array('pid', $pageData['tableColumns'], true);
    $hasPrice = !empty($pageData['tableColumns']) && in_array('price', $pageData['tableColumns'], true);
    $hasStatus = !empty($pageData['tableColumns']) && in_array('status', $pageData['tableColumns'], true);
    $treeCount = (int) ($pageData['treeCount'] ?? 0);
    $listUrl = url("admin/" . $moduleName . "/model") . '?' . http_build_query([
        'moduleName' => $moduleName,
        'action' => 'List',
        'model' => $pageData['model'],
    ]);
    $addUrl = url("admin/" . $moduleName . "/model") . '?' . http_build_query([
        'moduleName' => $moduleName,
        'action' => 'Add',
        'model' => $pageData['model'],
    ]);
    $statusMeta = function ($value) {
        return match ((string) $value) {
            '1' => ['label' => getTranslateByKey('ft_doc_published'), 'class' => 'ft-doc-badge ft-doc-badge--approve'],
            '0' => ['label' => getTranslateByKey('ft_stat_pending'), 'class' => 'ft-doc-badge ft-doc-badge--pending'],
            '2' => ['label' => getTranslateByKey('ft_doc_offline'), 'class' => 'ft-doc-badge ft-doc-badge--offline'],
            default => ['label' => getTranslateByKey('ft_status_unset'), 'class' => 'ft-doc-badge ft-doc-badge--unknown'],
        };
    };
@endphp
<style>
    .ft-doc-workspace {
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 16px;
        min-height: calc(100vh - 220px);
    }

    .ft-doc-side {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
    }

    .ft-doc-side__head {
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .ft-doc-side__title {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-doc-side__desc {
        margin: 4px 0 0;
        font-size: 12px;
        color: #64748b;
    }

    .ft-doc-side__search {
        position: relative;
        margin-top: 10px;
    }

    .ft-doc-side__search input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        font-size: 13px;
        transition: all 0.15s ease;
    }

    .ft-doc-side__search input:focus {
        outline: none;
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .ft-doc-side__search::before {
        content: "\e090";
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-family: 'icomoon';
        color: #94a3b8;
        font-size: 14px;
    }

    .ft-doc-tree {
        flex: 1;
        padding: 8px 6px 12px;
        overflow: auto;
        max-height: calc(100vh - 320px);
    }

    .ft-doc-node {
        position: relative;
    }

    .ft-doc-node__row {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        margin: 1px 0;
        border-radius: 8px;
        cursor: pointer;
        color: #1e293b;
        font-size: 13px;
        line-height: 1.4;
        transition: background 0.12s ease;
    }

    .ft-doc-node__row:hover {
        background: #f1f5f9;
    }

    .ft-doc-node__row.is-active {
        background: #eef2ff;
        color: #1d4ed8;
        font-weight: 600;
    }

    .ft-doc-node__caret {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        font-size: 11px;
        color: #94a3b8;
        transition: transform 0.15s ease;
        flex-shrink: 0;
    }

    .ft-doc-node.is-expanded > .ft-doc-node__row .ft-doc-node__caret {
        transform: rotate(90deg);
    }

    .ft-doc-node.is-leaf > .ft-doc-node__row .ft-doc-node__caret {
        visibility: hidden;
    }

    .ft-doc-node__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        color: #6366f1;
        flex-shrink: 0;
    }

    .ft-doc-node__title {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ft-doc-node__price {
        display: inline-block;
        margin-left: 4px;
        padding: 1px 6px;
        border-radius: 999px;
        background: #fef3c7;
        color: #b45309;
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .ft-doc-node__price--free {
        background: #dcfce7;
        color: #166534;
    }

    .ft-doc-node__status {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .ft-doc-node__status--approve { background: #22c55e; }
    .ft-doc-node__status--pending { background: #f59e0b; }
    .ft-doc-node__status--offline { background: #ef4444; }
    .ft-doc-node__status--unknown { background: #cbd5e1; }

    .ft-doc-node__children {
        display: none;
        margin: 0;
        padding: 0 0 0 16px;
        list-style: none;
    }

    .ft-doc-node.is-expanded > .ft-doc-node__children {
        display: block;
    }

    .ft-doc-side__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        border-top: 1px solid #edf2f7;
        background: #f8fafc;
        font-size: 12px;
        color: #64748b;
    }

    .ft-doc-main {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
    }

    .ft-doc-main__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .ft-doc-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
        color: #64748b;
    }

    .ft-doc-breadcrumb a {
        color: #6366f1;
        text-decoration: none;
    }

    .ft-doc-breadcrumb a:hover {
        text-decoration: underline;
    }

    .ft-doc-breadcrumb__sep {
        color: #cbd5e1;
    }

    .ft-doc-main__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ft-doc-main__actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .ft-doc-detail {
        flex: 1;
        padding: 22px 28px;
        overflow: auto;
        max-height: calc(100vh - 240px);
    }

    .ft-doc-detail__title {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
    }

    .ft-doc-detail__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0 0 20px;
        font-size: 12px;
        color: #64748b;
    }

    .ft-doc-detail__meta .ft-doc-badge {
        margin-right: 0;
    }

    .ft-doc-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-doc-badge--approve { background: #dcfce7; color: #166534; }
    .ft-doc-badge--pending { background: #fef3c7; color: #92400e; }
    .ft-doc-badge--offline { background: #fee2e2; color: #991b1b; }
    .ft-doc-badge--unknown { background: #e2e8f0; color: #475569; }
    .ft-doc-badge--price { background: #ede9fe; color: #6d28d9; }
    .ft-doc-badge--free { background: #dcfce7; color: #166534; }

    .ft-doc-detail__price-card {
        display: inline-flex;
        align-items: baseline;
        gap: 6px;
        padding: 8px 14px;
        margin-bottom: 20px;
        border-radius: 12px;
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
        color: #4338ca;
        font-size: 14px;
    }

    .ft-doc-detail__price-card strong {
        font-size: 22px;
        font-weight: 800;
    }

    .ft-doc-detail__section {
        margin-bottom: 22px;
    }

    .ft-doc-detail__section-title {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .ft-doc-field {
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr);
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px dashed #edf2f7;
    }

    .ft-doc-field__label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
    }

    .ft-doc-field__value {
        font-size: 14px;
        color: #1e293b;
        line-height: 1.7;
        word-break: break-word;
    }

    .ft-doc-field__value--html {
        line-height: 1.7;
    }

    .ft-doc-field__value--html img {
        max-width: 100%;
        height: auto;
    }

    .ft-doc-field__value--image img {
        max-width: 240px;
        max-height: 240px;
        border-radius: 8px;
        border: 1px solid #edf2f7;
        cursor: pointer;
    }

    .ft-doc-field__value--empty {
        color: #cbd5e1;
        font-style: italic;
    }

    .ft-doc-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        color: #94a3b8;
        text-align: center;
    }

    .ft-doc-empty__icon {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .ft-doc-empty__title {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 600;
        color: #475569;
    }

    .ft-doc-empty__desc {
        margin: 0;
        font-size: 13px;
        line-height: 1.7;
    }

    .ft-doc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0 14px;
        margin-bottom: 18px;
        border-bottom: 1px solid #edf2f7;
    }

    .ft-doc-toolbar__title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-doc-toolbar__desc {
        margin: 4px 0 0;
        font-size: 13px;
        color: #64748b;
    }

    .ft-doc-toolbar__actions {
        display: flex;
        gap: 10px;
    }

    .ft-doc-toolbar__actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    @media (max-width: 1199px) {
        .ft-doc-workspace {
            grid-template-columns: 280px minmax(0, 1fr);
        }
    }

    @media (max-width: 991px) {
        .ft-doc-workspace {
            grid-template-columns: 1fr;
        }
        .ft-doc-side {
            max-height: 360px;
        }
        .ft-doc-tree {
            max-height: 240px;
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
                <div class="ft-doc-toolbar">
                    <div>
                        <h2 class="ft-doc-toolbar__title">{{$pageData['title']}} · {{ getTranslateByKey('ft_doc_workbench') }}</h2>
                        <p class="ft-doc-toolbar__desc">{{ getTranslateByKey('ft_doc_toolbar_desc', 'admin', ['count' => $treeCount]) }}</p>
                    </div>
                    <div class="ft-doc-toolbar__actions">
                        <a href="{{ $listUrl }}&_view=table" class="btn btn-default btn-sm">{{ getTranslateByKey('ft_doc_switch_table') }}</a>
                        <a href="{{ $listUrl }}&_view=doc" class="btn btn-default btn-sm" style="display:none;" id="ftDocRefresh">{{ getTranslateByKey('ft_doc_refresh') }}</a>
                        <a href="{{ $addUrl }}" class="btn btn-info btn-sm">{{ getTranslateByKey('ft_doc_add') }}</a>
                    </div>
                </div>
                <div class="ft-doc-workspace">
                    <aside class="ft-doc-side">
                        <div class="ft-doc-side__head">
                            <h3 class="ft-doc-side__title">{{ getTranslateByKey('ft_doc_catalog') }}</h3>
                            <p class="ft-doc-side__desc">{{ getTranslateByKey('ft_doc_catalog_desc_pre') }}{{ $labelColumn ?: 'id' }}{{ getTranslateByKey('ft_doc_catalog_desc_post') }}{{ $hasPid ? getTranslateByKey('ft_doc_catalog_desc_pid') : '' }}</p>
                            <div class="ft-doc-side__search">
                                <input type="text" id="ftDocSearch" placeholder="{{ getTranslateByKey('ft_doc_search_placeholder') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="ft-doc-tree" id="ftDocTree">
                            @if(empty($treeData))
                                <div style="padding: 24px 12px; text-align: center; color: #94a3b8; font-size: 13px;">
                                    {{ getTranslateByKey('ft_doc_empty_tree') }}<a href="{{ $addUrl }}" style="color: #6366f1;">{{ getTranslateByKey('ft_doc_create_now') }}</a>
                                </div>
                            @else
                                @include('formtools::admin.model._docTreeNode', [
                                    'nodes' => $treeData,
                                    'level' => 0,
                                    'selectedId' => $selectedId,
                                    'labelColumn' => $labelColumn,
                                    'hasPrice' => $hasPrice,
                                    'hasStatus' => $hasStatus,
                                    'statusMeta' => $statusMeta,
                                ])
                            @endif
                        </div>
                        <div class="ft-doc-side__footer">
                            <span>{{ getTranslateByKey('ft_doc_count_with', 'admin', ['count' => $treeCount]) }}</span>
                            <span>
                                <a href="javascript:void(0)" id="ftDocExpandAll" style="color: #6366f1; text-decoration: none;">{{ getTranslateByKey('ft_doc_expand_all') }}</a>
                                ·
                                <a href="javascript:void(0)" id="ftDocCollapseAll" style="color: #6366f1; text-decoration: none;">{{ getTranslateByKey('ft_doc_collapse_all') }}</a>
                            </span>
                        </div>
                    </aside>
                    <section class="ft-doc-main" id="ftDocMain">
                        <div class="ft-doc-main__head">
                            <div class="ft-doc-breadcrumb" id="ftDocBreadcrumb">
                                @if($selectedRow)
                                    <a href="{{ $listUrl }}">{{ getTranslateByKey('ft_doc_library') }}</a>
                                    @foreach($selectedBreadcrumb as $crumb)
                                        <span class="ft-doc-breadcrumb__sep">/</span>
                                        <a href="{{ $listUrl }}&id={{ $crumb['id'] }}" data-doc-id="{{ $crumb['id'] }}">{{ $crumb['title'] }}</a>
                                    @endforeach
                                    <span class="ft-doc-breadcrumb__sep">/</span>
                                    <span style="color: #0f172a; font-weight: 600;">{{ $selectedTitle }}</span>
                                @else
                                    <span style="color: #94a3b8;">{{ getTranslateByKey('ft_doc_select_hint') }}</span>
                                @endif
                            </div>
                            <div class="ft-doc-main__actions" id="ftDocActions">
                                @if($selectedRow)
                                    <a href="{{ $addUrl }}&pid={{ $selectedId }}" class="btn btn-default btn-xs">{{ getTranslateByKey('ft_doc_add_child') }}</a>
                                    @if(!empty($pageData['access_identification']))
                                        <a href="{{ model_detail_url($pageData['access_identification'], $selectedId) }}" target="_blank" class="btn btn-info btn-xs">{{ getTranslateByKey('ft_single_home_detail') }}</a>
                                    @endif
                                    <a href="{{ url("admin/".$moduleName."/model") }}?action=Edit&moduleName={{ $moduleName }}&model={{ $pageData['model'] }}&id={{ $selectedId }}" class="btn btn-success btn-xs">{{ getTranslateByKey('common_edit') }}</a>
                                    <a href="javascript:void(0)" data-del-url="{{ url("admin/".$moduleName."/model") }}?action=Del&moduleName={{ $moduleName }}&model={{ $pageData['model'] }}&id={{ $selectedId }}" data-del-title="{{ $selectedTitle }}" class="btn btn-danger btn-xs ft-doc-del">{{ getTranslateByKey('common_delete') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="ft-doc-detail" id="ftDocDetail">
                            @if($selectedRow)
                                <h1 class="ft-doc-detail__title" id="ftDocDetailTitle">{{ $selectedTitle }}</h1>
                                <div class="ft-doc-detail__meta" id="ftDocDetailMeta">
                                    @if($hasStatus)
                                        @php($meta = $statusMeta($selectedRow->status ?? ''))
                                        <span class="{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                    @endif
                                    @if($hasPrice)
                                        @if(floatval($selectedRow->price ?? 0) > 0)
                                            <span class="ft-doc-badge ft-doc-badge--price">￥{{ number_format(floatval($selectedRow->price), 2) }}</span>
                                        @else
                                            <span class="ft-doc-badge ft-doc-badge--free">{{ getTranslateByKey('ft_free') }}</span>
                                        @endif
                                    @endif
                                    @if(!empty($selectedRow->created_at))
                                        <span>{{ getTranslateByKey('ft_doc_created_at_with', 'admin', ['name' => $selectedRow->created_at]) }}</span>
                                    @endif
                                    @if(!empty($selectedRow->updated_at) && $selectedRow->updated_at !== $selectedRow->created_at)
                                        <span>{{ getTranslateByKey('ft_doc_updated_at_with', 'admin', ['name' => $selectedRow->updated_at]) }}</span>
                                    @endif
                                </div>
                                @if($hasPrice && floatval($selectedRow->price ?? 0) > 0)
                                    <div class="ft-doc-detail__price-card">
                                        {{ getTranslateByKey('ft_doc_paid') }} <strong>￥{{ number_format(floatval($selectedRow->price), 2) }}</strong>
                                    </div>
                                @endif
                                <div class="ft-doc-detail__section" id="ftDocDetailFields">
                                    @if(empty($selectedDetail))
                                        <div style="padding: 20px; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 12px;">
                                            {{ getTranslateByKey('ft_doc_no_fields') }}
                                        </div>
                                    @else
                                        @foreach($selectedDetail as $field)
                                            <div class="ft-doc-field">
                                                <div class="ft-doc-field__label">{{ $field['name'] }}</div>
                                                <div class="ft-doc-field__value ft-doc-field__value--{{ $field['formtype'] === 'editor' ? 'html' : $field['display_type'] }}">
                                                    @if($field['raw'] === '' || $field['raw'] === null)
                                                        <span class="ft-doc-field__value--empty">—</span>
                                                    @elseif($field['display_type'] === 'image')
                                                        <img src="{{ GetUrlByPath($field['raw']) }}" alt="{{ $field['name'] }}" onclick="clickDocImage(this.src)">
                                                    @elseif($field['display_type'] === 'file')
                                                        <a href="{{ GetUrlByPath($field['raw']) }}" target="_blank"><i class="icon-file-download2"></i> {{ $field['name'] }}</a>
                                                    @elseif($field['formtype'] === 'editor')
                                                        {!! $field['raw'] !!}
                                                    @else
                                                        {{ $field['raw'] }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @else
                                <div class="ft-doc-empty">
                                    <i class="ft-doc-empty__icon icon-file-text2"></i>
                                    <h3 class="ft-doc-empty__title">{{ getTranslateByKey('ft_doc_empty_title') }}</h3>
                                    <p class="ft-doc-empty__desc">{{ getTranslateByKey('ft_doc_empty_desc_pre') }}<a href="{{ $addUrl }}" style="color: #6366f1;">{{ getTranslateByKey('ft_doc_create_now') }}</a>。</p>
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@include(moduleAdminTemplate($moduleName)."public.footer")
@include(moduleAdminTemplate($moduleName)."public.js")
<script>
    (function () {
        var tree = document.getElementById('ftDocTree');
        var detail = document.getElementById('ftDocDetail');
        var breadcrumb = document.getElementById('ftDocBreadcrumb');
        var actions = document.getElementById('ftDocActions');
        var searchInput = document.getElementById('ftDocSearch');
        if (!tree) return;

        var baseUrl = @json($listUrl);
        var moduleName = @json($moduleName);
        var modelName = @json($pageData['model']);
        var accessIdentification = @json($pageData['access_identification'] ?? '');

        function bindCaret(node) {
            var caret = node.querySelector(':scope > .ft-doc-node__row .ft-doc-node__caret');
            if (caret && !node.classList.contains('is-leaf')) {
                caret.addEventListener('click', function (e) {
                    e.stopPropagation();
                    node.classList.toggle('is-expanded');
                });
            }
        }

        function bindRow(node) {
            var row = node.querySelector(':scope > .ft-doc-node__row');
            if (!row) return;
            row.addEventListener('click', function (e) {
                if (e.target.closest('.ft-doc-node__caret')) return;
                var id = row.getAttribute('data-doc-id');
                if (id) {
                    loadDocDetail(id);
                }
            });
        }

        document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (node) {
            if (node.querySelector(':scope > .ft-doc-node__children')) {
                bindCaret(node);
            } else {
                node.classList.add('is-leaf');
            }
            bindRow(node);
        });

        document.querySelectorAll('#ftDocBreadcrumb a[data-doc-id]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                loadDocDetail(a.getAttribute('data-doc-id'));
            });
        });

        var delBtn = document.querySelector('.ft-doc-del');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                var url = delBtn.getAttribute('data-del-url');
                var title = delBtn.getAttribute('data-del-title');
                layer.confirm('{{ getTranslateByKey('ft_doc_del_confirm', 'admin', ['title' => ':T:']) }}'.replace(':T:', title), {
                    title: '{{ getTranslateByKey('ft_doc_del_tip') }}',
                    btn: ['{{ getTranslateByKey('common_ensure') }}', '{{ getTranslateByKey('common_cancel') }}']
                }, function () {
                    window.location.href = url;
                });
            });
        }

        var expandAll = document.getElementById('ftDocExpandAll');
        var collapseAll = document.getElementById('ftDocCollapseAll');
        if (expandAll) {
            expandAll.addEventListener('click', function () {
                document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (n) {
                    if (!n.classList.contains('is-leaf')) n.classList.add('is-expanded');
                });
            });
        }
        if (collapseAll) {
            collapseAll.addEventListener('click', function () {
                document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (n) {
                    n.classList.remove('is-expanded');
                });
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var q = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (node) {
                    var titleEl = node.querySelector(':scope > .ft-doc-node__row .ft-doc-node__title');
                    if (!titleEl) return;
                    var text = titleEl.textContent.toLowerCase();
                    if (q === '' || text.indexOf(q) !== -1) {
                        node.style.display = '';
                    } else {
                        node.style.display = 'none';
                    }
                });
                if (q !== '') {
                    document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (node) {
                        if (!node.classList.contains('is-leaf')) node.classList.add('is-expanded');
                    });
                }
            });
        }

        function escapeHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getUrlByPath(p) {
            if (typeof window.GetUrlByPath === 'function') return window.GetUrlByPath(p);
            return p;
        }

        window.clickDocImage = function (src) {
            if (typeof layer === 'undefined') {
                window.open(src);
                return;
            }
            layer.open({
                type: 1,
                title: false,
                skin: 'layui-layer-demo',
                closeBtn: 0,
                shadeClose: true,
                content: "<img style='max-width:90vw;max-height:90vh;' src='" + src + "'>"
            });
        };

        function renderFieldsHtml(fields) {
            if (!fields || !fields.length) {
                return '<div style="padding: 20px; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 12px;">' + '{{ getTranslateByKey('ft_doc_no_fields') }}' + '</div>';
            }
            var html = '';
            fields.forEach(function (f) {
                var raw = f.raw == null ? '' : String(f.raw);
                var inner = '';
                if (raw === '') {
                    inner = '<span class="ft-doc-field__value--empty">—</span>';
                } else if (f.display_type === 'image') {
                    inner = '<img src="' + escapeHtml(getUrlByPath(raw)) + '" alt="' + escapeHtml(f.name) + '" onclick="clickDocImage(this.src)">';
                } else if (f.display_type === 'file') {
                    inner = '<a href="' + escapeHtml(getUrlByPath(raw)) + '" target="_blank"><i class="icon-file-download2"></i> ' + escapeHtml(f.name) + '</a>';
                } else if (f.formtype === 'editor') {
                    inner = raw;
                } else {
                    inner = escapeHtml(raw);
                }
                var valueClass = f.formtype === 'editor'
                    ? 'ft-doc-field__value--html'
                    : 'ft-doc-field__value--' + (f.display_type || 'text');
                html += '<div class="ft-doc-field">'
                    + '<div class="ft-doc-field__label">' + escapeHtml(f.name) + '</div>'
                    + '<div class="' + valueClass + '">' + inner + '</div>'
                    + '</div>';
            });
            return html;
        }

        function renderBreadcrumbHtml(crumbs, currentTitle) {
            var html = '<a href="' + escapeHtml(baseUrl) + '">' + '{{ getTranslateByKey('ft_doc_library') }}' + '</a>';
            (crumbs || []).forEach(function (c) {
                html += '<span class="ft-doc-breadcrumb__sep">/</span>'
                    + '<a href="' + escapeHtml(baseUrl) + '&id=' + encodeURIComponent(c.id) + '" data-doc-id="' + escapeHtml(c.id) + '">' + escapeHtml(c.title) + '</a>';
            });
            html += '<span class="ft-doc-breadcrumb__sep">/</span>'
                + '<span style="color: #0f172a; font-weight: 600;">' + escapeHtml(currentTitle) + '</span>';
            return html;
        }

        function renderActionsHtml(id, title) {
            var addChildUrl = @json($addUrl) + '&pid=' + encodeURIComponent(id);
            var editUrl = @json(url("admin/".$moduleName."/model")) + '?action=Edit&moduleName=' + encodeURIComponent(moduleName) + '&model=' + encodeURIComponent(modelName) + '&id=' + encodeURIComponent(id);
            var delUrl = @json(url("admin/".$moduleName."/model")) + '?action=Del&moduleName=' + encodeURIComponent(moduleName) + '&model=' + encodeURIComponent(modelName) + '&id=' + encodeURIComponent(id);
            var detailUrl = accessIdentification ? ('/' + accessIdentification + '/detail/' + id + '.html') : '';
            var html = '<a href="' + escapeHtml(addChildUrl) + '" class="btn btn-default btn-xs">' + '{{ getTranslateByKey('ft_doc_add_child') }}' + '</a>';
            if (detailUrl) {
                html += '<a href="' + escapeHtml(detailUrl) + '" target="_blank" class="btn btn-info btn-xs">' + '{{ getTranslateByKey('ft_single_home_detail') }}' + '</a>';
            }
            html += '<a href="' + escapeHtml(editUrl) + '" class="btn btn-success btn-xs">' + '{{ getTranslateByKey('common_edit') }}' + '</a>';
            html += '<a href="javascript:void(0)" data-del-url="' + escapeHtml(delUrl) + '" data-del-title="' + escapeHtml(title) + '" class="btn btn-danger btn-xs ft-doc-del">' + '{{ getTranslateByKey('common_delete') }}' + '</a>';
            return html;
        }

        function renderDetailHtml(data) {
            var hasPrice = data.price > 0;
            var metaHtml = '';
            var statusMap = {
                '1': { label: '{{ getTranslateByKey('ft_doc_published') }}', cls: 'ft-doc-badge--approve' },
                '0': { label: '{{ getTranslateByKey('ft_stat_pending') }}', cls: 'ft-doc-badge--pending' },
                '2': { label: '{{ getTranslateByKey('ft_doc_offline') }}', cls: 'ft-doc-badge--offline' }
            };
            var sm = statusMap[data.status] || { label: '{{ getTranslateByKey('ft_status_unset') }}', cls: 'ft-doc-badge--unknown' };
            metaHtml += '<span class="ft-doc-badge ' + sm.cls + '">' + sm.label + '</span>';
            if (hasPrice) {
                metaHtml += '<span class="ft-doc-badge ft-doc-badge--price">￥' + Number(data.price).toFixed(2) + '</span>';
            } else {
                metaHtml += '<span class="ft-doc-badge ft-doc-badge--free">' + '{{ getTranslateByKey('ft_free') }}' + '</span>';
            }
            if (data.created_at) {
                metaHtml += '<span>' + '{{ getTranslateByKey('ft_doc_created_at_prefix') }}' + escapeHtml(data.created_at) + '</span>';
            }
            if (data.updated_at && data.updated_at !== data.created_at) {
                metaHtml += '<span>' + '{{ getTranslateByKey('ft_doc_updated_at_prefix') }}' + escapeHtml(data.updated_at) + '</span>';
            }
            var priceCard = hasPrice
                ? '<div class="ft-doc-detail__price-card">' + '{{ getTranslateByKey('ft_doc_paid') }}' + ' <strong>￥' + Number(data.price).toFixed(2) + '</strong></div>'
                : '';
            return ''
                + '<h1 class="ft-doc-detail__title" id="ftDocDetailTitle">' + escapeHtml(data.title) + '</h1>'
                + '<div class="ft-doc-detail__meta">' + metaHtml + '</div>'
                + priceCard
                + '<div class="ft-doc-detail__section" id="ftDocDetailFields">' + renderFieldsHtml(data.fields) + '</div>';
        }

        function loadDocDetail(id) {
            if (!id) return;
            document.querySelectorAll('#ftDocTree .ft-doc-node__row.is-active').forEach(function (r) {
                r.classList.remove('is-active');
            });
            var activeRow = document.querySelector('#ftDocTree .ft-doc-node__row[data-doc-id="' + id + '"]');
            if (activeRow) {
                activeRow.classList.add('is-active');
                var parent = activeRow.parentElement;
                while (parent && parent.id !== 'ftDocTree') {
                    if (parent.classList && parent.classList.contains('ft-doc-node')) {
                        parent.classList.add('is-expanded');
                    }
                    parent = parent.parentElement;
                }
            }
            var url = @json(url("admin/".$moduleName."/model")) + '?action=DocDetail&moduleName=' + encodeURIComponent(moduleName) + '&model=' + encodeURIComponent(modelName) + '&id=' + encodeURIComponent(id);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (resp) {
                    if (resp && resp.code === 200 && resp.data) {
                        detail.innerHTML = renderDetailHtml(resp.data);
                        breadcrumb.innerHTML = renderBreadcrumbHtml(resp.data.breadcrumb, resp.data.title);
                        actions.innerHTML = renderActionsHtml(resp.data.id, resp.data.title);
                        bindDelBtn();
                        bindBreadcrumbLinks();
                        if (history.replaceState) {
                            var newUrl = baseUrl + '&id=' + encodeURIComponent(id);
                            history.replaceState(null, '', newUrl);
                        }
                    } else {
                        layer.msg(resp && resp.msg ? resp.msg : '{{ getTranslateByKey('ft_doc_load_failed') }}');
                    }
                })
                .catch(function () {
                    layer.msg('{{ getTranslateByKey('ft_doc_network_error') }}');
                });
        }

        function bindDelBtn() {
            var btn = document.querySelector('.ft-doc-del');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-del-url');
                var title = btn.getAttribute('data-del-title');
                layer.confirm('{{ getTranslateByKey('ft_doc_del_confirm', 'admin', ['title' => ':T:']) }}'.replace(':T:', title), {
                    title: '{{ getTranslateByKey('ft_doc_del_tip') }}',
                    btn: ['{{ getTranslateByKey('common_ensure') }}', '{{ getTranslateByKey('common_cancel') }}']
                }, function () {
                    window.location.href = url;
                });
            });
        }

        function bindBreadcrumbLinks() {
            document.querySelectorAll('#ftDocBreadcrumb a[data-doc-id]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    loadDocDetail(a.getAttribute('data-doc-id'));
                });
            });
        }

        bindDelBtn();
        bindBreadcrumbLinks();
    })();
</script>
</body>
</html>
