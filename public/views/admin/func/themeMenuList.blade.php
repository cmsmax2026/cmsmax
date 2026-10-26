@include("admin.public.header")
<link rel="stylesheet" href="{{url('assets/layui/css/layui.css')}}">
@php($currentTheme = trim((string) ($_GET['m'] ?? 'default')) ?: 'default')
<style>
    .mx-menu-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 20px 22px;
        border-radius: 20px;
        border: 1px solid #e8edf5;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 48%, #eef6ff 100%);
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.05);
    }

    .mx-menu-hero__title {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 24px;
        font-weight: 700;
    }

    .mx-menu-hero__desc {
        margin: 0;
        max-width: 760px;
        color: #475569;
        line-height: 1.75;
    }

    .mx-menu-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-start;
    }

    .mx-menu-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #d7e4f5;
        background: #fff;
        color: #1e293b;
        font-size: 12px;
        font-weight: 600;
    }

    .mx-menu-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .mx-menu-toolbar__left,
    .mx-menu-toolbar__right,
    .mx-menu-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .mx-menu-stat-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .mx-menu-stat {
        padding: 16px 18px;
        border-radius: 16px;
        border: 1px solid #e8edf5;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .mx-menu-stat__label {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .mx-menu-stat__value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .mx-menu-panel {
        border-radius: 18px;
        border: 1px solid #e8edf5;
        background: #fff;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .mx-menu-panel .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .mx-menu-panel .card-body {
        padding: 18px;
        overflow-x: hidden;
    }

    .mx-menu-filter-input,
    .mx-menu-filter-select {
        min-width: 160px;
        height: 36px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        padding: 0 12px;
        background: #fff;
    }

    .mx-menu-filter-input {
        min-width: 220px;
    }

    .mx-menu-table-wrap {
        border: 1px solid #edf2f7;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .mx-menu-summary {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 4px 0;
    }

    .mx-menu-summary__title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.5;
    }

    .mx-menu-summary__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mx-menu-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eef4ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 600;
    }

    .mx-menu-badge--success {
        background: #ecfdf5;
        color: #16a34a;
    }

    .mx-menu-badge--warn {
        background: #fffbeb;
        color: #d97706;
    }

    .mx-menu-badge--gray {
        background: #f1f5f9;
        color: #475569;
    }

    .mx-menu-link-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 4px 0;
        word-break: break-all;
    }

    .mx-menu-link-block__item {
        font-size: 13px;
        line-height: 1.7;
        color: #475569;
    }

    .mx-menu-link-block__label {
        display: inline-block;
        min-width: 54px;
        color: #94a3b8;
    }

    .mx-menu-icon-preview {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0f172a;
    }

    .mx-menu-sort-group,
    .mx-menu-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .mx-menu-note {
        margin-top: 12px;
        color: #64748b;
        font-size: 12px;
    }

    .mx-menu-filter-hint {
        margin-top: 10px;
        color: #64748b;
        font-size: 12px;
    }

    #auth-table + .layui-table-view .layui-table-cell {
        height: auto;
        line-height: 1.6;
        white-space: normal;
    }

    #auth-table + .layui-table-view .layui-table-body {
        overflow-x: hidden !important;
    }

    @media (max-width: 1199px) {
        .mx-menu-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .mx-menu-stat-grid {
            grid-template-columns: 1fr;
        }

        .mx-menu-toolbar,
        .mx-menu-toolbar__left,
        .mx-menu-toolbar__right,
        .mx-menu-filters {
            align-items: stretch;
        }

        .mx-menu-filter-input,
        .mx-menu-filter-select {
            width: 100%;
            min-width: 0;
        }
    }
</style>
<body class="horizontal">

@include("admin.public.themeMenuNav")

<div class="row page-header" style="margin-bottom: -15px;">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url("admin/index")}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item"><a href="{{url("admin/theme")}}">{{getTranslateByKey("theme_list")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("menu_management")}}</li>
        </ol>
    </div>
</div>

<section class="main-content mt-20" style="padding-bottom: 0px">
    <div class="row pb-5">
        <div class="col-md-12">
            <div class="mx-menu-hero">
                <div>
                    <h2 class="mx-menu-hero__title">{{getTranslateByKey('main_theme_menu_workbench')}}</h2>
                    <p class="mx-menu-hero__desc">{{getTranslateByKey('main_menu_workbench_desc', 'admin', ['theme' => $currentTheme])}}</p>
                </div>
                <div class="mx-menu-hero__meta">
                    <span class="mx-menu-chip"><i class="fa fa-paint-brush"></i> {{getTranslateByKey('main_current_theme')}}{{$currentTheme}}</span>
                    <span class="mx-menu-chip"><i class="fa fa-sitemap"></i> {{getTranslateByKey('main_menu_tree_overview')}}</span>
                    <a href="{{url('admin/theme/themeMenuAdd?m='.$_GET['m'])}}" class="btn btn-primary btn-sm">{{getTranslateByKey('main_add_menu')}}</a>
                </div>
            </div>
            <div class="card mx-menu-panel">
                <div class="card-header card-default">
                    <div class="btn-group float-left">{{getTranslateByKey("navigation_menu")}}</div>
                    <div class="btn-group float-right">
                        <button type="button" class="btn btn-default btn-sm mr-2" id="btn-expand">{{getTranslateByKey('main_expand_all')}}</button>
                        <button type="button" class="btn btn-default btn-sm mr-2" id="btn-fold">{{getTranslateByKey('main_fold_all')}}</button>
                        <a href="{{url('admin/theme/themeMenuAdd?m='.$_GET['m'])}}" class="btn btn-default btn-sm">
                            <em class="fa fa-plus"></em> {{getTranslateByKey('main_add_menu')}}
                        </a>
                    </div>
                </div>
                <div class="card-body" style="min-height: calc(100vh - 230px)">
                    <div class="mx-menu-stat-grid">
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('main_menu_total_count')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatTotal">0</div>
                        </div>
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('top_menu')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatTop">0</div>
                        </div>
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('bottom_menu')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatBottom">0</div>
                        </div>
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('footer_menu')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatFooter">0</div>
                        </div>
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('main_menu_enabled_count')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatEnabled">0</div>
                        </div>
                        <div class="mx-menu-stat">
                            <div class="mx-menu-stat__label">{{getTranslateByKey('main_menu_auto_count')}}</div>
                            <div class="mx-menu-stat__value" id="menuStatAuto">0</div>
                        </div>
                    </div>

                    <div class="mx-menu-toolbar">
                        <div class="mx-menu-toolbar__left">
                            <div class="mx-menu-filters">
                                <input type="text" id="menuKeyword" class="mx-menu-filter-input" placeholder="{{getTranslateByKey('main_menu_search_placeholder')}}">
                                <select id="menuPositionFilter" class="mx-menu-filter-select">
                                    <option value="">{{getTranslateByKey('main_all_positions')}}</option>
                                    <option value="top">{{getTranslateByKey('main_filter_top')}}</option>
                                    <option value="bottom">{{getTranslateByKey('main_filter_bottom')}}</option>
                                    <option value="footer">{{getTranslateByKey('main_filter_footer')}}</option>
                                </select>
                                <select id="menuTypeFilter" class="mx-menu-filter-select">
                                    <option value="">{{getTranslateByKey('main_all_sources')}}</option>
                                    <option value="manual">{{getTranslateByKey('main_manual_menu')}}</option>
                                    <option value="module">{{getTranslateByKey('module_menu')}}</option>
                                    <option value="model">{{getTranslateByKey('model_menu')}}</option>
                                    <option value="page">{{getTranslateByKey('main_page_menu')}}</option>
                                    <option value="search">{{getTranslateByKey('search_menu')}}</option>
                                </select>
                                <select id="menuLangFilter" class="mx-menu-filter-select">
                                    <option value="">{{getTranslateByKey('main_all_lang_scopes')}}</option>
                                    @foreach($langList as $langCode => $langName)
                                        <option value="{{$langCode}}">{{$langName}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mx-menu-toolbar__right">
                            <button type="button" class="btn btn-default btn-sm" id="clearMenuFilter">{{getTranslateByKey('main_clear_filter')}}</button>
                        </div>
                    </div>
                    <div class="mx-menu-filter-hint" id="menuFilterHint">{{getTranslateByKey('main_showing_all_menus')}}</div>

                    <div class="mx-menu-table-wrap">
                        <table id="auth-table" class="layui-table" lay-filter="auth-table"></table>
                    </div>
                    <div class="mx-menu-note">{{getTranslateByKey('main_menu_list_note')}}</div>
                </div>

            </div>
        </div>
    </div>
    @include('admin.public.footer')
</section>

@include('admin.public.js',['load'=> ["custom"]])
<script src="{{url('assets/layui/layui.js')}}"></script>
<script>
    const themeMenuListLang = {
        menuName: @json(getTranslateByKey('menu_name')),
        menuUrl: @json(getTranslateByKey('menu_url')),
        jumpUrl: @json(getTranslateByKey('jump_url')),
        position: @json(getTranslateByKey('position')),
        top: @json(getTranslateByKey('top_section')),
        bottom: @json(getTranslateByKey('bottom_section')),
        footer: @json(getTranslateByKey('footer_section')),
        iconText: @json(getTranslateByKey('icon_text')),
        menuType: @json(getTranslateByKey('main_source')),
        target: @json(getTranslateByKey('main_open_method')),
        currentWindow: @json(getTranslateByKey('main_current_window')),
        newWindow: @json(getTranslateByKey('main_new_window')),
        sourceManual: @json(getTranslateByKey('main_manual_menu')),
        sourceModule: @json(getTranslateByKey('module_menu')),
        sourceModel: @json(getTranslateByKey('model_menu')),
        sourceSearch: @json(getTranslateByKey('search_menu')),
        sourcePage: @json(getTranslateByKey('main_page_menu')),
        sourceModuleLabel: @json(getTranslateByKey('main_source_module')),
        sourceValueLabel: @json(getTranslateByKey('main_source_value')),
        sort: @json(getTranslateByKey('sort_desc')),
        sortAction: @json(getTranslateByKey('main_sort_action')),
        sortTop: @json(getTranslateByKey('main_top')),
        sortUp: @json(getTranslateByKey('main_move_up')),
        sortDown: @json(getTranslateByKey('main_move_down')),
        status: @json(getTranslateByKey('common_account_status')),
        enable: @json(getTranslateByKey('common_enable')),
        disable: @json(getTranslateByKey('common_disable')),
        action: @json(getTranslateByKey('operation')),
        edit: @json(getTranslateByKey('common_edit')),
        del: @json(getTranslateByKey('common_delete')),
        sureEnable: @json(getTranslateByKey('common_sure_to_enabling')),
        sureDisable: @json(getTranslateByKey('common_sure_to_forbidden')),
        sortUpdated: @json(getTranslateByKey('main_sort_updated')),
        source: @json(getTranslateByKey('main_source')),
        jumpMode: @json(getTranslateByKey('main_open_method')),
        langScopeGlobal: @json(getTranslateByKey('main_lang_scope_global')),
        noIconSet: @json(getTranslateByKey('main_no_icon_set')),
        jump: @json(getTranslateByKey('main_jump')),
        keyword: @json(getTranslateByKey('main_keyword')),
        filterHitPrefix: @json(getTranslateByKey('main_filter_hit_prefix')),
        filterHitSuffix: @json(getTranslateByKey('main_filter_hit_suffix')),
        showingAllMenus: @json(getTranslateByKey('main_showing_all_menus'))
    };
    const themeMenuLangMap = @json($langList);

    layui.config({
        base: '../../assets/layui/module/'
    }).extend({
        treetable: 'treetable-lay/treetable'
    }).use(['table', 'treetable'], function () {
        var $ = layui.jquery;
        var treetable = layui.treetable;
        let menuData = [];

        function updateStats(rows) {
            $('#menuStatTotal').text(rows.length);
            $('#menuStatTop').text(rows.filter(item => item.position === 'top').length);
            $('#menuStatBottom').text(rows.filter(item => item.position === 'bottom').length);
            $('#menuStatFooter').text(rows.filter(item => item.position === 'footer').length);
            $('#menuStatEnabled').text(rows.filter(item => String(item.status) === '1').length);
            $('#menuStatAuto').text(rows.filter(item => ['module', 'model', 'page', 'search'].includes(item.menu_type)).length);
        }

        function matchKeyword(row, keyword) {
            if (!keyword) return true;
            const text = [
                row.name,
                row.url,
                row.source_module,
                row.source_value,
                row.icon_character
            ].join(' ').toLowerCase();
            return text.indexOf(keyword) !== -1;
        }

        function buildFilteredRows() {
            const keyword = ($('#menuKeyword').val() || '').trim().toLowerCase();
            const position = $('#menuPositionFilter').val();
            const menuType = $('#menuTypeFilter').val();
            const menuLang = ($('#menuLangFilter').val() || '').toString();
            const matchedIds = new Set();

            menuData.forEach(function (row) {
                if (position && row.position !== position) return false;
                if (menuType && row.menu_type !== menuType) return false;
                if (((row.lang || '').toString()) !== menuLang && menuLang !== '') return false;
                if (!matchKeyword(row, keyword)) return false;
                matchedIds.add(row.id);
                let parentId = row.pid;
                while (parentId && parentId !== 0) {
                    matchedIds.add(parentId);
                    const parent = menuData.find(item => item.id === parentId);
                    parentId = parent ? parent.pid : 0;
                }
            });

            if (!keyword && !position && !menuType && menuLang === '') {
                return menuData;
            }

            return menuData.filter(function (row) {
                return matchedIds.has(row.id);
            });
        }

        function renderTable(rows) {
            layer.load(2);
            treetable.render({
                treeColIndex: 0,
                treeSpid: 0,
                treeIdName: 'id',
                treePidName: 'pid',
                elem: '#auth-table',
                data: rows,
                cellMinWidth: 80,
                cols: [[
                    {
                        field: 'name', minWidth: 280, title: themeMenuListLang.menuName, templet: function (d) {
                            const badges = [];
                            const langLabel = themeMenuLangMap[d.lang || ''] || d.lang || themeMenuListLang.langScopeGlobal;
                            badges.push(`<span class="mx-menu-badge mx-menu-badge--gray">${langLabel}</span>`);
                            if (d.position === 'top') badges.push(`<span class="mx-menu-badge">${themeMenuListLang.top}</span>`);
                            if (d.position === 'bottom') badges.push(`<span class="mx-menu-badge mx-menu-badge--success">${themeMenuListLang.bottom}</span>`);
                            if (d.position === 'footer') badges.push(`<span class="mx-menu-badge mx-menu-badge--warn">${themeMenuListLang.footer}</span>`);
                            if (d.menu_type === 'module') badges.push(`<span class="mx-menu-badge">${themeMenuListLang.sourceModule}</span>`);
                            if (d.menu_type === 'model') badges.push(`<span class="mx-menu-badge mx-menu-badge--success">${themeMenuListLang.sourceModel}</span>`);
                            if (d.menu_type === 'search') badges.push(`<span class="mx-menu-badge mx-menu-badge--warn">${themeMenuListLang.sourceSearch}</span>`);
                            if (d.menu_type === 'page') badges.push(`<span class="mx-menu-badge">${themeMenuListLang.sourcePage}</span>`);
                            if (d.menu_type === 'manual') badges.push(`<span class="mx-menu-badge mx-menu-badge--gray">${themeMenuListLang.sourceManual}</span>`);
                            badges.push(d.target === '_blank'
                                ? `<span class="mx-menu-badge mx-menu-badge--warn">${themeMenuListLang.newWindow}</span>`
                                : `<span class="mx-menu-badge mx-menu-badge--gray">${themeMenuListLang.currentWindow}</span>`);

                            const iconHtml = d.icon
                                ? `<span class="mx-menu-icon-preview"><i class="${d.icon}"></i>${d.icon_character ? `<span>${d.icon_character}</span>` : ''}</span>`
                                : `<span class="mx-menu-icon-preview"><i class="fa fa-circle-o"></i><span>${themeMenuListLang.noIconSet}</span></span>`;

                            return `
                                <div class="mx-menu-summary">
                                    <div class="mx-menu-summary__title">
                                        <span>${d.name || '-'}</span>
                                        ${iconHtml}
                                    </div>
                                    <div class="mx-menu-summary__meta">${badges.join('')}</div>
                                </div>
                            `;
                        }
                    },
                    {
                        field: 'url', minWidth: 360, title: @json(getTranslateByKey('main_link_info')), templet: function (d) {
                            const rawUrl = d.url || '#';
                            const jumpUrl = rawUrl && rawUrl !== '#' && !/^https?:\/\//i.test(rawUrl)
                                ? `{{url('')}}${rawUrl.charAt(0) === '/' ? '' : '/'}${rawUrl}`
                                : rawUrl;
                            return `
                                <div class="mx-menu-link-block">
                                    <div class="mx-menu-link-block__item"><span class="mx-menu-link-block__label">URL</span>${d.url || '#'}</div>
                                    <div class="mx-menu-link-block__item"><span class="mx-menu-link-block__label">${themeMenuListLang.jump}</span>${jumpUrl}</div>
                                    <div class="mx-menu-link-block__item"><span class="mx-menu-link-block__label">${themeMenuListLang.source}</span>${d.source_module || '-'} / ${d.source_value || '-'}</div>
                                </div>
                            `;
                        }
                    },
                    {
                        width: 220, title: themeMenuListLang.sortAction, templet: function (d) {
                            return `
                                <div class="mx-menu-sort-group">
                                    <span class="mx-menu-badge mx-menu-badge--gray">Sort ${d.sort || 0}</span>
                                    <a class="btn btn-xs btn-default" href="javascript:;" onclick="moveMenu(${d.id}, 'top')">${themeMenuListLang.sortTop}</a>
                                    <a class="btn btn-xs btn-default" href="javascript:;" onclick="moveMenu(${d.id}, 'up')">${themeMenuListLang.sortUp}</a>
                                    <a class="btn btn-xs btn-default" href="javascript:;" onclick="moveMenu(${d.id}, 'down')">${themeMenuListLang.sortDown}</a>
                                </div>
                            `;
                        }
                    },
                    {
                        width: 110, title: themeMenuListLang.status, templet: function (d) {
                            if (d.status == 1) {
                                return `<span class="btn btn-xs btn-success" onclick="changeStatus(${d.id},2)">${themeMenuListLang.enable}</span>`;
                            }
                            return `<span class="btn btn-xs btn-danger" onclick="changeStatus(${d.id},1)">${themeMenuListLang.disable}</span>`;
                        }
                    },
                    {
                        width: 150, align: 'center', title: themeMenuListLang.action, templet: function (d) {
                            var edit = `<a class="btn btn-xs btn-teal mr-2" href="{{url('admin/theme/themeMenuEdit?m='.$_GET['m'])}}&id=${d.id}">${themeMenuListLang.edit}</a>`;
                            var del = `<a class="btn btn-xs btn-danger" href="javascript:;" onclick="delData(${d.id})">${themeMenuListLang.del}</a>`;
                            return `<div class="mx-menu-action-group">${edit}${del}</div>`;
                        }
                    }
                ]],
                done: function () {
                    layer.closeAll('loading');
                }
            });
        }

        function renderFilteredTable() {
            const rows = buildFilteredRows();
            const position = $('#menuPositionFilter option:selected').text();
            const menuType = $('#menuTypeFilter option:selected').text();
            const menuLang = $('#menuLangFilter option:selected').text();
            const keyword = ($('#menuKeyword').val() || '').trim();
            const hintParts = [];
            if (keyword) hintParts.push(`${themeMenuListLang.keyword}${keyword}`);
            if ($('#menuPositionFilter').val()) hintParts.push(position);
            if ($('#menuTypeFilter').val()) hintParts.push(menuType);
            if ($('#menuLangFilter').val() !== '') hintParts.push(menuLang);
            $('#menuFilterHint').text(hintParts.length ? `${themeMenuListLang.filterHitPrefix}${rows.length}${themeMenuListLang.filterHitSuffix}${hintParts.join(' / ')}` : themeMenuListLang.showingAllMenus);
            renderTable(rows);
        }

        layer.load(2);
        $.get('{{url('admin/theme/themeMenuList')}}', function (res) {
            menuData = (res && res.data) ? res.data : [];
            updateStats(menuData);
            renderFilteredTable();
        });

        $('#menuKeyword').on('input', function () {
            renderFilteredTable();
        });

        $('#menuPositionFilter, #menuTypeFilter, #menuLangFilter').on('change', function () {
            renderFilteredTable();
        });

        $('#clearMenuFilter').click(function () {
            $('#menuKeyword').val('');
            $('#menuPositionFilter').val('');
            $('#menuTypeFilter').val('');
            $('#menuLangFilter').val('');
            renderFilteredTable();
        });

        $('#btn-expand').click(function () {
            treetable.expandAll('#auth-table');
        });

        $('#btn-fold').click(function () {
            treetable.foldAll('#auth-table');
        });
    });

    function moveMenu(id, direction) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            method: 'post',
            url: "{{url('admin/theme/themeMenuMove?m='.$_GET['m'])}}",
            dataType: 'json',
            data: {id: id, direction: direction},
            success: function (res) {
                if (res.status == 200) {
                    popup({type: "success", msg: res.msg || themeMenuListLang.sortUpdated, delay: 1200});
                    setTimeout(function () {
                        location.reload();
                    }, 800);
                } else {
                    popup({type: "error", msg: res.msg, delay: 1800});
                }
            },
            error: function (res) {
                console.log(res);
            }
        });
    }

    function delData(id) {
        return adminConfirmAction("{{url('admin/theme/themeMenuDelete?id=')}}" + id, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: '{{getTranslateByKey("common_sure_to_delete")}}',
            type: 'warning',
            confirmClass: 'btn-danger'
        });
    }

    function changeStatus(id, status) {
        var content = status == 1 ? themeMenuListLang.sureEnable : themeMenuListLang.sureDisable;
        return adminConfirmAction("{{url('admin/theme/themeMenuChangeStatus?id=')}}" + id + '&status=' + status, {
            title: '{{getTranslateByKey("common_tip")}}',
            content: content
        });
    }
</script>
</body>
</html>
