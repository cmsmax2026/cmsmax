@php
    $moduleName = $moduleName ?? ($pageData['moduleName'] ?? 'Formtools');
    $sceneTabs = $sceneTabs ?? [];
    $sceneMeta = $sceneMeta ?? [];
    $accessIdentification = trim((string) ($model->access_identification ?? ''));
    $homeConfig = json_decode((string) ($model->home_config ?? ''), true) ?: [];
    $homeTitle = trim((string) data_get($homeConfig, 'home_page_title', $model->name));
    $homeDescription = trim((string) data_get($homeConfig, 'home_page_describe', ''));

    $scenePresets = [
        'list' => [
            [
                'name' => getTranslateByKey('ft_lb_preset_list_1_name'),
                'desc' => getTranslateByKey('ft_lb_preset_list_1_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#2563eb',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#dbe2ea',
                        'radiusCard' => '24px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => [
                                'contentWidth' => 'contained',
                                'innerWidth' => '1200px',
                                'anchor' => 'list-hero',
                            ],
                            'style' => [
                                'padding' => '72px 0 28px',
                                'background' => '#f8fbff',
                            ],
                            'children' => [
                                [
                                    'type' => 'row',
                                    'style' => [
                                        'gap' => '28px',
                                        'alignItems' => 'center',
                                    ],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 8],
                                            'blocks' => [
                                                [
                                                    'type' => 'text',
                                                    'props' => ['text' => 'LIST LANDING'],
                                                    'style' => ['margin' => '0 0 10px', 'color' => '#2563eb', 'fontWeight' => '700', 'letterSpacing' => '0.18em'],
                                                ],
                                                [
                                                    'type' => 'heading',
                                                    'props' => ['level' => 'h1', 'text' => $model->name . getTranslateByKey('ft_lb_list_page_suffix')],
                                                    'style' => ['margin' => '0 0 16px'],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'props' => ['text' => $homeDescription !== '' ? $homeDescription : getTranslateByKey('ft_lb_preset_list_1_intro')],
                                                    'style' => ['margin' => '0 0 24px', 'lineHeight' => '1.9', 'color' => '#64748b'],
                                                ],
                                                [
                                                    'type' => 'button',
                                                    'props' => ['href' => '#list-content', 'text' => getTranslateByKey('ft_lb_btn_view_now'), 'align' => 'left', 'variant' => 'solid'],
                                                    'style' => ['display' => 'inline-flex', 'padding' => '12px 22px'],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 4],
                                            'blocks' => [
                                                [
                                                    'type' => 'stats',
                                                    'props' => [
                                                        'title' => getTranslateByKey('ft_lb_stats_title'),
                                                        'intro' => getTranslateByKey('ft_lb_stats_intro'),
                                                        'columns' => 2,
                                                        'items' => [
                                                            ['label' => getTranslateByKey('ft_lb_stats_item1_label'), 'value' => '120', 'suffix' => '+', 'description' => getTranslateByKey('ft_lb_stats_item1_desc')],
                                                            ['label' => getTranslateByKey('ft_lb_stats_item2_label'), 'value' => '08', 'suffix' => '', 'description' => getTranslateByKey('ft_lb_stats_item2_desc')],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'section',
                            'props' => [
                                'contentWidth' => 'contained',
                                'innerWidth' => '1200px',
                                'anchor' => 'list-content',
                            ],
                            'style' => [
                                'padding' => '24px 0 64px',
                            ],
                            'children' => [
                                [
                                    'type' => 'model_list',
                                    'props' => [
                                        'model' => $accessIdentification !== '' ? $accessIdentification : 'news',
                                        'limit' => 12,
                                        'template' => 'card',
                                        'title' => getTranslateByKey('ft_lb_list_title'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => getTranslateByKey('ft_lb_preset_list_2_name'),
                'desc' => getTranslateByKey('ft_lb_preset_list_2_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#0f766e',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#d1fae5',
                        'radiusCard' => '22px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1220px'],
                            'style' => ['padding' => '56px 0 64px', 'background' => '#ffffff'],
                            'children' => [
                                [
                                    'type' => 'row',
                                    'style' => ['gap' => '28px', 'alignItems' => 'start'],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 8],
                                            'blocks' => [
                                                [
                                                    'type' => 'heading',
                                                    'props' => ['level' => 'h1', 'text' => $model->name . getTranslateByKey('ft_lb_infoflow_suffix')],
                                                    'style' => ['margin' => '0 0 14px'],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'props' => ['text' => getTranslateByKey('ft_lb_preset_list_2_intro')],
                                                    'style' => ['margin' => '0 0 24px', 'lineHeight' => '1.9', 'color' => '#64748b'],
                                                ],
                                                [
                                                    'type' => 'model_list',
                                                    'props' => ['model' => $accessIdentification !== '' ? $accessIdentification : 'news', 'limit' => 10, 'template' => 'list', 'title' => getTranslateByKey('ft_lb_latest_content')],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 4],
                                            'blocks' => [
                                                [
                                                    'type' => 'cta',
                                                    'props' => [
                                                        'eyebrow' => 'SIDEBAR CTA',
                                                        'title' => getTranslateByKey('ft_lb_cta_1_title'),
                                                        'description' => getTranslateByKey('ft_lb_cta_1_desc'),
                                                        'primaryText' => getTranslateByKey('ft_lb_cta_contact'),
                                                        'primaryHref' => '/contact',
                                                        'secondaryText' => getTranslateByKey('ft_lb_cta_get_materials'),
                                                        'secondaryHref' => '#',
                                                        'align' => 'left',
                                                    ],
                                                ],
                                                [
                                                    'type' => 'qrcode',
                                                    'props' => [
                                                        'title' => getTranslateByKey('ft_lb_qr_1_title'),
                                                        'text' => getTranslateByKey('ft_lb_qr_1_text'),
                                                        'value' => 'https://example.com/contact',
                                                        'size' => '132',
                                                    ],
                                                    'style' => ['margin' => '18px 0 0'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'detail' => [
            [
                'name' => getTranslateByKey('ft_lb_preset_detail_1_name'),
                'desc' => getTranslateByKey('ft_lb_preset_detail_1_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#2563eb',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#e2e8f0',
                        'radiusCard' => '24px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1200px', 'anchor' => 'detail-main'],
                            'style' => ['padding' => '64px 0', 'background' => '#f8fafc'],
                            'children' => [
                                [
                                    'type' => 'row',
                                    'style' => ['gap' => '28px', 'alignItems' => 'start'],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 8],
                                            'blocks' => [
                                                [
                                                    'type' => 'text',
                                                    'props' => ['text' => 'DETAIL CONTENT'],
                                                    'style' => ['margin' => '0 0 10px', 'color' => '#2563eb', 'fontWeight' => '700', 'letterSpacing' => '0.18em'],
                                                ],
                                                [
                                                    'type' => 'model_detail',
                                                    'props' => ['model' => $accessIdentification !== '' ? $accessIdentification : 'news', 'template' => 'detail', 'title' => $model->name . getTranslateByKey('ft_lb_detail_suffix')],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 4],
                                            'blocks' => [
                                                [
                                                    'type' => 'cta',
                                                    'props' => [
                                                        'eyebrow' => 'ACTION PANEL',
                                                        'title' => getTranslateByKey('ft_lb_cta_2_title'),
                                                        'description' => getTranslateByKey('ft_lb_cta_2_desc'),
                                                        'primaryText' => getTranslateByKey('ft_lb_cta_consult_now'),
                                                        'primaryHref' => '/contact',
                                                        'secondaryText' => getTranslateByKey('ft_lb_cta_back_list'),
                                                        'secondaryHref' => $accessIdentification !== '' ? '/list/' . $accessIdentification : '#',
                                                        'align' => 'left',
                                                    ],
                                                ],
                                                [
                                                    'type' => 'qrcode',
                                                    'props' => [
                                                        'title' => getTranslateByKey('ft_lb_qr_2_title'),
                                                        'text' => getTranslateByKey('ft_lb_qr_2_text'),
                                                        'value' => 'https://example.com/contact',
                                                        'size' => '132',
                                                    ],
                                                    'style' => ['margin' => '18px 0 0'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => getTranslateByKey('ft_lb_preset_detail_2_name'),
                'desc' => getTranslateByKey('ft_lb_preset_detail_2_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#7c3aed',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#ede9fe',
                        'radiusCard' => '26px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1220px'],
                            'style' => ['padding' => '40px 0 18px', 'background' => '#ffffff'],
                            'children' => [
                                [
                                    'type' => 'image',
                                    'props' => [
                                        'src' => 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%221600%22 height=%22720%22 viewBox=%220 0 1600 720%22%3E%3Cdefs%3E%3ClinearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22%3E%3Cstop stop-color=%22%237c3aed%22/%3E%3Cstop offset=%221%22 stop-color=%22%230f172a%22/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width=%221600%22 height=%22720%22 rx=%2240%22 fill=%22url(%23g)%22/%3E%3Ctext x=%2250%25%22 y=%2252%25%22 text-anchor=%22middle%22 font-size=%2266%22 font-family=%22Arial,sans-serif%22 fill=%22white%22%3EDetail Hero%3C/text%3E%3C/svg%3E',
                                        'alt' => getTranslateByKey('ft_lb_hero_alt'),
                                        'align' => 'center',
                                    ],
                                    'style' => ['borderRadius' => '30px', 'overflow' => 'hidden'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1100px'],
                            'style' => ['padding' => '18px 0 64px'],
                            'children' => [
                                [
                                    'type' => 'model_detail',
                                    'props' => ['model' => $accessIdentification !== '' ? $accessIdentification : 'news', 'template' => 'detail', 'title' => ''],
                                ],
                                [
                                    'type' => 'cta',
                                    'props' => [
                                        'eyebrow' => 'BOTTOM CTA',
                                        'title' => getTranslateByKey('ft_lb_cta_3_title'),
                                        'description' => getTranslateByKey('ft_lb_cta_3_desc'),
                                        'primaryText' => getTranslateByKey('ft_lb_cta_submit_needs'),
                                        'primaryHref' => '/contact',
                                        'secondaryText' => getTranslateByKey('ft_lb_btn_view_more'),
                                        'secondaryHref' => $accessIdentification !== '' ? '/list/' . $accessIdentification : '#',
                                        'align' => 'left',
                                    ],
                                    'style' => ['margin' => '28px 0 0'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'home_section' => [
            [
                'name' => getTranslateByKey('ft_lb_preset_home_1_name'),
                'desc' => getTranslateByKey('ft_lb_preset_home_1_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#2563eb',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#dbeafe',
                        'radiusCard' => '24px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1200px', 'anchor' => 'home-section'],
                            'style' => ['padding' => '72px 0', 'background' => '#f8fbff'],
                            'children' => [
                                [
                                    'type' => 'heading',
                                    'props' => ['level' => 'h2', 'text' => $homeTitle !== '' ? $homeTitle : ($model->name . getTranslateByKey('ft_lb_home_section_suffix'))],
                                    'style' => ['margin' => '0 0 14px'],
                                ],
                                [
                                    'type' => 'text',
                                    'props' => ['text' => $homeDescription !== '' ? $homeDescription : getTranslateByKey('ft_lb_preset_home_1_intro')],
                                    'style' => ['margin' => '0 0 24px', 'lineHeight' => '1.9', 'color' => '#64748b'],
                                ],
                                [
                                    'type' => 'model_list',
                                    'props' => ['model' => $accessIdentification !== '' ? $accessIdentification : 'news', 'limit' => 6, 'template' => 'card', 'title' => ''],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => getTranslateByKey('ft_lb_preset_home_2_name'),
                'desc' => getTranslateByKey('ft_lb_preset_home_2_desc'),
                'schema' => [
                    'theme' => [
                        'primary' => '#0f766e',
                        'accent' => '#0f172a',
                        'surface' => '#ffffff',
                        'text' => '#0f172a',
                        'border' => '#ccfbf1',
                        'radiusCard' => '24px',
                    ],
                    'sections' => [
                        [
                            'type' => 'section',
                            'props' => ['contentWidth' => 'contained', 'innerWidth' => '1220px'],
                            'style' => ['padding' => '72px 0'],
                            'children' => [
                                [
                                    'type' => 'row',
                                    'style' => ['gap' => '28px', 'alignItems' => 'start'],
                                    'children' => [
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 4],
                                            'blocks' => [
                                                [
                                                    'type' => 'heading',
                                                    'props' => ['level' => 'h2', 'text' => $homeTitle !== '' ? $homeTitle : ($model->name . getTranslateByKey('ft_lb_home_aggregate_suffix'))],
                                                    'style' => ['margin' => '0 0 16px'],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'props' => ['text' => $homeDescription !== '' ? $homeDescription : getTranslateByKey('ft_lb_preset_home_2_intro')],
                                                    'style' => ['margin' => '0 0 22px', 'lineHeight' => '1.9', 'color' => '#64748b'],
                                                ],
                                                [
                                                    'type' => 'button',
                                                    'props' => ['href' => $accessIdentification !== '' ? '/list/' . $accessIdentification : '#', 'text' => getTranslateByKey('ft_lb_btn_view_all'), 'align' => 'left', 'variant' => 'solid'],
                                                    'style' => ['display' => 'inline-flex', 'padding' => '12px 22px'],
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'column',
                                            'props' => ['span' => 8],
                                            'blocks' => [
                                                [
                                                    'type' => 'model_list',
                                                    'props' => ['model' => $accessIdentification !== '' ? $accessIdentification : 'news', 'limit' => 4, 'template' => 'card', 'title' => ''],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $currentScenePresets = $scenePresets[$scene] ?? [];

    $catalogGroups = [
        getTranslateByKey('ft_lb_group_layout') => ['section', 'row', 'column'],
        getTranslateByKey('ft_lb_group_content') => ['heading', 'text', 'image', 'button', 'divider', 'html'],
        getTranslateByKey('ft_lb_group_business') => ['cta', 'stats', 'faq', 'gallery', 'carousel', 'video', 'navigation', 'sidebar', 'qrcode', 'login_box'],
        getTranslateByKey('ft_lb_group_model') => ['model_list', 'model_detail'],
    ];
    $catalogGroupItems = [];
    foreach ($catalogGroups as $groupLabel => $types) {
        $catalogGroupItems[$groupLabel] = array_values(array_filter($blockCatalog, static function ($block) use ($types) {
            return in_array($block['type'], $types, true);
        }));
    }
    $catalogGroupMeta = [
        getTranslateByKey('ft_lb_group_layout') => getTranslateByKey('ft_lb_group_layout_meta'),
        getTranslateByKey('ft_lb_group_content') => getTranslateByKey('ft_lb_group_content_meta'),
        getTranslateByKey('ft_lb_group_business') => getTranslateByKey('ft_lb_group_business_meta'),
        getTranslateByKey('ft_lb_group_model') => getTranslateByKey('ft_lb_group_model_meta'),
    ];
    $catalogGroupMarks = [
        getTranslateByKey('ft_lb_group_layout') => getTranslateByKey('ft_lb_mark_layout'),
        getTranslateByKey('ft_lb_group_content') => getTranslateByKey('ft_lb_mark_content'),
        getTranslateByKey('ft_lb_group_business') => getTranslateByKey('ft_lb_mark_business'),
        getTranslateByKey('ft_lb_group_model') => getTranslateByKey('ft_lb_mark_model'),
    ];
    $defaultCatalogGroup = '';
    foreach ($catalogGroupItems as $groupLabel => $items) {
        if ($defaultCatalogGroup === '' && !empty($items)) {
            $defaultCatalogGroup = $groupLabel;
        }
    }

    $sceneWorkspaceTips = [
        'list' => getTranslateByKey('ft_lb_tips_list'),
        'detail' => getTranslateByKey('ft_lb_tips_detail'),
        'home_section' => getTranslateByKey('ft_lb_tips_home'),
    ];
@endphp
@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    @import url('{{ asset('views/modules/formtools/assets/page-core.css') }}');

    .ft-builder {
        display: grid;
        gap: 18px;
    }

    .ft-builder__hero,
    .ft-builder__panel {
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
    }

    .ft-builder__hero {
        padding: 28px 30px;
        background: #0f172a;
        color: #fff;
    }

    .ft-builder__hero-title {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .ft-builder__hero h2 {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 700;
    }

    .ft-builder__hero p {
        margin: 0;
        max-width: 920px;
        line-height: 1.9;
        opacity: .92;
    }

    .ft-builder__hero-meta,
    .ft-builder__scene-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .ft-builder__chip {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        font-size: 12px;
    }

    .ft-builder__scene-tab {
        display: inline-flex;
        flex-direction: column;
        gap: 4px;
        min-width: 180px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.10);
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: #fff;
        text-decoration: none;
        transition: all .2s ease;
    }

    .ft-builder__scene-tab:hover,
    .ft-builder__scene-tab.is-active {
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-1px);
    }

    .ft-builder__scene-tab strong {
        font-size: 14px;
    }

    .ft-builder__scene-tab span {
        opacity: .82;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-builder__layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        grid-template-areas:
            "catalog workspace"
            "inspector workspace";
        gap: 18px;
        align-items: start;
    }

    .ft-builder__panel--catalog {
        grid-area: catalog;
    }

    .ft-builder__panel--workspace {
        grid-area: workspace;
    }

    .ft-builder__panel--inspector {
        grid-area: inspector;
    }

    .ft-builder__panel {
        overflow: hidden;
    }

    .ft-builder__panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .ft-builder__panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
    }

    .ft-builder__panel-desc {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.8;
    }

    .ft-builder__panel-body {
        padding: 18px 20px 20px;
    }

    .ft-builder__stack {
        display: grid;
        gap: 14px;
    }

    .ft-builder__preset-grid,
    .ft-builder__catalog-list,
    .ft-builder__quick-grid,
    .ft-builder__inspector-grid,
    .ft-builder__theme-grid {
        display: grid;
        gap: 10px;
    }

    .ft-builder__preset-card,
    .ft-builder__catalog-card {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid #dbe2ea;
        background: #f8fafc;
        transition: all .2s ease;
    }

    .ft-builder__preset-card:hover,
    .ft-builder__catalog-card:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        transform: translateY(-1px);
    }

    .ft-builder__preset,
    .ft-builder__catalog-item,
    .ft-builder__tree-card,
    .ft-builder__summary-card,
    .ft-builder__quick-button {
        border-radius: 18px;
        border: 1px solid #dbe2ea;
        background: #f8fafc;
        transition: all .2s ease;
    }

    .ft-builder__preset,
    .ft-builder__catalog-item,
    .ft-builder__quick-button {
        cursor: pointer;
    }

    .ft-builder__preset:hover,
    .ft-builder__catalog-item:hover,
    .ft-builder__quick-button:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        transform: translateY(-1px);
    }

    .ft-builder__preset {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
    }

    .ft-builder__preset-name,
    .ft-builder__catalog-name,
    .ft-builder__summary-title {
        color: #0f172a;
        font-weight: 700;
    }

    .ft-builder__preset-desc,
    .ft-builder__catalog-desc,
    .ft-builder__summary-desc {
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.8;
    }

    .ft-builder__catalog-group + .ft-builder__catalog-group {
        margin-top: 16px;
    }

    .ft-builder__catalog-group-title {
        margin: 0 0 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-builder__catalog-item {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
    }

    .ft-builder__card-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .ft-builder__card-actions .btn {
        min-width: 88px;
    }

    .ft-builder__workspace {
        display: grid;
        gap: 16px;
    }

    .ft-builder__toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 20px 22px 0;
    }

    .ft-builder__toolbar-actions,
    .ft-builder__preview-modes,
    .ft-builder__node-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ft-builder__summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        padding: 0 22px;
    }

    .ft-builder__summary-card {
        padding: 16px;
    }

    .ft-builder__workspace-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        grid-template-areas:
            "board"
            "advanced";
        gap: 16px;
        padding: 0 22px 22px;
        align-items: start;
    }

    .ft-builder__board,
    .ft-builder__preview-area,
    .ft-builder__advanced,
    .ft-builder__right-section {
        border-radius: 20px;
        border: 1px solid #e5edf5;
        background: #fff;
    }

    .ft-builder__board {
        grid-area: board;
    }

    .ft-builder__advanced {
        grid-area: advanced;
    }

    .ft-builder__board-head,
    .ft-builder__preview-head,
    .ft-builder__advanced-head,
    .ft-builder__right-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
        padding: 18px 18px 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .ft-builder__board-body,
    .ft-builder__preview-body,
    .ft-builder__advanced-body,
    .ft-builder__right-body {
        padding: 18px;
    }

    .ft-builder__tree-list {
        display: grid;
        gap: 12px;
    }

    .ft-builder__tree-group {
        display: grid;
        gap: 12px;
    }

    .ft-builder__tree-children {
        margin-left: 20px;
        padding-left: 14px;
        border-left: 2px dashed #dbe2ea;
        display: grid;
        gap: 12px;
    }

    .ft-builder__tree-card {
        position: relative;
        padding: 14px;
        background: #f8fafc;
        cursor: pointer;
    }

    .ft-builder__tree-card:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        transform: translateY(-1px);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
    }

    .ft-builder__tree-card.is-selected {
        border-color: #60a5fa;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.12);
    }

    .ft-builder__tree-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .ft-builder__tree-type {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
    }

    .ft-builder__tree-title {
        margin-top: 8px;
        color: #0f172a;
        font-weight: 700;
    }

    .ft-builder__tree-desc {
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.75;
    }

    .ft-builder__tree-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 6px;
        border-radius: 14px;
        border: 1px solid #eef2f7;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .12);
        opacity: 0;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    .ft-builder__tree-card:hover .ft-builder__tree-buttons,
    .ft-builder__tree-card.is-selected .ft-builder__tree-buttons {
        opacity: 1;
        pointer-events: auto;
    }

    .ft-builder__tree-preview {
        margin-top: 12px;
        border-radius: 14px;
        border: 1px solid #eef2f7;
        background: #fff;
        overflow: hidden;
    }

    .ft-builder__tree-preview-inner {
        padding: 12px 12px;
        display: grid;
        gap: 10px;
    }

    .ft-builder__mini-preview__kicker {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .ft-builder__mini-preview__title {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.25;
    }

    .ft-builder__mini-preview__heading {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.25;
    }

    .ft-builder__mini-preview__heading.is-h1 {
        font-size: 18px;
    }

    .ft-builder__mini-preview__heading.is-h2 {
        font-size: 16px;
    }

    .ft-builder__mini-preview__heading.is-h3 {
        font-size: 14px;
    }

    .ft-builder__mini-preview__text {
        color: #334155;
        font-size: 12px;
        line-height: 1.7;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
        overflow: hidden;
    }

    .ft-builder__mini-preview__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .ft-builder__mini-preview__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 30px;
        padding: 0 12px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .ft-builder__mini-preview__btn.is-primary {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .ft-builder__mini-preview__btn.is-outline {
        border-color: #93c5fd;
        background: #fff;
        color: #1d4ed8;
    }

    .ft-builder__mini-preview__divider {
        height: 1px;
        background: #e2e8f0;
    }

    .ft-builder__mini-preview__media {
        position: relative;
        height: 140px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 45%, #f1f5f9 100%);
        overflow: hidden;
    }

    .ft-builder__mini-preview__media-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 0 10px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: rgba(219, 234, 254, .75);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
    }

    .ft-builder__mini-preview__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .ft-builder__mini-preview__card {
        padding: 10px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
        display: grid;
        gap: 8px;
    }

    .ft-builder__mini-preview__table {
        display: grid;
        gap: 8px;
    }

    .ft-builder__mini-preview__table-row {
        display: grid;
        grid-template-columns: 120px 1fr 90px;
        gap: 10px;
    }

    .ft-builder__mini-preview__muted {
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }

    .ft-builder__skeleton-line {
        height: 10px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(226,232,240,.88) 0%, rgba(241,245,249,.96) 45%, rgba(226,232,240,.88) 100%);
    }

    .ft-builder__skeleton-line.is-lg {
        height: 14px;
        border-radius: 10px;
    }

    .ft-builder__skeleton-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .ft-builder__skeleton-table-head {
        display: grid;
        grid-template-columns: 120px 1fr 140px;
        gap: 10px;
    }

    .ft-builder__skeleton-table-row {
        display: grid;
        grid-template-columns: 120px 1fr 140px;
        gap: 10px;
    }

    .ft-builder__mini-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        border-radius: 10px;
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
        cursor: pointer;
        transition: all .2s ease;
        font-size: 12px;
        line-height: 1;
    }

    .ft-builder__mini-btn:hover {
        border-color: #93c5fd;
        color: #1d4ed8;
    }

    .ft-builder__mini-btn--danger:hover {
        border-color: #fecaca;
        color: #dc2626;
    }

    .ft-builder__preview-shell {
        margin: 0 auto;
        min-height: 620px;
        border-radius: 20px;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        overflow: auto;
        position: relative;
        box-sizing: border-box;
        transition: width .2s ease, min-height .2s ease;
    }

    .ft-builder__preview-shell.is-desktop {
        width: calc(100% - 36px);
        max-width: 100%;
    }

    .ft-builder__preview-shell.is-tablet {
        width: 840px;
        max-width: 100%;
    }

    .ft-builder__preview-shell.is-mobile {
        width: 430px;
        max-width: 100%;
    }

    .ft-builder__preview-shell-inner {
        min-height: 580px;
        padding: 20px;
    }

    .ft-builder__preview-mode {
        min-width: 72px;
    }

    .ft-builder__preview-mode.is-active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .ft-builder__preview-shell.is-expanded {
        width: 100%;
        min-height: calc(100vh - 180px);
    }

    .ft-builder__preview-shell.is-expanded .ft-builder__preview-shell-inner {
        min-height: calc(100vh - 230px);
    }

    .ft-builder__preview-stage {
        padding: 18px;
        border-radius: 18px;
        background: #edf4ff;
    }

    .ft-builder__preview-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ft-builder__preview-dialog {
        padding: 18px;
        background: #f8fafc;
    }

    .ft-builder__preview-dialog-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .ft-builder__preview-dialog-title {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .ft-builder__preview-dialog-modes {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ft-builder__preview-dialog-shell {
        width: 100%;
        min-height: 75vh;
        border-radius: 20px;
        border: 1px solid #dbe2ea;
        background: #fff;
        overflow: auto;
    }

    .ft-builder__preview-dialog-shell.is-desktop {
        max-width: 100%;
    }

    .ft-builder__preview-dialog-shell.is-tablet {
        max-width: 960px;
        margin: 0 auto;
    }

    .ft-builder__preview-dialog-shell.is-mobile {
        max-width: 430px;
        margin: 0 auto;
    }

    .ft-builder__preview-dialog-inner {
        min-height: 72vh;
        padding: 20px;
    }

    .ft-builder__canvas-preview {
        min-height: 0;
    }

    #layoutPreview [data-node-id] {
        cursor: pointer;
    }

    #layoutPreview [data-node-id].ft-builder__preview-node--selected {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        border-radius: 12px;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.14);
    }

    #layoutPreview [data-node-id]:hover {
        outline: 1px solid rgba(37, 99, 235, 0.65);
        outline-offset: 2px;
        border-radius: 12px;
    }

    .ft-builder__preview-floatbar {
        position: absolute;
        z-index: 1999;
        display: none;
        gap: 6px;
        padding: 6px;
        border-radius: 14px;
        border: 1px solid rgba(203, 213, 225, 0.9);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(12px);
        align-items: center;
        pointer-events: auto;
        user-select: none;
    }

    .ft-builder__preview-floatbar-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 1px solid rgba(203, 213, 225, 0.85);
        background: rgba(255, 255, 255, 0.98);
        color: #0f172a;
        transition: background .15s ease, border-color .15s ease, transform .15s ease;
    }

    .ft-builder__preview-floatbar-btn:hover {
        background: rgba(241, 245, 249, 0.9);
        border-color: rgba(148, 163, 184, 0.9);
        transform: translateY(-1px);
    }

    .ft-builder__preview-floatbar-btn svg {
        display: block;
        width: 16px;
        height: 16px;
        fill: currentColor;
    }

    .ft-builder__preview-floatbar-btn.is-danger {
        border-color: rgba(254, 202, 202, 0.95);
        color: #dc2626;
    }

    .ft-builder__preview-floatbar-btn.is-danger:hover {
        background: rgba(254, 202, 202, 0.18);
        border-color: rgba(252, 165, 165, 0.95);
        color: #b91c1c;
    }

    .ft-builder__inspector-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .ft-builder__textarea {
        width: 100%;
        min-height: 320px;
        border-radius: 16px;
        border: 1px solid #dbe2ea;
        padding: 14px 16px;
        font-family: Consolas, Monaco, monospace;
        line-height: 1.8;
        resize: vertical;
    }

    .ft-builder__textarea.is-invalid {
        border-color: #f87171;
        box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.12);
    }

    .ft-builder__textarea:focus,
    .ft-builder__field-input:focus,
    .ft-builder__field-textarea:focus,
    .ft-builder__field-select:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.12);
    }

    .ft-builder__status {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-builder__status.is-valid {
        background: #dcfce7;
        color: #166534;
    }

    .ft-builder__status.is-invalid {
        background: #fee2e2;
        color: #991b1b;
    }

    .ft-builder__field {
        display: grid;
        gap: 6px;
    }

    .ft-builder__field label {
        margin: 0;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-builder__field-input,
    .ft-builder__field-textarea,
    .ft-builder__field-select {
        width: 100%;
        min-height: 42px;
        border-radius: 14px;
        border: 1px solid #dbe2ea;
        background: #fff;
        padding: 10px 12px;
        color: #0f172a;
        transition: all .2s ease;
    }

    .ft-builder__field-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .ft-builder__section-title {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
    }

    .ft-builder__muted {
        color: #64748b;
        font-size: 12px;
        line-height: 1.8;
    }

    .ft-builder__empty {
        padding: 18px;
        border-radius: 18px;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        line-height: 1.9;
        text-align: center;
    }

    .ft-builder__right-section + .ft-builder__right-section {
        margin-top: 14px;
    }

    details.ft-builder__advanced[open] .ft-builder__advanced-head {
        border-bottom: 1px solid #eef2f7;
    }

    .ft-builder__advanced-head {
        list-style: none;
        cursor: pointer;
    }

    .ft-builder__advanced-head::-webkit-details-marker {
        display: none;
    }

    @media (max-width: 1600px) {
        .ft-builder__layout {
            grid-template-columns: 300px minmax(0, 1fr);
        }

    }

    @media (max-width: 1360px) {
        .ft-builder__layout {
            grid-template-columns: 1fr;
            grid-template-areas:
                "catalog"
                "workspace"
                "inspector";
        }

        .ft-builder__summary {
            grid-template-columns: 1fr;
        }

    }

    .page-content > .content-wrapper {
        min-width: 0;
    }

    #layoutBuilderForm {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        gap: 12px;
        height: calc(100vh - 180px);
        min-height: 680px;
        padding-bottom: 120px;
    }

    .ft-model-layout-head {
        display: grid;
        gap: 18px;
        padding: 26px 28px;
        border-radius: 24px;
        border: 1px solid #dbe2ea;
        background: #0f172a;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
        color: #fff;
    }

    .ft-model-layout-head__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .ft-model-layout-head__title h2 {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 700;
    }

    .ft-model-layout-head__title p {
        margin: 0;
        line-height: 1.9;
        opacity: .92;
    }

    .ft-model-layout-head__meta,
    .ft-model-layout-head__tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ft-model-layout-head__chip {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        font-size: 12px;
    }

    .ft-model-layout-head__tab {
        display: inline-flex;
        flex-direction: column;
        gap: 4px;
        min-width: 180px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.10);
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: #fff;
        text-decoration: none;
        transition: all .2s ease;
    }

    .ft-model-layout-head__tab:hover,
    .ft-model-layout-head__tab.is-active {
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-1px);
    }

    .ft-model-layout-head__tab strong {
        font-size: 14px;
    }

    .ft-model-layout-head__tab span {
        opacity: .82;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-builder--model-layout {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr) 360px;
        gap: 10px;
        align-items: start;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel {
        position: sticky;
        top: 16px;
        height: calc(100vh - 32px);
        min-height: 560px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #f8fafc;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65), 0 10px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel--overlay,
    .ft-page-builder--model-layout .ft-page-builder__panel--canvas {
        z-index: 1;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel--catalog {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        z-index: 30;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel--canvas {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
    }

    .ft-page-builder--model-layout .ft-page-builder__panel--inspector {
        z-index: 20;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-track {
        position: relative;
        min-height: 0;
        height: 100%;
        align-self: start;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-header {
        position: relative;
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.96);
        z-index: 10;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-kicker,
    .ft-page-builder--model-layout .ft-page-builder__canvas-toolbar-chip {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 2px 7px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.12);
        color: #475569;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-title,
    .ft-page-builder--model-layout .ft-page-builder__catalog-current-title {
        margin: 0 0 2px;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-desc,
    .ft-page-builder--model-layout .ft-page-builder__catalog-current-meta,
    .ft-page-builder--model-layout .ft-page-builder__catalog-meta {
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-actions,
    .ft-page-builder--model-layout .ft-page-builder__canvas-actions,
    .ft-page-builder--model-layout .ft-page-builder__selection-actions,
    .ft-page-builder--model-layout .ft-page-builder__inspector-actions,
    .ft-page-builder--model-layout .ft-page-builder__inspector-sticky-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-body {
        position: relative;
        padding: 10px;
        overflow: auto;
    }

    .ft-page-builder--model-layout .ft-page-builder__panel-body--canvas,
    .ft-page-builder--model-layout .ft-page-builder__panel-body--inspector {
        display: grid;
        gap: 10px;
        min-height: 0;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-shell {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr);
        gap: 8px;
        align-items: start;
        min-height: 100%;
        height: 100%;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-toolbox {
        position: sticky;
        top: 0;
        display: grid;
        gap: 6px;
        padding: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-tool {
        display: grid;
        justify-items: center;
        gap: 3px;
        width: 100%;
        padding: 6px 3px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all .2s ease;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-tool:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-tool.is-active {
        border-color: #2563eb;
        background: #dbeafe;
        color: #1d4ed8;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-tool-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 8px;
        background: rgba(148, 163, 184, 0.12);
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-tool-label,
    .ft-page-builder--model-layout .ft-page-builder__inspector-tab-label {
        display: none;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-main,
    .ft-page-builder--model-layout .ft-page-builder__inspector-main {
        min-width: 0;
        display: grid;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-toolbar {
        position: sticky;
        top: -10px;
        z-index: 3;
        display: grid;
        gap: 6px;
        margin: -12px -12px 0;
        padding: 12px 12px 8px;
        border-bottom: 1px solid rgba(219, 226, 234, 0.9);
        background: rgba(248, 250, 252, 0.96);
        backdrop-filter: blur(10px);
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-toolbar-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-quick {
        display: grid;
        gap: 6px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-quick-list {
        display: flex;
        gap: 6px;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-quick-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 5px 10px;
        border: 1px solid #dbe2ea;
        border-radius: 999px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-quick-btn:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-target,
    .ft-page-builder--model-layout .ft-page-builder__canvas-status {
        padding: 12px 14px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #eff6ff;
        color: #1e3a8a;
        line-height: 1.7;
        font-size: 12px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-target code,
    .ft-page-builder--model-layout .ft-page-builder__canvas-status code {
        color: #1d4ed8;
        background: rgba(255, 255, 255, 0.75);
        padding: 2px 8px;
        border-radius: 999px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-list {
        display: grid;
        gap: 8px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section {
        display: none;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section.is-visible {
        display: block;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        padding: 12px 14px;
        border: 0;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-title {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-meta {
        margin-top: 4px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-toggle,
    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
        transition: transform .2s ease, background .2s ease, color .2s ease;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section.is-open .ft-page-builder__catalog-section-toggle,
    .ft-page-builder--model-layout .ft-page-builder__inspector-outline.is-expanded .ft-page-builder__inspector-outline-toggle {
        transform: rotate(180deg);
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-body,
    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-body {
        display: none;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section.is-open .ft-page-builder__catalog-section-body,
    .ft-page-builder--model-layout .ft-page-builder__inspector-outline.is-expanded .ft-page-builder__inspector-outline-body {
        display: block;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-body {
        padding: 0 10px 10px;
        border-top: 1px solid #f1f5f9;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-section-list {
        display: grid;
        gap: 6px;
        padding-top: 8px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-card {
        padding: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-card h5 {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .ft-page-builder--model-layout .ft-page-builder__catalog-type {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 10px;
    }

    .ft-page-builder--model-layout .ft-page-builder__canvas-toolbar,
    .ft-page-builder--model-layout .ft-page-builder__selection-bar,
    .ft-page-builder--model-layout .ft-page-builder__inspector-summary,
    .ft-page-builder--model-layout .ft-page-builder__breadcrumb {
        padding: 12px 14px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: #fff;
    }

    .ft-page-builder--model-layout .ft-page-builder__canvas-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ft-page-builder--model-layout .ft-page-builder__canvas-stage {
        display: grid;
        gap: 10px;
        padding: 10px 10px 140px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: #fff;
        min-height: 0;
        height: 100%;
        overflow: auto;
    }

    .ft-page-builder--model-layout .ft-page-builder__selection-bar,
    .ft-page-builder--model-layout .ft-page-builder__inspector-summary {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .ft-page-builder--model-layout .ft-page-builder__selection-bar.is-visible,
    .ft-page-builder--model-layout .ft-page-builder__inspector-summary.is-visible {
        display: flex;
    }

    .ft-page-builder--model-layout .ft-page-builder__selection-main strong,
    .ft-page-builder--model-layout .ft-page-builder__inspector-summary strong {
        display: block;
        margin-bottom: 4px;
        color: #0f172a;
        font-size: 14px;
    }

    .ft-page-builder--model-layout .ft-page-builder__selection-main span,
    .ft-page-builder--model-layout .ft-page-builder__inspector-summary span {
        display: block;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-builder--model-layout .ft-page-builder__breadcrumb {
        display: none;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ft-page-builder--model-layout .ft-page-builder__breadcrumb.is-visible {
        display: flex;
    }

    .ft-page-builder--model-layout .ft-page-builder__breadcrumb-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .ft-page-builder--model-layout .ft-page-builder__breadcrumb-item.is-current {
        border-color: #94a3b8;
        background: #fff;
        color: #0f172a;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.18);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline {
        display: none;
        border-radius: 16px;
        border: 1px solid #dbe2ea;
        background: #fff;
        overflow: hidden;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline.is-visible {
        display: block;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        padding: 12px 14px;
        border: 0;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-title {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-summary {
        margin-top: 3px;
        color: #475569;
        font-size: 12px;
        line-height: 1.6;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-body {
        padding: 0 14px 14px;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-item {
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.78);
        color: #475569;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-outline-item strong {
        display: block;
        color: #0f172a;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: -12px -12px 2px;
        padding: 8px 12px 10px;
        border-bottom: 1px solid #e5e7eb;
        background: rgba(248, 250, 252, 0.92);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 36px;
        min-height: 32px;
        padding: 4px 10px;
        border: 1px solid #dbe2ea;
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tab:hover {
        border-color: #bfdbfe;
        color: #1d4ed8;
        transform: translateY(-1px);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tab.is-active {
        border-color: #93c5fd;
        background: #dbeafe;
        color: #1d4ed8;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tab-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-panel {
        display: none;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-panel.is-active {
        display: block;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-tab-label {
        display: inline;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-sticky-actions {
        display: none;
        position: sticky;
        top: -12px;
        z-index: 3;
        margin: -12px -12px 12px;
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        background: rgba(248, 250, 252, 0.94);
        backdrop-filter: blur(10px);
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-sticky-actions.is-visible {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-sticky-meta {
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold {
        margin-bottom: 0;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
        overflow: hidden;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 10px;
        cursor: pointer;
        list-style: none;
        user-select: none;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-summary::-webkit-details-marker {
        display: none;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-title::before {
        content: '+';
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 999px;
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #64748b;
        font-size: 11px;
        line-height: 1;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold[open] .ft-page-builder__fold-title::before {
        content: '-';
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-meta {
        color: #64748b;
        font-size: 10px;
        line-height: 1.4;
    }

    .ft-page-builder--model-layout .ft-page-builder__fold-body {
        padding: 0 10px 10px;
        border-top: 1px solid #eef2f7;
    }

    .ft-page-builder--model-layout .ft-page-builder__preset-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding-top: 8px;
    }

    .ft-page-builder--model-layout .ft-page-builder__preset-list .btn,
    .ft-page-builder--model-layout .ft-page-builder__selection-actions .btn,
    .ft-page-builder--model-layout .ft-page-builder__inspector-sticky-buttons .btn {
        border-radius: 10px;
    }

    .ft-page-builder--model-layout .ft-page-builder__inspector-shell {
        min-height: 0;
    }

    .ft-page-form__floating-bar {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        bottom: 24px;
        z-index: 140;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        width: min(960px, calc(100% - 120px));
        padding: 12px 16px;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 22px;
        background: rgba(15, 23, 42, 0.92);
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.2);
        backdrop-filter: blur(16px);
    }

    .ft-page-form__floating-main {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .ft-page-form__floating-title {
        color: #f8fafc;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }

    .ft-page-form__floating-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.7;
    }

    .ft-page-form__floating-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.16);
        color: #e2e8f0;
    }

    .ft-page-form__floating-chip.is-success {
        background: rgba(16, 185, 129, 0.18);
        color: #bbf7d0;
    }

    .ft-page-form__floating-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
    }

    .ft-page-form__floating-actions .btn {
        border-radius: 999px;
        min-width: 118px;
        font-weight: 700;
    }

    @media (max-width: 1600px) {
        .ft-page-builder--model-layout {
            grid-template-columns: 320px minmax(0, 1fr) 340px;
        }
    }

    @media (max-width: 1360px) {
        #layoutBuilderForm {
            height: auto;
            min-height: 0;
        }

        .ft-page-builder--model-layout {
            grid-template-columns: 1fr;
            height: auto;
        }

        .ft-page-builder--model-layout .ft-page-builder__panel--inspector {
            position: relative;
            top: auto;
            max-height: none;
        }
    }

    @media (max-width: 991px) {
        .ft-model-layout-head {
            padding: 22px 18px;
        }

        .ft-page-builder--model-layout .ft-page-builder__catalog-shell {
            grid-template-columns: 1fr;
        }

        .ft-page-builder--model-layout .ft-page-builder__catalog-toolbox {
            position: static;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .ft-page-form__floating-bar {
            width: calc(100% - 24px);
            bottom: 12px;
        }
    }

    @media (max-width: 767px) {
        .ft-page-builder--model-layout .ft-page-builder__inspector-outline-grid {
            grid-template-columns: 1fr;
        }

        .ft-page-form__floating-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .ft-page-form__floating-actions {
            justify-content: stretch;
        }

        .ft-page-form__floating-actions .btn {
            width: 100%;
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

                <form method="post" action="{{ url('admin/formtools/modelLayoutBuilder?id=' . $model->id . '&scene=' . $scene) }}" id="layoutBuilderForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $model->id }}">
                    <section class="ft-model-layout-head">
                        <div class="ft-model-layout-head__top">
                            <div class="ft-model-layout-head__title">
                                <h2>{{ $sceneLabel }}{{ getTranslateByKey('ft_lb_builder') }}</h2>
                                <p>{{ getTranslateByKey('ft_lb_current_model') }}{{ $model->name }} / {{ $model->access_identification }}</p>
                            </div>
                            <div class="ft-model-layout-head__meta">
                                <span class="ft-model-layout-head__chip">{{ getTranslateByKey('ft_lb_scene') }}{{ $sceneLabel }}</span>
                                <span class="ft-model-layout-head__chip">{{ getTranslateByKey('ft_lb_model_identification_colon') }}{{ $model->identification }}</span>
                                <span class="ft-model-layout-head__chip">{{ getTranslateByKey('ft_lb_access_identification_colon') }}{{ $model->access_identification }}</span>
                                @if(!empty($sceneMeta['route']))
                                    <span class="ft-model-layout-head__chip">{{ getTranslateByKey('ft_lb_route_colon') }}{{ $sceneMeta['route'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="ft-model-layout-head__tabs">
                            @foreach($sceneTabs as $tab)
                                <a href="{{ $tab['url'] }}" class="ft-model-layout-head__tab{{ $tab['active'] ? ' is-active' : '' }}">
                                    <strong>{{ $tab['label'] }}</strong>
                                    <span>{{ $tab['summary'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    <div class="ft-page-builder ft-page-builder--model-layout" id="layoutBuilderWorkspace">
                        <div class="ft-page-builder__panel ft-page-builder__panel--overlay ft-page-builder__panel--catalog">
                            <div class="ft-page-builder__panel-header">
                                <div class="ft-page-builder__panel-head">
                                    <div>
                                        <span class="ft-page-builder__panel-kicker">Catalog</span>
                                        <h3 class="ft-page-builder__panel-title">{{ getTranslateByKey('ft_lb_template_library') }}</h3>
                                        <p class="ft-page-builder__panel-desc">{{ $sceneWorkspaceTips[$scene] ?? getTranslateByKey('ft_lb_default_tip_1') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="ft-page-builder__panel-body ft-page-builder__panel-body--catalog">
                                <div class="ft-page-builder__catalog-shell">
                                    <div class="ft-page-builder__catalog-toolbox" id="layoutBuilderCatalogFilters">
                                        @foreach($catalogGroupItems as $groupLabel => $items)
                                            @if(!empty($items))
                                                <button type="button"
                                                        class="ft-page-builder__catalog-tool js-catalog-group{{ $groupLabel === $defaultCatalogGroup ? ' is-active' : '' }}"
                                                        data-group="{{ $groupLabel }}"
                                                        title="{{ $groupLabel }}">
                                                    <span class="ft-page-builder__catalog-tool-mark">{{ $catalogGroupMarks[$groupLabel] ?? (function_exists('mb_substr') ? mb_substr($groupLabel, 0, 1) : substr($groupLabel, 0, 1)) }}</span>
                                                    <span class="ft-page-builder__catalog-tool-label">{{ $groupLabel }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="ft-page-builder__catalog-main">
                                        <div class="ft-page-builder__catalog-toolbar">
                                            <div class="ft-page-builder__catalog-toolbar-head">
                                                <div class="ft-page-builder__catalog-current">
                                                    <p class="ft-page-builder__catalog-current-title" id="layoutBuilderCatalogTitle">{{ $defaultCatalogGroup !== '' ? $defaultCatalogGroup : getTranslateByKey('ft_lb_component_library') }}</p>
                                                    <div class="ft-page-builder__catalog-current-meta" id="layoutBuilderCatalogMeta">{{ $catalogGroupMeta[$defaultCatalogGroup] ?? ($sceneWorkspaceTips[$scene] ?? '') }}</div>
                                                </div>
                                            </div>
                                            <div class="ft-page-builder__catalog-quick">
                                                <p class="ft-page-builder__catalog-quick-title">{{ getTranslateByKey('ft_lb_quick_actions') }}</p>
                                                <div class="ft-page-builder__catalog-quick-list">
                                                    <button type="button" class="ft-page-builder__catalog-quick-btn js-layout-empty-root">{{ getTranslateByKey('ft_lb_clear_canvas') }}</button>
                                                    <button type="button" class="ft-page-builder__catalog-quick-btn js-layout-add-root-section">{{ getTranslateByKey('ft_lb_add_root_section') }}</button>
                                                    <button type="button" class="ft-page-builder__catalog-quick-btn js-preview-dialog">{{ getTranslateByKey('ft_lb_view_preview') }}</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ft-page-builder__catalog-target" id="layoutBuilderCatalogTarget"></div>

                                        <details class="ft-page-builder__fold" open>
                                            <summary class="ft-page-builder__fold-summary">
                                                <span class="ft-page-builder__fold-title">{{ getTranslateByKey('ft_lb_full_page_presets') }}</span>
                                                <span class="ft-page-builder__fold-meta">{{ getTranslateByKey('ft_lb_presets_count', 'admin', ['count' => count($currentScenePresets)]) }}</span>
                                            </summary>
                                            <div class="ft-page-builder__fold-body">
                                                <div class="ft-page-builder__preset-list">
                                                    @foreach($currentScenePresets as $preset)
                                                        <button type="button"
                                                                class="btn btn-default js-layout-preset"
                                                                data-schema='{{ json_encode($preset['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}'>{{ $preset['name'] }}</button>
                                                        <button type="button"
                                                                class="btn btn-default js-layout-preset-preview"
                                                                data-schema='{{ json_encode($preset['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}'>{{ getTranslateByKey('ft_lb_view_preset') }}</button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </details>

                                        <div class="ft-page-builder__catalog-list">
                                            @foreach($catalogGroupItems as $groupLabel => $items)
                                                @if(!empty($items))
                                                    <div class="ft-page-builder__catalog-section{{ $groupLabel === $defaultCatalogGroup ? ' is-visible is-open' : '' }}" data-catalog-group-section="{{ $groupLabel }}">
                                                        <button type="button" class="ft-page-builder__catalog-section-head js-catalog-section-toggle">
                                                            <div>
                                                                <h5 class="ft-page-builder__catalog-section-title">{{ $groupLabel }}</h5>
                                                                <div class="ft-page-builder__catalog-section-meta">{{ $catalogGroupMeta[$groupLabel] ?? '' }}</div>
                                                            </div>
                                                            <span class="ft-page-builder__catalog-section-toggle">v</span>
                                                        </button>
                                                        <div class="ft-page-builder__catalog-section-body">
                                                            <div class="ft-page-builder__catalog-section-list">
                                                                @foreach($items as $block)
                                                                    <div class="ft-page-builder__catalog-card">
                                                                        <div class="ft-page-builder__catalog-card-head">
                                                                            <div>
                                                                                <h5>{{ $block['name'] }}</h5>
                                                                                <div class="ft-builder__catalog-desc">{{ $block['desc'] }}</div>
                                                                            </div>
                                                                            <span class="ft-page-builder__catalog-type">{{ $block['type'] }}</span>
                                                                        </div>
                                                                        <div class="ft-page-builder__catalog-actions">
                                                                            <button type="button"
                                                                                    class="btn btn-default btn-xs js-layout-block"
                                                                                    data-schema='{{ json_encode(json_decode($block['schema'], true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}'>{{ getTranslateByKey('ft_lb_insert_current') }}</button>
                                                                            <button type="button"
                                                                                    class="btn btn-default btn-xs js-layout-block-root"
                                                                                    data-schema='{{ json_encode(json_decode($block['schema'], true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}'>{{ getTranslateByKey('ft_lb_insert_root') }}</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ft-page-builder__panel ft-page-builder__panel--canvas">
                            <div class="ft-page-builder__panel-header">
                                <div class="ft-page-builder__panel-head">
                                    <div>
                                        <span class="ft-page-builder__panel-kicker">Canvas</span>
                                        <h3 class="ft-page-builder__panel-title">{{ getTranslateByKey('ft_lb_canvas') }}</h3>
                                        <p class="ft-page-builder__panel-desc">{{ getTranslateByKey('ft_lb_canvas_desc') }}</p>
                                    </div>
                                    <div class="ft-page-builder__panel-actions">
                                        <a href="{{ url('admin/formtools/modelEdit?id=' . $model->id) }}" class="btn btn-default btn-sm">{{ getTranslateByKey('ft_lb_back_model') }}</a>
                                        <button type="button" class="btn btn-default btn-sm js-layout-preview">{{ getTranslateByKey('ft_lb_refresh_preview') }}</button>
                                        <button type="button" class="btn btn-default btn-sm js-preview-dialog">{{ getTranslateByKey('ft_lb_view_preview') }}</button>
                                        <button type="submit" class="btn btn-primary btn-sm">{{ getTranslateByKey('ft_lb_save_layout') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="ft-page-builder__panel-body ft-page-builder__panel-body--canvas">
                                <div class="ft-page-builder__canvas-toolbar">
                                    <div>
                                        <span class="ft-page-builder__canvas-toolbar-chip">{{ strtoupper($scene) }}</span>
                                        <div class="ft-builder__muted" style="margin-top:8px;">{{ $sceneWorkspaceTips[$scene] ?? getTranslateByKey('ft_lb_default_tip_2') }}</div>
                                    </div>
                                    <div class="ft-builder__preview-actions">
                                        <button type="button" class="btn btn-default ft-builder__preview-mode js-preview-mode is-active" data-mode="desktop">PC</button>
                                        <button type="button" class="btn btn-default ft-builder__preview-mode js-preview-mode" data-mode="tablet">Pad</button>
                                        <button type="button" class="btn btn-default ft-builder__preview-mode js-preview-mode" data-mode="mobile">Mobile</button>
                                    </div>
                                </div>

                                <div class="ft-page-builder__canvas-status">
                                    {{ getTranslateByKey('ft_lb_edit_target') }}<code id="layoutCurrentTargetText">{{ getTranslateByKey('ft_lb_root_sections') }}</code>。
                                    {{ getTranslateByKey('ft_lb_canvas_hint') }}
                                </div>

                                <div class="ft-page-builder__breadcrumb" id="layoutBuilderSelectionBreadcrumb"></div>
                                <div class="ft-page-builder__selection-bar" id="layoutBuilderSelectionBar"></div>

                                <div class="ft-page-builder__canvas-stage">
                                    <div id="layoutTree" class="ft-builder__canvas-preview">
                                        <div class="ft-builder__preview-stage">
                                            <div class="ft-builder__preview-shell is-desktop" id="layoutPreviewShell">
                                                <div class="ft-builder__preview-shell-inner" id="layoutPreview">
                                                    <div id="layoutPreviewContent">{!! $previewHtml !!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ft-page-builder__inspector-track" id="layoutBuilderInspectorTrack">
                            <div class="ft-page-builder__panel ft-page-builder__panel--overlay ft-page-builder__panel--inspector">
                                <div class="ft-page-builder__panel-header">
                                    <div class="ft-page-builder__panel-head">
                                        <div>
                                            <span class="ft-page-builder__panel-kicker">Inspector</span>
                                            <h3 class="ft-page-builder__panel-title">{{ getTranslateByKey('ft_lb_inspector') }}</h3>
                                            <p class="ft-page-builder__panel-desc">{{ getTranslateByKey('ft_lb_inspector_desc') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ft-page-builder__panel-body ft-page-builder__panel-body--inspector">
                                    <div class="ft-page-builder__inspector-shell">
                                        <div class="ft-page-builder__inspector-main">
                                            <div class="ft-page-builder__inspector-summary" id="layoutBuilderInspectorSummary"></div>
                                            <div class="ft-page-builder__inspector-outline" id="layoutBuilderInspectorOutline"></div>
                                            <div class="ft-page-builder__inspector-tabs" id="layoutBuilderInspectorTabs">
                                                <button type="button" class="ft-page-builder__inspector-tab is-active js-inspector-tab" data-inspector-tab="content" title="{{ getTranslateByKey('ft_lb_tab_common') }}"><span class="ft-page-builder__inspector-tab-icon">{{ getTranslateByKey('ft_lb_tab_common_icon') }}</span><span class="ft-page-builder__inspector-tab-label">{{ getTranslateByKey('ft_lb_tab_common') }}</span></button>
                                                <button type="button" class="ft-page-builder__inspector-tab js-inspector-tab" data-inspector-tab="style" title="{{ getTranslateByKey('ft_lb_tab_style') }}"><span class="ft-page-builder__inspector-tab-icon">{{ getTranslateByKey('ft_lb_tab_style_icon') }}</span><span class="ft-page-builder__inspector-tab-label">{{ getTranslateByKey('ft_lb_tab_style') }}</span></button>
                                                <button type="button" class="ft-page-builder__inspector-tab js-inspector-tab" data-inspector-tab="responsive" title="{{ getTranslateByKey('ft_lb_tab_layout') }}"><span class="ft-page-builder__inspector-tab-icon">{{ getTranslateByKey('ft_lb_tab_layout_icon') }}</span><span class="ft-page-builder__inspector-tab-label">{{ getTranslateByKey('ft_lb_tab_layout') }}</span></button>
                                                <button type="button" class="ft-page-builder__inspector-tab js-inspector-tab" data-inspector-tab="advanced" title="JSON"><span class="ft-page-builder__inspector-tab-icon">{{ getTranslateByKey('ft_lb_tab_json_icon') }}</span><span class="ft-page-builder__inspector-tab-label">JSON</span></button>
                                            </div>
                                            <div class="ft-page-builder__inspector-sticky-actions" id="layoutBuilderInspectorStickyActions"></div>

                                            <div class="ft-page-builder__inspector-panel is-active" data-inspector-panel="content">
                                                <div id="nodeInspector"></div>
                                            </div>

                                            <div class="ft-page-builder__inspector-panel" data-inspector-panel="style">
                                                <div id="themeInspector" class="ft-builder__theme-grid"></div>
                                            </div>

                                            <div class="ft-page-builder__inspector-panel" data-inspector-panel="responsive">
                                                <div id="layoutMetaInspector"></div>
                                            </div>

                                            <div class="ft-page-builder__inspector-panel" data-inspector-panel="advanced">
                                                <details class="ft-page-builder__fold" open>
                                                    <summary class="ft-page-builder__fold-summary">
                                                        <span class="ft-page-builder__fold-title">{{ getTranslateByKey('ft_lb_advanced_schema') }}</span>
                                                        <span class="ft-page-builder__fold-meta" id="layoutJsonStatus">{{ getTranslateByKey('ft_lb_json_ok') }}</span>
                                                    </summary>
                                                    <div class="ft-page-builder__fold-body">
                                                        <textarea name="layout_schema" id="layoutSchema" class="ft-builder__textarea">{{ $layoutSchema }}</textarea>
                                                    </div>
                                                </details>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ft-page-form__floating-bar" id="layoutBuilderFloatingBar">
                        <div class="ft-page-form__floating-main">
                            <div class="ft-page-form__floating-title">{{ $sceneLabel }}{{ getTranslateByKey('ft_lb_editing') }}</div>
                            <div class="ft-page-form__floating-meta">
                                <span class="ft-page-form__floating-chip">{{ $model->name }}</span>
                                <span class="ft-page-form__floating-chip">{{ $sceneLabel }}</span>
                                <span class="ft-page-form__floating-chip is-success">{{ getTranslateByKey('ft_lb_floating_hint') }}</span>
                            </div>
                        </div>
                        <div class="ft-page-form__floating-actions">
                            <a href="{{ url('admin/formtools/modelEdit?id=' . $model->id) }}" class="btn btn-default">{{ getTranslateByKey('ft_lb_back_model') }}</a>
                            <button type="button" class="btn btn-default js-layout-preview">{{ getTranslateByKey('ft_lb_refresh_preview') }}</button>
                            <button type="button" class="btn btn-default js-preview-dialog">{{ getTranslateByKey('ft_lb_view_preview') }}</button>
                            <button type="submit" class="btn btn-primary">{{ getTranslateByKey('ft_lb_save_layout') }}</button>
                        </div>
                    </div>
                </form>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>

@include(moduleAdminTemplate($moduleName)."public.js")
<script>
    (function ($) {
        var $layoutSchema = $('#layoutSchema');
        var $layoutTree = $('#layoutTree');
        var $layoutPreview = $('#layoutPreview');
        var $layoutPreviewContent = $('#layoutPreviewContent');
        var $layoutPreviewShell = $('#layoutPreviewShell');
        var $nodeInspector = $('#nodeInspector');
        var $themeInspector = $('#themeInspector');
        var $layoutMetaInspector = $('#layoutMetaInspector');
        var $jsonStatus = $('#layoutJsonStatus');
        var $selectionBar = $('#layoutBuilderSelectionBar');
        var $selectionBreadcrumb = $('#layoutBuilderSelectionBreadcrumb');
        var $inspectorSummary = $('#layoutBuilderInspectorSummary');
        var $inspectorOutline = $('#layoutBuilderInspectorOutline');
        var $inspectorStickyActions = $('#layoutBuilderInspectorStickyActions');
        var $catalogTitle = $('#layoutBuilderCatalogTitle');
        var $catalogMeta = $('#layoutBuilderCatalogMeta');
        var $catalogTarget = $('#layoutBuilderCatalogTarget');
        var $currentTargetText = $('#layoutCurrentTargetText');
        var previewTimer = null;
        var syncTimer = null;
        var uid = 0;
        var catalogGroupMeta = {!! json_encode($catalogGroupMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

        var state = {
            schema: null,
            selectedPath: null,
            previewMode: 'desktop',
            activeCatalogGroup: {!! json_encode($defaultCatalogGroup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
            activeInspectorTab: 'content'
        };

        function deepClone(value) {
            return JSON.parse(JSON.stringify(value));
        }

        function nextNodeId() {
            uid += 1;
            return 'ft-node-' + uid;
        }

        function normalizeTheme(theme) {
            var defaults = {
                primary: '#2563eb',
                accent: '#0f172a',
                surface: '#ffffff',
                text: '#0f172a',
                border: '#e2e8f0',
                radiusCard: '24px'
            };
            theme = theme && typeof theme === 'object' ? theme : {};
            Object.keys(defaults).forEach(function (key) {
                if (typeof theme[key] !== 'string' || theme[key].trim() === '') {
                    theme[key] = defaults[key];
                }
            });
            return theme;
        }

        function normalizeNode(node) {
            if (!node || typeof node !== 'object') {
                return null;
            }
            node.type = (node.type || 'section').toString();
            node.props = node.props && typeof node.props === 'object' ? node.props : {};
            node.style = node.style && typeof node.style === 'object' ? node.style : {};
            node.motion = node.motion && typeof node.motion === 'object' ? node.motion : {};
            if (!node.id) {
                node.id = nextNodeId();
            }
            var childKey = getChildKey(node);
            if (childKey) {
                var children = Array.isArray(node[childKey]) ? node[childKey] : [];
                node[childKey] = children.map(function (child) {
                    return normalizeNode(child);
                }).filter(Boolean);
            }
            return node;
        }

        function normalizeSchema(raw) {
            if (!raw || typeof raw !== 'object') {
                raw = {};
            }
            if (!Array.isArray(raw.sections)) {
                raw.sections = Array.isArray(raw.blocks) ? raw.blocks : [];
            }
            raw.theme = normalizeTheme(raw.theme);
            raw.sections = raw.sections.map(function (node) {
                return normalizeNode(node);
            }).filter(Boolean);
            return raw;
        }

        function readSchemaFromTextarea() {
            try {
                return normalizeSchema(JSON.parse($layoutSchema.val() || '{}'));
            } catch (err) {
                return null;
            }
        }

        function writeSchemaToTextarea() {
            $layoutSchema.val(JSON.stringify(state.schema, null, 2));
            setJsonStatus(true);
        }

        function setJsonStatus(isValid, message) {
            $jsonStatus
                .toggleClass('is-valid', !!isValid)
                .toggleClass('is-invalid', !isValid)
                .text(message || (isValid ? '{{ getTranslateByKey('ft_lb_json_ok') }}' : '{{ getTranslateByKey('ft_lb_json_error') }}'));
            $layoutSchema.toggleClass('is-invalid', !isValid);
        }

        function pathToString(path) {
            return (path || []).join('.');
        }

        function parsePath(pathString) {
            if (!pathString) {
                return [];
            }
            return pathString.split('.').map(function (part) {
                return /^\d+$/.test(part) ? parseInt(part, 10) : part;
            });
        }

        function getByPath(target, path) {
            var current = target;
            for (var i = 0; i < path.length; i += 1) {
                if (current == null) {
                    return null;
                }
                current = current[path[i]];
            }
            return current;
        }

        function getNodeByPath(path) {
            return getByPath(state.schema, path || []);
        }

        function findPathByNodeId(nodes, collectionPath, nodeId) {
            if (!nodes || !nodes.length || !nodeId) {
                return null;
            }
            for (var i = 0; i < nodes.length; i += 1) {
                var node = nodes[i];
                var nodePath = collectionPath.concat([i]);
                if (node && node.id === nodeId) {
                    return nodePath;
                }
                var childKey = getChildKey(node);
                if (childKey && Array.isArray(node[childKey])) {
                    var match = findPathByNodeId(node[childKey], nodePath.concat([childKey]), nodeId);
                    if (match) {
                        return match;
                    }
                }
            }
            return null;
        }

        function getCollectionInfo(path) {
            if (!path || !path.length) {
                return null;
            }
            return {
                parentPath: path.slice(0, -1),
                index: path[path.length - 1]
            };
        }

        function getChildKey(node) {
            if (!node) {
                return '';
            }
            if (node.type === 'section' || node.type === 'row' || Object.prototype.hasOwnProperty.call(node, 'children')) {
                return 'children';
            }
            if (node.type === 'column' || Object.prototype.hasOwnProperty.call(node, 'blocks')) {
                return 'blocks';
            }
            return '';
        }

        function canHaveChildren(node) {
            return getChildKey(node) !== '';
        }

        function ensureChildCollection(node) {
            var childKey = getChildKey(node);
            if (!childKey) {
                return null;
            }
            if (!Array.isArray(node[childKey])) {
                node[childKey] = [];
            }
            return {
                key: childKey,
                list: node[childKey]
            };
        }

        function selectPath(path) {
            state.selectedPath = path || null;
            renderWorkspace();
        }

        function ensureSelection() {
            if (!state.schema.sections.length) {
                state.selectedPath = null;
                return;
            }
            var selected = getNodeByPath(state.selectedPath || []);
            if (!selected) {
                state.selectedPath = ['sections', 0];
            }
        }

        function setSchema(nextSchema, selectedPath) {
            state.schema = normalizeSchema(nextSchema);
            ensureSelection();
            if (selectedPath) {
                state.selectedPath = selectedPath;
            }
            writeSchemaToTextarea();
            renderWorkspace();
            queuePreview();
        }

        function queuePreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(previewSchema, 250);
        }

        function previewSchema() {
            $.post('{{ url('admin/formtools/modelLayoutPreview') }}', {
                _token: '{{ csrf_token() }}',
                id: '{{ $model->id }}',
                layout_schema: $layoutSchema.val()
            }).done(function (res) {
                if (res && res.status === 200) {
                    ($layoutPreviewContent.length ? $layoutPreviewContent : $layoutPreview).html(res.data.html || '<div class="mx-page-empty">' + '{{ getTranslateByKey('ft_lb_no_blocks') }}' + '</div>');
                    syncPreviewSelection(false);
                    syncPreviewFloatbarToSelection();
                    return;
                }
                layer.msg((res && res.msg) || '{{ getTranslateByKey('ft_lb_preview_failed') }}');
            }).fail(function () {
                layer.msg('{{ getTranslateByKey('ft_lb_preview_failed') }}');
            });
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function nodeTitle(node) {
            var props = node.props || {};
            if (props.text) {
                return props.text;
            }
            if (props.title) {
                return props.title;
            }
            if (props.label) {
                return props.label;
            }
            if (props.anchor) {
                return '#' + props.anchor;
            }
            if (props.model) {
                return props.model;
            }
            return '{{ getTranslateByKey('ft_lb_unnamed_node') }}';
        }

        function nodeSummary(node) {
            var props = node.props || {};
            switch (node.type) {
                case 'section':
                    return '{{ getTranslateByKey('ft_lb_ns_section') }}';
                case 'row':
                    return '{{ getTranslateByKey('ft_lb_ns_row') }}';
                case 'column':
                    return '{{ getTranslateByKey('ft_lb_ns_column', 'admin', ['span' => ':SPAN:']) }}'.replace(':SPAN:', (props.span || 12));
                case 'heading':
                    return '{{ getTranslateByKey('ft_lb_ns_heading') }}' + nodeTitle(node);
                case 'text':
                    return '{{ getTranslateByKey('ft_lb_ns_text') }}' + nodeTitle(node);
                case 'button':
                    return '{{ getTranslateByKey('ft_lb_ns_button') }}' + (props.text || '{{ getTranslateByKey('ft_lb_word_button') }}');
                case 'image':
                    return '{{ getTranslateByKey('ft_lb_ns_image') }}' + ((props.alt || '').trim() || '{{ getTranslateByKey('common_image') }}');
                case 'model_list':
                    return '{{ getTranslateByKey('ft_lb_ns_model_list', 'admin', ['model' => ':M:', 'template' => ':T:']) }}'.replace(':M:', (props.model || '[model]')).replace(':T:', (props.template || 'card'));
                case 'model_detail':
                    return '{{ getTranslateByKey('ft_lb_ns_model_detail', 'admin', ['model' => ':M:']) }}'.replace(':M:', (props.model || '[model]'));
                case 'cta':
                    return '{{ getTranslateByKey('ft_lb_ns_cta') }}';
                case 'stats':
                    return '{{ getTranslateByKey('ft_lb_ns_stats') }}';
                case 'faq':
                    return '{{ getTranslateByKey('ft_lb_ns_faq') }}';
                case 'gallery':
                    return '{{ getTranslateByKey('ft_lb_ns_gallery') }}';
                case 'carousel':
                    return '{{ getTranslateByKey('ft_lb_ns_carousel') }}';
                case 'html':
                    return '{{ getTranslateByKey('ft_lb_ns_html') }}';
                default:
                    return '{{ getTranslateByKey('ft_lb_ns_default') }}' + node.type;
            }
        }

        function countNodes(nodes) {
            var total = 0;
            (nodes || []).forEach(function (node) {
                total += 1;
                var childKey = getChildKey(node);
                if (childKey && Array.isArray(node[childKey])) {
                    total += countNodes(node[childKey]);
                }
            });
            return total;
        }

        function getSelectionTargetText() {
            var selectedNode = getNodeByPath(state.selectedPath || []);
            if (selectedNode && canHaveChildren(selectedNode)) {
                return '{{ getTranslateByKey('ft_lb_target_container', 'admin', ['name' => ':N:', 'type' => ':T:']) }}'.replace(':N:', nodeTitle(selectedNode)).replace(':T:', selectedNode.type);
            }
            if (selectedNode) {
                return '{{ getTranslateByKey('ft_lb_target_no_children') }}';
            }
            return '{{ getTranslateByKey('ft_lb_root_sections') }}';
        }

        function getNodeAncestors(path) {
            var items = [];
            var currentPath = [];
            (path || []).forEach(function (part) {
                currentPath.push(part);
                if (typeof part === 'number') {
                    var node = getNodeByPath(currentPath);
                    if (node) {
                        items.push({
                            path: pathToString(currentPath),
                            title: nodeTitle(node),
                            type: node.type
                        });
                    }
                }
            });
            return items;
        }

        function renderNodePreview(node, childNodes) {
            var type = node.type;
            var props = node.props || {};
            var title = nodeTitle(node);
            var childrenCount = (childNodes || []).length;

            function toText(value) {
                if (value == null) {
                    return '';
                }
                return String(value);
            }

            function clamp(value, max) {
                var text = toText(value).trim();
                if (!max || text.length <= max) {
                    return text;
                }
                return text.slice(0, max - 1) + '…';
            }

            function safe(value, max) {
                return escapeHtml(clamp(value, max));
            }

            function wrap(innerHtml) {
                return '<div class="ft-builder__tree-preview"><div class="ft-builder__tree-preview-inner">' + innerHtml + '</div></div>';
            }

            if (type === 'divider') {
                return wrap('<div class="ft-builder__mini-preview__divider"></div>');
            }

            if (type === 'heading') {
                var level = toText(props.level || 'h2').toLowerCase();
                if (['h1', 'h2', 'h3'].indexOf(level) === -1) {
                    level = 'h2';
                }
                return wrap(
                    '<div class="ft-builder__mini-preview__heading is-' + escapeHtml(level) + '">' + safe(props.text || title || '{{ getTranslateByKey('ft_lb_word_heading') }}', 120) + '</div>'
                    + '<div class="ft-builder__mini-preview__muted">' + safe(nodeSummary(node), 120) + '</div>'
                );
            }

            if (type === 'text') {
                return wrap('<div class="ft-builder__mini-preview__text">' + safe(props.text || title || '{{ getTranslateByKey('ft_lb_word_body') }}', 320) + '</div>');
            }

            if (type === 'button') {
                var variant = toText(props.variant || 'solid').toLowerCase();
                var variantClass = variant === 'outline' ? ' is-outline' : ' is-primary';
                if (variant === 'ghost') {
                    variantClass = '';
                }
                return wrap(
                    '<div class="ft-builder__mini-preview__actions">'
                    + '<span class="ft-builder__mini-preview__btn' + variantClass + '">' + safe(props.text || title || '{{ getTranslateByKey('ft_lb_word_button') }}', 40) + '</span>'
                    + '</div>'
                    + (props.href ? '<div class="ft-builder__mini-preview__muted">' + safe(props.href, 120) + '</div>' : '')
                );
            }

            if (type === 'image') {
                var imgLabel = (props.alt || '').trim() || '{{ getTranslateByKey('common_image') }}';
                return wrap(
                    '<div class="ft-builder__mini-preview__media"><span class="ft-builder__mini-preview__media-badge">IMAGE</span></div>'
                    + '<div class="ft-builder__mini-preview__muted">' + safe(imgLabel, 80) + (props.src ? ' · ' + safe(props.src, 120) : '') + '</div>'
                );
            }

            if (type === 'carousel') {
                return wrap(
                    '<div class="ft-builder__mini-preview__media"><span class="ft-builder__mini-preview__media-badge">CAROUSEL</span></div>'
                    + '<div class="ft-builder__mini-preview__actions">'
                    + '<span class="ft-builder__mini-preview__btn is-outline">' + '{{ getTranslateByKey('ft_lb_prev') }}' + '</span>'
                    + '<span class="ft-builder__mini-preview__btn is-primary">' + '{{ getTranslateByKey('ft_lb_next') }}' + '</span>'
                    + '</div>'
                );
            }

            if (type === 'gallery') {
                return wrap(
                    '<div class="ft-builder__mini-preview__grid">'
                    + '<div class="ft-builder__mini-preview__media" style="height: 86px;"><span class="ft-builder__mini-preview__media-badge">1</span></div>'
                    + '<div class="ft-builder__mini-preview__media" style="height: 86px;"><span class="ft-builder__mini-preview__media-badge">2</span></div>'
                    + '<div class="ft-builder__mini-preview__media" style="height: 86px;"><span class="ft-builder__mini-preview__media-badge">3</span></div>'
                    + '</div>'
                );
            }

            if (type === 'model_list') {
                var listTitle = (props.title || '').trim() || '{{ getTranslateByKey('ft_lb_word_list') }}';
                var template = toText(props.template || 'card').toLowerCase();
                if (template === 'table') {
                    return wrap(
                        '<div class="ft-builder__mini-preview__kicker">MODEL LIST</div>'
                        + '<div class="ft-builder__mini-preview__title">' + safe(listTitle, 80) + '</div>'
                        + '<div class="ft-builder__mini-preview__table">'
                        + '<div class="ft-builder__mini-preview__table-row">'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '</div>'
                        + '<div class="ft-builder__mini-preview__table-row">'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '<div class="ft-builder__skeleton-line"></div>'
                        + '</div>'
                        + '</div>'
                        + '<div class="ft-builder__mini-preview__muted">' + safe(props.model || '[model]', 80) + '</div>'
                    );
                }
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">MODEL LIST</div>'
                    + '<div class="ft-builder__mini-preview__title">' + safe(listTitle, 80) + '</div>'
                    + '<div class="ft-builder__mini-preview__grid">'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__skeleton-line"></div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__skeleton-line"></div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__skeleton-line"></div></div>'
                    + '</div>'
                    + '<div class="ft-builder__mini-preview__muted">' + safe(props.model || '[model]', 80) + '</div>'
                );
            }

            if (type === 'model_detail') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">MODEL DETAIL</div>'
                    + '<div class="ft-builder__mini-preview__media"><span class="ft-builder__mini-preview__media-badge">COVER</span></div>'
                    + '<div class="ft-builder__mini-preview__title">' + safe(props.title || title || '{{ getTranslateByKey('ft_lb_word_detail') }}', 80) + '</div>'
                    + '<div class="ft-builder__mini-preview__text">' + safe(props.subtitle || nodeSummary(node), 240) + '</div>'
                    + '<div class="ft-builder__mini-preview__muted">' + safe(props.model || '[model]', 80) + '</div>'
                );
            }

            if (type === 'cta') {
                return wrap(
                    (props.eyebrow ? '<div class="ft-builder__mini-preview__kicker">' + safe(props.eyebrow, 50) + '</div>' : '<div class="ft-builder__mini-preview__kicker">CTA</div>')
                    + '<div class="ft-builder__mini-preview__title">' + safe(props.title || title || '{{ getTranslateByKey('ft_lb_word_cta') }}', 90) + '</div>'
                    + '<div class="ft-builder__mini-preview__text">' + safe(props.description || nodeSummary(node), 240) + '</div>'
                    + '<div class="ft-builder__mini-preview__actions">'
                    + '<span class="ft-builder__mini-preview__btn is-primary">' + safe(props.primaryText || '{{ getTranslateByKey('ft_lb_primary_btn') }}', 40) + '</span>'
                    + '<span class="ft-builder__mini-preview__btn is-outline">' + safe(props.secondaryText || '{{ getTranslateByKey('ft_lb_secondary_btn') }}', 40) + '</span>'
                    + '</div>'
                );
            }

            if (type === 'stats') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">STATS</div>'
                    + '<div class="ft-builder__mini-preview__title">' + safe(props.title || title || '{{ getTranslateByKey('ft_lb_word_stats') }}', 90) + '</div>'
                    + '<div class="ft-builder__mini-preview__grid">'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__mini-preview__muted">' + '{{ getTranslateByKey('ft_lb_word_metric') }}' + '</div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__mini-preview__muted">' + '{{ getTranslateByKey('ft_lb_word_metric') }}' + '</div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__mini-preview__muted">' + '{{ getTranslateByKey('ft_lb_word_metric') }}' + '</div></div>'
                    + '</div>'
                );
            }

            if (type === 'faq') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">FAQ</div>'
                    + '<div class="ft-builder__mini-preview__title">' + safe(props.title || title || '{{ getTranslateByKey('ft_lb_word_faq') }}', 90) + '</div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg" style="width: 70%;"></div><div class="ft-builder__skeleton-line" style="width: 90%;"></div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg" style="width: 64%;"></div><div class="ft-builder__skeleton-line" style="width: 86%;"></div></div>'
                );
            }

            if (type === 'html') {
                var html = clamp(props.html || '', 260);
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">HTML</div>'
                    + '<div class="ft-builder__mini-preview__text">' + safe(html || '<div>...</div>', 260) + '</div>'
                );
            }

            if (type === 'row') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">ROW</div>'
                    + '<div class="ft-builder__mini-preview__grid">'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line"></div><div class="ft-builder__skeleton-line" style="width: 78%;"></div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line"></div><div class="ft-builder__skeleton-line" style="width: 62%;"></div></div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line"></div><div class="ft-builder__skeleton-line" style="width: 66%;"></div></div>'
                    + '</div>'
                    + (childrenCount ? '<div class="ft-builder__mini-preview__muted">' + childrenCount + '{{ getTranslateByKey('ft_lb_child_nodes_suffix') }}' + '</div>' : '')
                );
            }

            if (type === 'column') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">COLUMN</div>'
                    + '<div class="ft-builder__mini-preview__card"><div class="ft-builder__skeleton-line is-lg"></div><div class="ft-builder__skeleton-line" style="width: 72%;"></div></div>'
                    + (props.span ? '<div class="ft-builder__mini-preview__muted">' + '{{ getTranslateByKey('ft_lb_span_prefix') }}' + safe(props.span, 12) + '/12</div>' : '')
                );
            }

            if (type === 'section') {
                return wrap(
                    '<div class="ft-builder__mini-preview__kicker">SECTION</div>'
                    + '<div class="ft-builder__mini-preview__title">' + safe(props.title || title || '{{ getTranslateByKey('ft_lb_word_section') }}', 90) + '</div>'
                    + '<div class="ft-builder__mini-preview__text">' + safe(props.description || nodeSummary(node), 240) + '</div>'
                    + '<div class="ft-builder__mini-preview__actions">'
                    + '<span class="ft-builder__mini-preview__btn is-primary">' + '{{ getTranslateByKey('ft_lb_primary_btn') }}' + '</span>'
                    + '<span class="ft-builder__mini-preview__btn is-outline">' + '{{ getTranslateByKey('ft_lb_secondary_btn') }}' + '</span>''
                    + '</div>'
                    + (childrenCount ? '<div class="ft-builder__mini-preview__muted">' + childrenCount + '{{ getTranslateByKey('ft_lb_child_nodes_suffix') }}' + '</div>' : '')
                );
            }

            return wrap(
                '<div class="ft-builder__mini-preview__kicker">' + safe(type, 40) + '</div>'
                + '<div class="ft-builder__mini-preview__title">' + safe(title || node.type, 90) + '</div>'
                + '<div class="ft-builder__mini-preview__text">' + safe(nodeSummary(node), 240) + '</div>'
                + (childrenCount ? '<div class="ft-builder__mini-preview__muted">' + childrenCount + '{{ getTranslateByKey('ft_lb_child_nodes_suffix') }}' + '</div>' : '')
            );
        }

        function renderTreeNodes(nodes, collectionPath) {
            if (!nodes.length) {
                return '<div class="ft-builder__empty">' + '{{ getTranslateByKey('ft_lb_empty_canvas') }}' + '</div>';
            }

            return '<div class="ft-builder__tree-group">' + nodes.map(function (node, index) {
                var nodePath = collectionPath.concat([index]);
                var nodePathString = pathToString(nodePath);
                var childKey = getChildKey(node);
                var childNodes = childKey && Array.isArray(node[childKey]) ? node[childKey] : [];
                var selected = nodePathString === pathToString(state.selectedPath || []);

                return '<div class="ft-builder__tree-item">'
                    + '<div class="ft-builder__tree-card js-node-select' + (selected ? ' is-selected' : '') + '" data-path="' + escapeHtml(nodePathString) + '">'
                    + '<div class="ft-builder__tree-head">'
                    + '<div>'
                    + '<span class="ft-builder__tree-type">' + escapeHtml(node.type) + '</span>'
                    + '<div class="ft-builder__tree-title">' + escapeHtml(nodeTitle(node)) + '</div>'
                    + '<div class="ft-builder__tree-desc">' + escapeHtml(nodeSummary(node)) + '</div>'
                    + '</div>'
                    + '<div class="ft-builder__tree-buttons">'
                    + '<button type="button" class="ft-builder__mini-btn js-node-up" title="' + '{{ getTranslateByKey('ft_field_move_up') }}' + '" data-path="' + escapeHtml(nodePathString) + '">↑</button>'
                    + '<button type="button" class="ft-builder__mini-btn js-node-down" title="' + '{{ getTranslateByKey('ft_field_move_down') }}' + '" data-path="' + escapeHtml(nodePathString) + '">↓</button>'
                    + '<button type="button" class="ft-builder__mini-btn js-node-clone" title="' + '{{ getTranslateByKey('ft_lb_copy') }}' + '" data-path="' + escapeHtml(nodePathString) + '">⧉</button>'
                    + '<button type="button" class="ft-builder__mini-btn ft-builder__mini-btn--danger js-node-delete" title="' + '{{ getTranslateByKey('common_delete') }}' + '" data-path="' + escapeHtml(nodePathString) + '">✕</button>'
                    + '</div>'
                    + '</div>'
                    + renderNodePreview(node, childNodes)
                    + '</div>'
                    + (childNodes.length ? '<div class="ft-builder__tree-children">' + renderTreeNodes(childNodes, nodePath.concat([childKey])) + '</div>' : '')
                    + '</div>';
            }).join('') + '</div>';
        }

        function buildField(label, scope, key, value, type, options, placeholder) {
            var inputHtml = '';
            var safeValue = value == null ? '' : value;

            if (type === 'textarea') {
                inputHtml = '<textarea class="ft-builder__field-textarea js-field" data-scope="' + escapeHtml(scope) + '" data-key="' + escapeHtml(key) + '" placeholder="' + escapeHtml(placeholder || '') + '">' + escapeHtml(safeValue) + '</textarea>';
            } else if (type === 'select') {
                inputHtml = '<select class="ft-builder__field-select js-field" data-scope="' + escapeHtml(scope) + '" data-key="' + escapeHtml(key) + '">'
                    + (options || []).map(function (option) {
                        var selected = String(option.value) === String(safeValue) ? ' selected' : '';
                        return '<option value="' + escapeHtml(option.value) + '"' + selected + '>' + escapeHtml(option.label) + '</option>';
                    }).join('')
                    + '</select>';
            } else {
                inputHtml = '<input type="' + escapeHtml(type || 'text') + '" class="ft-builder__field-input js-field" data-scope="' + escapeHtml(scope) + '" data-key="' + escapeHtml(key) + '" value="' + escapeHtml(safeValue) + '" placeholder="' + escapeHtml(placeholder || '') + '">';
            }

            return '<div class="ft-builder__field"><label>' + escapeHtml(label) + '</label>' + inputHtml + '</div>';
        }

        function setCatalogGroup(group) {
            if (!group) {
                return;
            }
            state.activeCatalogGroup = group;
            $('.js-catalog-group').removeClass('is-active').filter('[data-group="' + group + '"]').addClass('is-active');
            $('[data-catalog-group-section]').removeClass('is-visible').hide();
            $('[data-catalog-group-section="' + group + '"]').addClass('is-visible').show();
            $catalogTitle.text(group);
            $catalogMeta.text(catalogGroupMeta[group] || '');
            renderCatalogTarget();
        }

        function renderCatalogTarget() {
            var targetText = getSelectionTargetText();
            $catalogTarget.html('{{ getTranslateByKey('ft_lb_insert_target_pre') }}<code>' + escapeHtml(targetText) + '</code>{{ getTranslateByKey('ft_lb_insert_target_post') }}');
            $currentTargetText.text(targetText);
        }

        function renderThemeInspector() {
            var theme = state.schema.theme || {};
            $themeInspector.html([
                buildField('{{ getTranslateByKey('ft_lb_theme_primary') }}', 'theme', 'primary', theme.primary || '', 'color'),
                buildField('{{ getTranslateByKey('ft_lb_theme_accent') }}', 'theme', 'accent', theme.accent || '', 'color'),
                buildField('{{ getTranslateByKey('ft_lb_theme_surface') }}', 'theme', 'surface', theme.surface || '', 'color'),
                buildField('{{ getTranslateByKey('ft_lb_theme_text') }}', 'theme', 'text', theme.text || '', 'color'),
                buildField('{{ getTranslateByKey('ft_lb_theme_border') }}', 'theme', 'border', theme.border || '', 'color'),
                buildField('{{ getTranslateByKey('ft_lb_theme_radius') }}', 'theme', 'radiusCard', theme.radiusCard || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_radius') }}')
            ].join(''));
        }

        function commonFieldSet(node) {
            var props = node.props || {};
            var style = node.style || {};
            return [
                '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_common_props') }}' + '</div>',
                '<div class="ft-builder__inspector-grid">',
                buildField('{{ getTranslateByKey('ft_lb_anchor') }}', 'props', 'anchor', props.anchor || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_anchor') }}'),
                buildField('{{ getTranslateByKey('ft_lb_class_name') }}', 'props', 'className', props.className || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_class') }}'),
                buildField('{{ getTranslateByKey('ft_lb_background') }}', 'style', 'background', style.background || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_bg') }}'),
                buildField('{{ getTranslateByKey('ft_lb_text_color') }}', 'style', 'color', style.color || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_color') }}'),
                buildField('{{ getTranslateByKey('ft_lb_padding') }}', 'style', 'padding', style.padding || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_padding') }}'),
                buildField('{{ getTranslateByKey('ft_lb_margin') }}', 'style', 'margin', style.margin || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_margin') }}'),
                buildField('{{ getTranslateByKey('ft_lb_border_radius') }}', 'style', 'borderRadius', style.borderRadius || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_radius') }}'),
                buildField('{{ getTranslateByKey('ft_lb_border') }}', 'style', 'border', style.border || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_border') }}'),
                buildField('{{ getTranslateByKey('ft_lb_shadow') }}', 'style', 'boxShadow', style.boxShadow || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_shadow') }}'),
                buildField('{{ getTranslateByKey('ft_lb_gap') }}', 'style', 'gap', style.gap || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_gap') }}'),
                buildField('{{ getTranslateByKey('ft_lb_align') }}', 'style', 'textAlign', style.textAlign || '', 'select', [
                    {value: '', label: '{{ getTranslateByKey('ft_default') }}'},
                    {value: 'left', label: '{{ getTranslateByKey('ft_lb_align_left') }}'},
                    {value: 'center', label: '{{ getTranslateByKey('ft_position_center') }}'},
                    {value: 'right', label: '{{ getTranslateByKey('ft_lb_align_right') }}'}
                ]),
                buildField('{{ getTranslateByKey('ft_lb_min_height') }}', 'style', 'minHeight', style.minHeight || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_min_height') }}'),
                '</div>'
            ].join('');
        }

        function specificFieldSet(node) {
            var props = node.props || {};
            if (node.type === 'section') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_section') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_content_width') }}', 'props', 'contentWidth', props.contentWidth || 'contained', 'select', [
                        {value: 'contained', label: '{{ getTranslateByKey('ft_lb_contained') }}'},
                        {value: 'full', label: '{{ getTranslateByKey('ft_lb_full') }}'}
                    ]),
                    buildField('{{ getTranslateByKey('ft_lb_inner_width') }}', 'props', 'innerWidth', props.innerWidth || '', 'text', null, '{{ getTranslateByKey('ft_lb_ph_inner_width') }}'),
                    '</div>'
                ].join('');
            }

            if (node.type === 'column') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_column') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_span') }}', 'props', 'span', props.span || 6, 'number'),
                    '</div>'
                ].join('');
            }

            if (node.type === 'heading') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_heading') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_heading_level') }}', 'props', 'level', props.level || 'h2', 'select', [
                        {value: 'h1', label: 'H1'},
                        {value: 'h2', label: 'H2'},
                        {value: 'h3', label: 'H3'},
                        {value: 'h4', label: 'H4'},
                        {value: 'h5', label: 'H5'},
                        {value: 'h6', label: 'H6'}
                    ]),
                    buildField('{{ getTranslateByKey('ft_lb_heading_text') }}', 'props', 'text', props.text || '', 'textarea'),
                    '</div>'
                ].join('');
            }

            if (node.type === 'text') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_text') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_text_content') }}', 'props', 'text', props.text || '', 'textarea'),
                    '</div>'
                ].join('');
            }

            if (node.type === 'button') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_button') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_btn_text') }}', 'props', 'text', props.text || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_btn_href') }}', 'props', 'href', props.href || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_btn_target') }}', 'props', 'target', props.target || '', 'select', [
                        {value: '', label: '{{ getTranslateByKey('ft_lb_target_self') }}'},
                        {value: '_blank', label: '{{ getTranslateByKey('ft_lb_target_blank') }}'}
                    ]),
                    buildField('{{ getTranslateByKey('ft_lb_align_way') }}', 'props', 'align', props.align || 'left', 'select', [
                        {value: 'left', label: '{{ getTranslateByKey('ft_lb_align_left') }}'},
                        {value: 'center', label: '{{ getTranslateByKey('ft_position_center') }}'},
                        {value: 'right', label: '{{ getTranslateByKey('ft_lb_align_right') }}'},
                        {value: 'full', label: '{{ getTranslateByKey('ft_lb_align_full') }}'}
                    ]),
                    buildField('{{ getTranslateByKey('ft_lb_btn_variant') }}', 'props', 'variant', props.variant || 'solid', 'select', [
                        {value: 'solid', label: '{{ getTranslateByKey('ft_lb_primary_btn') }}'},
                        {value: 'outline', label: '{{ getTranslateByKey('ft_lb_outline_btn') }}'},
                        {value: 'ghost', label: '{{ getTranslateByKey('ft_lb_ghost_btn') }}'}
                    ]),
                    '</div>'
                ].join('');
            }

            if (node.type === 'image') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_image') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_img_src') }}', 'props', 'src', props.src || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_img_alt') }}', 'props', 'alt', props.alt || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_align_way') }}', 'props', 'align', props.align || 'left', 'select', [
                        {value: 'left', label: '{{ getTranslateByKey('ft_lb_align_left') }}'},
                        {value: 'center', label: '{{ getTranslateByKey('ft_position_center') }}'},
                        {value: 'right', label: '{{ getTranslateByKey('ft_lb_align_right') }}'}
                    ]),
                    '</div>'
                ].join('');
            }

            if (node.type === 'html') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_html') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_html_content') }}', 'props', 'html', props.html || '', 'textarea'),
                    '</div>'
                ].join('');
            }

            if (node.type === 'model_list') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_model_list') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_label_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_model_access') }}', 'props', 'model', props.model || '{{ $model->access_identification }}', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_limit') }}', 'props', 'limit', props.limit || 12, 'number'),
                    buildField('{{ getTranslateByKey('ft_lb_list_tpl') }}', 'props', 'template', props.template || 'card', 'select', [
                        {value: 'card', label: '{{ getTranslateByKey('ft_lb_tpl_card') }}'},
                        {value: 'list', label: '{{ getTranslateByKey('ft_lb_tpl_list') }}'}
                    ]),
                    '</div>'
                ].join('');
            }

            if (node.type === 'model_detail') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_model_detail') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_label_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_model_access') }}', 'props', 'model', props.model || '{{ $model->access_identification }}', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_detail_tpl') }}', 'props', 'template', props.template || 'detail', 'select', [
                        {value: 'detail', label: '{{ getTranslateByKey('ft_lb_tpl_detail') }}'}
                    ]),
                    '</div>'
                ].join('');
            }

            if (node.type === 'cta') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_cta') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_eyebrow') }}', 'props', 'eyebrow', props.eyebrow || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_main_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_description') }}', 'props', 'description', props.description || '', 'textarea'),
                    buildField('{{ getTranslateByKey('ft_lb_primary_text') }}', 'props', 'primaryText', props.primaryText || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_primary_href') }}', 'props', 'primaryHref', props.primaryHref || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_secondary_text') }}', 'props', 'secondaryText', props.secondaryText || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_secondary_href') }}', 'props', 'secondaryHref', props.secondaryHref || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_content_align') }}', 'props', 'align', props.align || 'left', 'select', [
                        {value: 'left', label: '{{ getTranslateByKey('ft_lb_align_left') }}'},
                        {value: 'center', label: '{{ getTranslateByKey('ft_position_center') }}'}
                    ]),
                    '</div>'
                ].join('');
            }

            if (node.type === 'stats') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_stats') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_label_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_intro') }}', 'props', 'intro', props.intro || '', 'textarea'),
                    buildField('{{ getTranslateByKey('ft_lb_columns') }}', 'props', 'columns', props.columns || 4, 'number'),
                    '</div>',
                    '<div class="ft-builder__muted" style="margin-top:10px;">' + '{{ getTranslateByKey('ft_lb_stats_note') }}' + '</div>'
                ].join('');
            }

            if (node.type === 'faq') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_faq') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_label_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_intro') }}', 'props', 'intro', props.intro || '', 'textarea'),
                    buildField('{{ getTranslateByKey('ft_lb_columns') }}', 'props', 'columns', props.columns || 1, 'number'),
                    '</div>',
                    '<div class="ft-builder__muted" style="margin-top:10px;">' + '{{ getTranslateByKey('ft_lb_faq_note') }}' + '</div>'
                ].join('');
            }

            if (node.type === 'gallery') {
                return [
                    '<div class="ft-builder__section-title">' + '{{ getTranslateByKey('ft_lb_cfg_gallery') }}' + '</div>',
                    '<div class="ft-builder__inspector-grid">',
                    buildField('{{ getTranslateByKey('ft_lb_label_title') }}', 'props', 'title', props.title || '', 'text'),
                    buildField('{{ getTranslateByKey('ft_lb_intro') }}', 'props', 'subtitle', props.subtitle || '', 'textarea'),
                    buildField('{{ getTranslateByKey('ft_lb_columns') }}', 'props', 'columns', props.columns || 3, 'number'),
                    buildField('{{ getTranslateByKey('ft_lb_gap') }}', 'props', 'gap', props.gap || '18px', 'text'),
                    '</div>'
                ].join('');
            }

            return '<div class="ft-builder__empty">' + '{{ getTranslateByKey('ft_lb_not_visual') }}' + '</div>';
        }

        function buildNodeActionButtons(node, isCompact) {
            if (!node) {
                return '';
            }

            var sizeClass = isCompact ? ' btn-xs' : '';
            var actions = [
                '<button type="button" class="btn btn-default' + sizeClass + ' js-inspector-clone">' + '{{ getTranslateByKey('ft_lb_clone_node') }}' + '</button>',
                '<button type="button" class="btn btn-default' + sizeClass + ' js-inspector-up">' + '{{ getTranslateByKey('ft_field_move_up') }}' + '</button>',
                '<button type="button" class="btn btn-default' + sizeClass + ' js-inspector-down">' + '{{ getTranslateByKey('ft_field_move_down') }}' + '</button>'
            ];

            if (canHaveChildren(node)) {
                actions.push('<button type="button" class="btn btn-default' + sizeClass + ' js-add-child-default">' + '{{ getTranslateByKey('ft_lb_add_heading') }}' + '</button>');
            }

            actions.push('<button type="button" class="btn btn-default' + sizeClass + ' js-inspector-preview">' + '{{ getTranslateByKey('ft_lb_view_preview') }}' + '</button>');
            actions.push('<button type="button" class="btn btn-danger' + sizeClass + ' js-inspector-delete">' + '{{ getTranslateByKey('ft_lb_delete_node') }}' + '</button>');

            return actions.join('');
        }

        function renderSelectionPanels(node) {
            if (!node) {
                $selectionBar.removeClass('is-visible').html('');
                $selectionBreadcrumb.removeClass('is-visible').html('');
                $inspectorSummary.removeClass('is-visible').html('');
                $inspectorStickyActions.removeClass('is-visible').html('');
                $inspectorOutline.removeClass('is-visible').html('');
                renderCatalogTarget();
                return;
            }

            var pathString = pathToString(state.selectedPath || []);
            var ancestors = getNodeAncestors(state.selectedPath || []);
            var breadcrumbHtml = ancestors.map(function (item, index) {
                var isCurrent = index === ancestors.length - 1;
                return '<button type="button" class="ft-page-builder__breadcrumb-item js-breadcrumb-path' + (isCurrent ? ' is-current' : '') + '" data-path="' + escapeHtml(item.path) + '">'
                    + '<span>' + escapeHtml(item.type) + '</span>'
                    + '<span>' + escapeHtml(item.title) + '</span>'
                    + '</button>';
            }).join('');

            $selectionBreadcrumb.toggleClass('is-visible', breadcrumbHtml !== '').html(breadcrumbHtml);

            $selectionBar.addClass('is-visible').html(
                '<div class="ft-page-builder__selection-main">'
                + '<strong>' + escapeHtml(nodeTitle(node)) + '</strong>'
                + '<span>' + escapeHtml(nodeSummary(node)) + '</span>'
                + '</div>'
                + '<div class="ft-page-builder__selection-actions">' + buildNodeActionButtons(node, false) + '</div>'
            );

            $inspectorSummary.addClass('is-visible').html(
                '<div><strong>' + escapeHtml(nodeTitle(node)) + '</strong><span>{{ getTranslateByKey('ft_lb_node_type_path', 'admin', ['type' => ':T:', 'path' => ':P:']) }}'.replace(':T:', escapeHtml(node.type)).replace(':P:', escapeHtml(pathString)) + '</span></div>'
            );

            $inspectorStickyActions.addClass('is-visible').html(
                '<div class="ft-page-builder__inspector-sticky-meta">{{ getTranslateByKey('ft_lb_current_block') }}<strong>' + escapeHtml(nodeTitle(node)) + '</strong> / ' + escapeHtml(node.type) + '</div>'
                + '<div class="ft-page-builder__inspector-sticky-buttons">' + buildNodeActionButtons(node, true) + '</div>'
            );

            $inspectorOutline.addClass('is-visible is-expanded').html(
                '<button type="button" class="ft-page-builder__inspector-outline-head js-inspector-outline-toggle">'
                + '<div><h5 class="ft-page-builder__inspector-outline-title">' + '{{ getTranslateByKey('ft_lb_outline_title') }}' + '</h5><div class="ft-page-builder__inspector-outline-summary">' + '{{ getTranslateByKey('ft_lb_outline_summary') }}' + '</div></div>'
                + '<span class="ft-page-builder__inspector-outline-toggle">v</span>'
                + '</button>'
                + '<div class="ft-page-builder__inspector-outline-body">'
                + '<div class="ft-page-builder__inspector-outline-grid">'
                + '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_outline_type') }}' + '</strong>' + escapeHtml(node.type) + '</div>'
                + '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_outline_title_label') }}' + '</strong>' + escapeHtml(nodeTitle(node)) + '</div>'
                + '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_outline_path') }}' + '</strong>' + escapeHtml(pathString) + '</div>'
                + '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_outline_target') }}' + '</strong>' + escapeHtml(getSelectionTargetText()) + '</div>'
                + '</div>'
                + '</div>'
            );

            renderCatalogTarget();
        }

        function renderLayoutMetaInspector() {
            var selectedNode = getNodeByPath(state.selectedPath || []);
            var totalNodes = countNodes(state.schema.sections || []);
            var sectionsCount = (state.schema.sections || []).length;
            var route = {!! json_encode((string) ($sceneMeta['route'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
            var content = [
                '<div class="ft-page-builder__inspector-outline-grid">',
                '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_meta_scene') }}' + '</strong>{{ $sceneLabel }}</div>',
                '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_meta_sections') }}' + '</strong>' + sectionsCount + '</div>',
                '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_meta_nodes') }}' + '</strong>' + totalNodes + '</div>',
                '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_meta_model') }}' + '</strong>{{ $model->access_identification }}</div>',
                '<div class="ft-page-builder__inspector-outline-item"><strong>' + '{{ getTranslateByKey('ft_lb_meta_route') }}' + '</strong>' + escapeHtml(route || '{{ getTranslateByKey('ft_lb_undefined') }}') + '</div>',
                '</div>',
                '<div class="ft-builder__muted" style="margin-top:12px;">',
                selectedNode
                    ? '{{ getTranslateByKey('ft_lb_meta_selected') }}'
                    : '{{ getTranslateByKey('ft_lb_meta_no_selection') }}',
                '</div>'
            ];
            $layoutMetaInspector.html(content.join(''));
        }

        function setInspectorTab(tab) {
            state.activeInspectorTab = tab;
            $('.js-inspector-tab').removeClass('is-active').filter('[data-inspector-tab="' + tab + '"]').addClass('is-active');
            $('[data-inspector-panel]').removeClass('is-active').filter('[data-inspector-panel="' + tab + '"]').addClass('is-active');
        }

        function renderNodeInspector() {
            var node = getNodeByPath(state.selectedPath || []);
            if (!node) {
                $nodeInspector.html('<div class="ft-builder__empty">' + '{{ getTranslateByKey('ft_lb_no_selection') }}' + '</div>');
                return;
            }

            $nodeInspector.html([
                specificFieldSet(node),
                '<div style="margin-top:14px;">' + commonFieldSet(node) + '</div>'
            ].join(''));
        }

        function renderWorkspace() {
            ensureSelection();
            renderThemeInspector();
            renderNodeInspector();
            renderSelectionPanels(getNodeByPath(state.selectedPath || []));
            renderLayoutMetaInspector();
            setCatalogGroup(state.activeCatalogGroup || $('.js-catalog-group').first().data('group'));
            setInspectorTab(state.activeInspectorTab || 'content');
            applyPreviewMode();
            syncPreviewSelection(false);
        }

        function applyPreviewMode() {
            $layoutPreviewShell.removeClass('is-desktop is-tablet is-mobile').addClass('is-' + state.previewMode);
            $('.js-preview-mode').removeClass('is-active').filter('[data-mode="' + state.previewMode + '"]').addClass('is-active');
        }

        function syncPreviewSelection(scrollToNode) {
            var node = getNodeByPath(state.selectedPath || []);
            var nodeId = node && node.id ? String(node.id) : '';
            $layoutPreview.find('.ft-builder__preview-node--selected').removeClass('ft-builder__preview-node--selected');
            if (!nodeId) {
                return;
            }
            var $target = $layoutPreview
                .find('[data-node-id]')
                .filter(function () {
                    return String($(this).attr('data-node-id') || '') === nodeId;
                })
                .first();
            if (!$target.length) {
                return;
            }
            $target.addClass('ft-builder__preview-node--selected');
            if (scrollToNode) {
                var el = $target.get(0);
                if (el && el.scrollIntoView) {
                    el.scrollIntoView({block: 'center', inline: 'nearest'});
                }
            }
        }

        function openPreviewDialog() {
            var previewHtml = ($layoutPreviewContent.length ? $layoutPreviewContent : $layoutPreview).html() || '<div class="mx-page-empty">' + '{{ getTranslateByKey('ft_lb_no_blocks') }}' + '</div>';
            layer.open({
                type: 1,
                title: '{{ $sceneLabel }}{{ getTranslateByKey('ft_lb_full_preview') }}',
                area: ['92vw', '90vh'],
                shadeClose: true,
                maxmin: true,
                content: '<div class="ft-builder__preview-dialog">'
                    + '<div class="ft-builder__preview-dialog-toolbar">'
                    + '<div class="ft-builder__preview-dialog-title">' + '{{ getTranslateByKey('ft_lb_preview_mode') }}' + '</div>'
                    + '<div class="ft-builder__preview-dialog-modes">'
                    + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'desktop' ? ' is-active' : '') + '" data-mode="desktop">PC</button>'
                    + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'tablet' ? ' is-active' : '') + '" data-mode="tablet">Pad</button>'
                    + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'mobile' ? ' is-active' : '') + '" data-mode="mobile">Mobile</button>'
                    + '</div>'
                    + '</div>'
                    + '<div class="ft-builder__preview-dialog-shell is-' + state.previewMode + '">'
                    + '<div class="ft-builder__preview-dialog-inner">' + previewHtml + '</div>'
                    + '</div>'
                    + '</div>'
            });
        }

        var $previewFloatbar = null;
        var previewFloatbarState = {
            nodeId: '',
            path: null,
            hoverEl: null,
            raf: 0
        };

        function ensurePreviewFloatbar() {
            if ($previewFloatbar && $previewFloatbar.length) {
                return $previewFloatbar;
            }
            $previewFloatbar = $(
                '<div class="ft-builder__preview-floatbar" id="layoutPreviewFloatbar">' +
                    '<button type="button" class="ft-builder__preview-floatbar-btn" data-preview-action="edit" title="' + '{{ getTranslateByKey('common_edit') }}' + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.83H5v-.92l8.06-8.06.92.92L5.92 20.08zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/></svg>' +
                    '</button>' +
                    '<button type="button" class="ft-builder__preview-floatbar-btn" data-preview-action="clone" title="' + '{{ getTranslateByKey('ft_lb_copy') }}' + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm4 4H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 18H8V7h12v16z"/></svg>' +
                    '</button>' +
                    '<button type="button" class="ft-builder__preview-floatbar-btn" data-preview-action="up" title="' + '{{ getTranslateByKey('ft_field_move_up') }}' + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5H7z"/></svg>' +
                    '</button>' +
                    '<button type="button" class="ft-builder__preview-floatbar-btn" data-preview-action="down" title="' + '{{ getTranslateByKey('ft_field_move_down') }}' + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5H7z"/></svg>' +
                    '</button>' +
                    '<button type="button" class="ft-builder__preview-floatbar-btn is-danger" data-preview-action="delete" title="' + '{{ getTranslateByKey('common_delete') }}' + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/></svg>' +
                    '</button>' +
                '</div>'
            );
            $layoutPreviewShell.append($previewFloatbar);
            return $previewFloatbar;
        }

        function hidePreviewFloatbar() {
            if (!$previewFloatbar || !$previewFloatbar.length) {
                return;
            }
            $previewFloatbar.hide();
            previewFloatbarState.nodeId = '';
            previewFloatbarState.path = null;
            if (previewFloatbarState.hoverEl) {
                $(previewFloatbarState.hoverEl).removeClass('ft-builder__preview-node--hovering');
                previewFloatbarState.hoverEl = null;
            }
        }

        function positionPreviewFloatbar(hitEl) {
            if (!hitEl) {
                return;
            }
            var bar = ensurePreviewFloatbar().get(0);
            var shell = $layoutPreviewShell.get(0);
            if (!bar || !shell) {
                return;
            }
            var rect = hitEl.getBoundingClientRect();
            var shellRect = shell.getBoundingClientRect();
            var padding = 8;
            var top = rect.top - shellRect.top + shell.scrollTop + padding;
            var left = rect.right - shellRect.left + shell.scrollLeft - bar.offsetWidth - padding;
            var minLeft = shell.scrollLeft + 10;
            var maxLeft = shell.scrollLeft + shell.clientWidth - bar.offsetWidth - 10;
            if (left < minLeft) {
                left = minLeft;
            }
            if (left > maxLeft) {
                left = maxLeft;
            }
            var minTop = shell.scrollTop + 10;
            var maxTop = shell.scrollTop + shell.clientHeight - bar.offsetHeight - 10;
            if (top < minTop) {
                top = minTop;
            }
            if (top > maxTop) {
                top = maxTop;
            }
            bar.style.left = left + 'px';
            bar.style.top = top + 'px';
        }

        function schedulePreviewFloatbarPosition() {
            if (!previewFloatbarState.hoverEl || !$previewFloatbar || !$previewFloatbar.is(':visible')) {
                return;
            }
            if (previewFloatbarState.raf) {
                cancelAnimationFrame(previewFloatbarState.raf);
            }
            previewFloatbarState.raf = requestAnimationFrame(function () {
                positionPreviewFloatbar(previewFloatbarState.hoverEl);
            });
        }

        function syncPreviewFloatbarToSelection() {
            if (!state.selectedPath || !state.schema || !state.schema.sections) {
                return;
            }
            if (!$previewFloatbar || !$previewFloatbar.is(':visible')) {
                return;
            }
            var node = getNodeByPath(state.selectedPath || []);
            var nodeId = node && node.id ? String(node.id) : '';
            if (!nodeId) {
                return;
            }
            var $target = $layoutPreview.find('[data-node-id="' + nodeId.replace(/"/g, '\\"') + '"]').first();
            if (!$target.length) {
                return;
            }
            previewFloatbarState.nodeId = nodeId;
            previewFloatbarState.path = state.selectedPath;
            previewFloatbarState.hoverEl = $target.get(0);
            $previewFloatbar.show();
            schedulePreviewFloatbarPosition();
        }

        function previewCustomSchema(schema) {
            $.post('{{ url('admin/formtools/modelLayoutPreview') }}', {
                _token: '{{ csrf_token() }}',
                id: '{{ $model->id }}',
                layout_schema: JSON.stringify(normalizeSchema(schema), null, 2)
            }).done(function (res) {
                if (res && res.status === 200) {
                    var previewHtml = res.data.html || '<div class="mx-page-empty">' + '{{ getTranslateByKey('ft_lb_no_blocks') }}' + '</div>';
                    layer.open({
                        type: 1,
                        title: '{{ getTranslateByKey('ft_lb_preset_preview') }}',
                        area: ['92vw', '90vh'],
                        shadeClose: true,
                        maxmin: true,
                        content: '<div class="ft-builder__preview-dialog">'
                            + '<div class="ft-builder__preview-dialog-toolbar">'
                            + '<div class="ft-builder__preview-dialog-title">' + '{{ getTranslateByKey('ft_lb_preset_effect') }}' + '</div>'
                            + '<div class="ft-builder__preview-dialog-modes">'
                            + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'desktop' ? ' is-active' : '') + '" data-mode="desktop">PC</button>'
                            + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'tablet' ? ' is-active' : '') + '" data-mode="tablet">Pad</button>'
                            + '<button type="button" class="btn btn-default ft-builder__preview-mode js-dialog-preview-mode' + (state.previewMode === 'mobile' ? ' is-active' : '') + '" data-mode="mobile">Mobile</button>'
                            + '</div>'
                            + '</div>'
                            + '<div class="ft-builder__preview-dialog-shell is-' + state.previewMode + '"><div class="ft-builder__preview-dialog-inner">' + previewHtml + '</div></div>'
                            + '</div>'
                    });
                    return;
                }
                layer.msg((res && res.msg) || '{{ getTranslateByKey('ft_lb_preset_preview_failed') }}');
            }).fail(function () {
                layer.msg('{{ getTranslateByKey('ft_lb_preset_preview_failed') }}');
            });
        }

        function insertNode(schemaNode, forceRoot) {
            var node = normalizeNode(deepClone(schemaNode));
            var selectedNode = getNodeByPath(state.selectedPath || []);
            if (!forceRoot && selectedNode && canHaveChildren(selectedNode)) {
                var childInfo = ensureChildCollection(selectedNode);
                childInfo.list.push(node);
                setSchema(state.schema, state.selectedPath.concat([childInfo.key, childInfo.list.length - 1]));
                return;
            }

            state.schema.sections.push(node);
            setSchema(state.schema, ['sections', state.schema.sections.length - 1]);
        }

        function resetCanvas() {
            if (!confirm('{{ getTranslateByKey('ft_lb_confirm_clear') }}')) {
                return;
            }
            state.schema.sections = [];
            setSchema(state.schema, null);
        }

        function addRootSection() {
            insertNode({
                type: 'section',
                props: {
                    contentWidth: 'contained',
                    innerWidth: '1200px'
                },
                style: {
                    padding: '56px 0',
                    background: '#ffffff'
                },
                children: []
            });
        }

        function moveNode(path, offset) {
            var info = getCollectionInfo(path);
            if (!info) {
                return;
            }
            var collection = getByPath(state.schema, info.parentPath);
            if (!Array.isArray(collection)) {
                return;
            }
            var nextIndex = info.index + offset;
            if (nextIndex < 0 || nextIndex >= collection.length) {
                return;
            }
            var current = collection[info.index];
            collection[info.index] = collection[nextIndex];
            collection[nextIndex] = current;
            var nextPath = info.parentPath.concat([nextIndex]);
            setSchema(state.schema, nextPath);
        }

        function deleteNode(path) {
            var info = getCollectionInfo(path);
            if (!info) {
                return;
            }
            var collection = getByPath(state.schema, info.parentPath);
            if (!Array.isArray(collection)) {
                return;
            }
            collection.splice(info.index, 1);
            var fallback = collection.length ? info.parentPath.concat([Math.max(0, info.index - 1)]) : ['sections', 0];
            if (info.parentPath[0] !== 'sections' && !getNodeByPath(fallback)) {
                fallback = ['sections', 0];
            }
            setSchema(state.schema, getNodeByPath(fallback) ? fallback : null);
        }

        function cloneNode(path) {
            var info = getCollectionInfo(path);
            if (!info) {
                return;
            }
            var collection = getByPath(state.schema, info.parentPath);
            if (!Array.isArray(collection)) {
                return;
            }
            var cloned = normalizeNode(deepClone(collection[info.index]));
            collection.splice(info.index + 1, 0, cloned);
            setSchema(state.schema, info.parentPath.concat([info.index + 1]));
        }

        function updateField(scope, key, value) {
            var node = getNodeByPath(state.selectedPath || []);
            if (!node) {
                return;
            }

            if (scope === 'theme') {
                state.schema.theme[key] = value;
                setSchema(state.schema, state.selectedPath);
                return;
            }

            var bucket = scope === 'style' ? node.style : node.props;
            bucket[key] = value;
            if (key === 'span') {
                bucket[key] = Math.max(1, Math.min(12, parseInt(value || 12, 10) || 12));
            }
            if (key === 'columns') {
                bucket[key] = parseInt(value || 1, 10) || 1;
            }
            if (key === 'limit') {
                bucket[key] = parseInt(value || 0, 10) || 0;
            }
            setSchema(state.schema, state.selectedPath);
        }

        function syncFromTextarea() {
            clearTimeout(syncTimer);
            syncTimer = setTimeout(function () {
                var parsed = readSchemaFromTextarea();
                if (!parsed) {
                    setJsonStatus(false, '{{ getTranslateByKey('ft_lb_json_error') }}');
                    return;
                }
                state.schema = parsed;
                setJsonStatus(true, '{{ getTranslateByKey('ft_lb_json_ok') }}');
                ensureSelection();
                renderWorkspace();
                queuePreview();
            }, 300);
        }

        $(document).on('click', '.js-layout-block', function () {
            var schema = $(this).attr('data-schema');
            try {
                insertNode(JSON.parse(schema));
            } catch (err) {
                layer.msg('{{ getTranslateByKey('ft_lb_insert_failed') }}');
            }
        });

        $(document).on('click', '.js-layout-block-root', function () {
            var schema = $(this).attr('data-schema');
            try {
                insertNode(JSON.parse(schema), true);
            } catch (err) {
                layer.msg('{{ getTranslateByKey('ft_lb_insert_failed') }}');
            }
        });

        $(document).on('click', '.js-layout-preset', function () {
            var schema = $(this).attr('data-schema');
            if (!confirm('{{ getTranslateByKey('ft_lb_confirm_preset') }}')) {
                return;
            }
            try {
                setSchema(JSON.parse(schema), ['sections', 0]);
            } catch (err) {
                layer.msg('{{ getTranslateByKey('ft_lb_preset_apply_failed') }}');
            }
        });

        $(document).on('click', '.js-layout-preset-preview', function () {
            var schema = $(this).attr('data-schema');
            try {
                previewCustomSchema(JSON.parse(schema));
            } catch (err) {
                layer.msg('{{ getTranslateByKey('ft_lb_preset_preview_failed') }}');
            }
        });

        $(document).on('click', '.js-layout-empty-root', resetCanvas);
        $(document).on('click', '.js-layout-add-root-section', addRootSection);
        $(document).on('click', '.js-layout-preview', previewSchema);
        $(document).on('click', '.js-catalog-group', function () {
            setCatalogGroup($(this).data('group'));
        });
        $(document).on('click', '.js-catalog-section-toggle', function () {
            $(this).closest('[data-catalog-group-section]').toggleClass('is-open');
        });
        $(document).on('click', '.js-breadcrumb-path', function () {
            selectPath(parsePath($(this).data('path')));
        });
        $(document).on('click', '.js-inspector-tab', function () {
            setInspectorTab($(this).data('inspectorTab'));
        });
        $(document).on('click', '.js-inspector-outline-toggle', function () {
            $inspectorOutline.toggleClass('is-expanded');
        });

        $(document).on('click', '#layoutPreview', function (e) {
            if ($(e.target).closest('.ft-builder__preview-floatbar').length) {
                return;
            }
            var $hit = $(e.target).closest('[data-node-id]', this);
            if (!$hit.length) {
                return;
            }
            e.preventDefault();
            var nodeId = $hit.data('nodeId');
            var matchPath = findPathByNodeId(state.schema.sections || [], ['sections'], String(nodeId || ''));
            if (matchPath) {
                selectPath(matchPath);
            }
        });

        $layoutPreviewShell.on('mousemove', function (e) {
            if ($(e.target).closest('.ft-builder__preview-floatbar').length) {
                return;
            }
            var $hit = $(e.target).closest('[data-node-id]');
            var previewRoot = $layoutPreview.get(0);
            if (!$hit.length || !previewRoot || !previewRoot.contains($hit.get(0))) {
                hidePreviewFloatbar();
                return;
            }
            var nodeId = String($hit.attr('data-node-id') || '');
            if (!nodeId) {
                hidePreviewFloatbar();
                return;
            }
            var matchPath = findPathByNodeId(state.schema.sections || [], ['sections'], nodeId);
            if (!matchPath) {
                hidePreviewFloatbar();
                return;
            }
            ensurePreviewFloatbar();
            previewFloatbarState.nodeId = nodeId;
            previewFloatbarState.path = matchPath;
            previewFloatbarState.hoverEl = $hit.get(0);
            $previewFloatbar.show();
            schedulePreviewFloatbarPosition();
        });

        $layoutPreviewShell.on('mouseleave', function () {
            hidePreviewFloatbar();
        });

        $layoutPreviewShell.on('scroll', function () {
            schedulePreviewFloatbarPosition();
        });

        $(document).on('mousedown', '#layoutPreviewFloatbar', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        $(document).on('click', '#layoutPreviewFloatbar [data-preview-action]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var action = $(this).data('previewAction');
            var path = previewFloatbarState.path;
            if (!path) {
                return;
            }
            if (action === 'edit') {
                selectPath(path);
                return;
            }
            if (action === 'clone') {
                cloneNode(path);
                return;
            }
            if (action === 'up') {
                moveNode(path, -1);
                return;
            }
            if (action === 'down') {
                moveNode(path, 1);
                return;
            }
            if (action === 'delete') {
                if (!confirm('{{ getTranslateByKey('ft_lb_confirm_delete_node') }}')) {
                    return;
                }
                deleteNode(path);
            }
        });

        $(document).on('click', '.js-node-up', function () {
            moveNode(parsePath($(this).data('path')), -1);
        });

        $(document).on('click', '.js-node-down', function () {
            moveNode(parsePath($(this).data('path')), 1);
        });

        $(document).on('click', '.js-node-clone', function () {
            cloneNode(parsePath($(this).data('path')));
        });

        $(document).on('click', '.js-node-delete', function () {
            if (!confirm('{{ getTranslateByKey('ft_lb_confirm_delete_node') }}')) {
                return;
            }
            deleteNode(parsePath($(this).data('path')));
        });

        $(document).on('click', '.js-preview-mode', function () {
            state.previewMode = $(this).data('mode');
            applyPreviewMode();
        });

        $(document).on('click', '.js-preview-dialog', openPreviewDialog);

        $(document).on('click', '.js-dialog-preview-mode', function () {
            var mode = $(this).data('mode');
            state.previewMode = mode;
            applyPreviewMode();
            $('.js-dialog-preview-mode').removeClass('is-active');
            $(this).addClass('is-active');
            $(this).closest('.ft-builder__preview-dialog').find('.ft-builder__preview-dialog-shell')
                .removeClass('is-desktop is-tablet is-mobile')
                .addClass('is-' + mode);
        });

        $(document).on('input change', '.js-field', function () {
            updateField($(this).data('scope'), $(this).data('key'), $(this).val());
        });

        $(document).on('click', '.js-add-child-default', function () {
            insertNode({
                type: 'heading',
                props: {
                    level: 'h2',
                    text: '{{ getTranslateByKey('ft_lb_new_heading') }}'
                },
                style: {
                    margin: '0 0 16px'
                }
            });
        });

        $(document).on('click', '.js-inspector-clone', function () {
            if (!state.selectedPath) {
                return;
            }
            cloneNode(state.selectedPath);
        });

        $(document).on('click', '.js-inspector-up', function () {
            if (!state.selectedPath) {
                return;
            }
            moveNode(state.selectedPath, -1);
        });

        $(document).on('click', '.js-inspector-down', function () {
            if (!state.selectedPath) {
                return;
            }
            moveNode(state.selectedPath, 1);
        });

        $(document).on('click', '.js-inspector-delete', function () {
            if (!state.selectedPath || !confirm('{{ getTranslateByKey('ft_lb_confirm_delete_current') }}')) {
                return;
            }
            deleteNode(state.selectedPath);
        });

        $(document).on('click', '.js-inspector-preview', openPreviewDialog);

        $layoutSchema.on('input', syncFromTextarea);

        state.schema = readSchemaFromTextarea() || normalizeSchema({});
        if (state.schema.sections.length) {
            state.selectedPath = ['sections', 0];
        }
        writeSchemaToTextarea();
        renderWorkspace();
        previewSchema();
    })(jQuery);
</script>
 </body>
 </html>
